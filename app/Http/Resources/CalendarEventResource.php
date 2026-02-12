<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'organizationId' => $this->organization_id,
            'eventType' => $this->event_type,
            'title' => $this->title,
            'description' => $this->description,
            'color' => $this->color,
            'eventDate' => $this->event_date?->toIso8601String(),
            'dueDate' => $this->due_date?->toIso8601String(),
            'reminderDate' => $this->reminder_date?->toIso8601String(),
            'completedAt' => $this->completed_at?->toIso8601String(),
            'status' => $this->status,
            'relatedType' => $this->related_type,
            'relatedId' => $this->related_id,
            'metadata' => $this->metadata,
            'isRecurring' => $this->is_recurring,
            'recurrencePattern' => $this->recurrence_pattern,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
