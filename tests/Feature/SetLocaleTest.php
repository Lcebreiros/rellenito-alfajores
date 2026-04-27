<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\App;

describe('SetLocale middleware', function () {

    it('resolves locale from the session', function () {
        $this->withSession(['locale' => 'en'])
            ->get('/login')
            ->assertOk();

        expect(App::getLocale())->toBe('en');
    });

    it('resolves locale from cookie when session has no locale', function () {
        $this->withCookie('locale', 'pt')
            ->get('/login')
            ->assertOk();

        expect(App::getLocale())->toBe('pt');
    });

    it('resolves locale from Accept-Language header as fallback', function () {
        $this->withHeader('Accept-Language', 'pt-BR,pt;q=0.9,en;q=0.8')
            ->get('/login')
            ->assertOk();

        expect(App::getLocale())->toBe('pt');
    });

    it('ignores unsupported locales in Accept-Language and uses app default', function () {
        $this->withHeader('Accept-Language', 'fr-FR,de;q=0.8')
            ->get('/login')
            ->assertOk();

        expect(App::getLocale())->toBe(config('app.locale'));
    });

    it('session takes priority over cookie', function () {
        $this->withSession(['locale' => 'en'])
            ->withCookie('locale', 'pt')
            ->get('/login')
            ->assertOk();

        expect(App::getLocale())->toBe('en');
    });

    it('cookie takes priority over Accept-Language header', function () {
        $this->withCookie('locale', 'en')
            ->withHeader('Accept-Language', 'pt-BR,pt;q=0.9')
            ->get('/login')
            ->assertOk();

        expect(App::getLocale())->toBe('en');
    });

    it('falls back to app default when no locale signals are present', function () {
        $this->get('/login')->assertOk();

        expect(App::getLocale())->toBe(config('app.locale'));
    });

    it('SUPPORTED_LOCALES contains es, en and pt', function () {
        expect(SetLocale::SUPPORTED_LOCALES)
            ->toContain('es', 'en', 'pt')
            ->toHaveCount(3);
    });
});
