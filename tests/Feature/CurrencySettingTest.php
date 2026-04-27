<?php

use App\Models\Setting;
use App\Services\CurrencyService;

describe('CurrencyService::current() (DB)', function () {

    it('returns ARS when no setting exists', function () {
        expect(CurrencyService::current())->toBe('ARS');
    });

    it('returns the configured currency from settings', function () {
        Setting::set('currency', 'USD');

        expect(CurrencyService::current())->toBe('USD');
    });

    it('falls back to ARS when settings contain an invalid code', function () {
        Setting::set('currency', 'INVALID');

        expect(CurrencyService::current())->toBe('ARS');
    });
});
