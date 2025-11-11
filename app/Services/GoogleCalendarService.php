<?php

namespace App\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected GoogleClient $client;
    protected ?User $user = null;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setClientId(config('google-calendar.client_id'));
        $this->client->setClientSecret(config('google-calendar.client_secret'));
        $this->client->setRedirectUri(config('google-calendar.redirect_uri'));
        $this->client->setScopes(config('google-calendar.scopes'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Set the user for the service
     */
    public function forUser(User $user): self
    {
        $this->user = $user;

        if ($user->google_access_token) {
            $this->client->setAccessToken($user->google_access_token);

            // Refresh token if expired
            if ($this->client->isAccessTokenExpired() && $user->google_refresh_token) {
                $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                $newToken = $this->client->getAccessToken();

                $user->update([
                    'google_access_token' => $newToken,
                    'google_token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
                ]);
            }
        }

        return $this;
    }

    /**
     * Get the authorization URL for OAuth
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Handle the OAuth callback and store tokens
     */
    public function handleCallback(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \Exception('Error fetching access token: ' . ($token['error_description'] ?? $token['error']));
        }

        // Get user email from token
        $this->client->setAccessToken($token);
        $oauth2 = new \Google_Service_Oauth2($this->client);
        $userInfo = $oauth2->userinfo->get();

        return [
            'access_token' => $token,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
            'email' => $userInfo->email,
        ];
    }

    /**
     * Check if user is connected to Google Calendar
     */
    public function isConnected(): bool
    {
        return $this->user &&
               $this->user->google_access_token &&
               $this->user->google_refresh_token;
    }

    /**
     * Disconnect user from Google Calendar
     */
    public function disconnect(): void
    {
        if ($this->user) {
            $this->user->update([
                'google_access_token' => null,
                'google_refresh_token' => null,
                'google_token_expires_at' => null,
                'google_calendar_id' => null,
                'google_email' => null,
                'google_calendar_sync_enabled' => false,
            ]);
        }
    }

    /**
     * Create an event in Google Calendar
     */
    public function createEvent(
        string $summary,
        Carbon $start,
        ?Carbon $end = null,
        ?string $description = null,
        ?string $location = null,
        ?string $eventType = null
    ): ?Event {
        if (!$this->isConnected()) {
            return null;
        }

        try {
            $calendar = new Calendar($this->client);

            $event = new Event([
                'summary' => $summary,
                'description' => $description,
                'location' => $location,
            ]);

            // Set start time
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime($start->toRfc3339String());
            $startDateTime->setTimeZone(config('app.timezone', 'UTC'));
            $event->setStart($startDateTime);

            // Set end time (default to 1 hour after start)
            $endTime = $end ?? $start->copy()->addHour();
            $endDateTime = new EventDateTime();
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone(config('app.timezone', 'UTC'));
            $event->setEnd($endDateTime);

            // Set reminders
            $reminderMinutes = config('google-calendar.event_settings.default_reminder_minutes', 60);
            $reminders = new EventReminder();
            $event->setReminders([
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'popup', 'minutes' => $reminderMinutes],
                    ['method' => 'email', 'minutes' => $reminderMinutes],
                ],
            ]);

            // Set color based on event type
            if ($eventType && isset(config('google-calendar.event_settings.colors')[$eventType])) {
                $event->setColorId(config('google-calendar.event_settings.colors')[$eventType]);
            }

            $calendarId = 'primary';
            $sendNotifications = config('google-calendar.event_settings.send_notifications', true);

            return $calendar->events->insert($calendarId, $event, [
                'sendNotifications' => $sendNotifications,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating Google Calendar event: ' . $e->getMessage(), [
                'user_id' => $this->user?->id,
                'summary' => $summary,
            ]);
            return null;
        }
    }

    /**
     * Update an event in Google Calendar
     */
    public function updateEvent(
        string $eventId,
        string $summary,
        Carbon $start,
        ?Carbon $end = null,
        ?string $description = null,
        ?string $location = null
    ): ?Event {
        if (!$this->isConnected()) {
            return null;
        }

        try {
            $calendar = new Calendar($this->client);
            $calendarId = 'primary';

            // Get the existing event
            $event = $calendar->events->get($calendarId, $eventId);

            // Update fields
            $event->setSummary($summary);
            if ($description !== null) {
                $event->setDescription($description);
            }
            if ($location !== null) {
                $event->setLocation($location);
            }

            // Update start time
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime($start->toRfc3339String());
            $startDateTime->setTimeZone(config('app.timezone', 'UTC'));
            $event->setStart($startDateTime);

            // Update end time
            $endTime = $end ?? $start->copy()->addHour();
            $endDateTime = new EventDateTime();
            $endDateTime->setDateTime($endTime->toRfc3339String());
            $endDateTime->setTimeZone(config('app.timezone', 'UTC'));
            $event->setEnd($endDateTime);

            return $calendar->events->update($calendarId, $eventId, $event);
        } catch (\Exception $e) {
            Log::error('Error updating Google Calendar event: ' . $e->getMessage(), [
                'user_id' => $this->user?->id,
                'event_id' => $eventId,
            ]);
            return null;
        }
    }

    /**
     * Delete an event from Google Calendar
     */
    public function deleteEvent(string $eventId): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $calendar = new Calendar($this->client);
            $calendarId = 'primary';

            $calendar->events->delete($calendarId, $eventId);
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting Google Calendar event: ' . $e->getMessage(), [
                'user_id' => $this->user?->id,
                'event_id' => $eventId,
            ]);
            return false;
        }
    }

    /**
     * Get upcoming events from Google Calendar
     */
    public function getUpcomingEvents(int $maxResults = 10): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $calendar = new Calendar($this->client);
            $calendarId = 'primary';

            $optParams = [
                'maxResults' => $maxResults,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => now()->toRfc3339String(),
            ];

            $results = $calendar->events->listEvents($calendarId, $optParams);
            return $results->getItems();
        } catch (\Exception $e) {
            Log::error('Error fetching Google Calendar events: ' . $e->getMessage(), [
                'user_id' => $this->user?->id,
            ]);
            return [];
        }
    }
}
