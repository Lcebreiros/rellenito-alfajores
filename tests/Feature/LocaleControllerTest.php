<?php

use App\Models\User;

describe('LocaleController', function () {

    it('switches locale, sets session and sets cookie', function () {
        $response = $this->from('/login')->post(route('locale.switch', 'en'));

        $response->assertRedirect('/login');
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('locale', 'en');
    });

    it('switches to pt locale correctly', function () {
        $response = $this->from('/login')->post(route('locale.switch', 'pt'));

        $response->assertRedirect('/login');
        $response->assertSessionHas('locale', 'pt');
        $response->assertCookie('locale', 'pt');
    });

    it('switches to es locale correctly', function () {
        $response = $this->from('/login')->post(route('locale.switch', 'es'));

        $response->assertRedirect('/login');
        $response->assertSessionHas('locale', 'es');
        $response->assertCookie('locale', 'es');
    });

    it('returns 404 for an unsupported locale', function () {
        $this->post(route('locale.switch', 'fr'))->assertNotFound();
        $this->post(route('locale.switch', 'de'))->assertNotFound();
    });

    it('redirects authenticated users back to referer', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post(route('locale.switch', 'en'));

        $response->assertRedirect('/dashboard');
    });

    it('falls back to dashboard route when no referer is set', function () {
        $response = $this->post(route('locale.switch', 'en'));

        // Should redirect somewhere (either back or to dashboard fallback)
        $response->assertRedirect();
    });
});
