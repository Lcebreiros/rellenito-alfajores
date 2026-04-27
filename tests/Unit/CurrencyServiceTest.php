<?php

use App\Services\CurrencyService;

// Pure unit tests — no DB, no RefreshDatabase needed
describe('CurrencyService', function () {

    describe('codes()', function () {
        it('returns all 5 supported currency codes', function () {
            expect(CurrencyService::codes())
                ->toBeArray()
                ->toHaveCount(5)
                ->toContain('ARS', 'UYU', 'USD', 'EUR', 'BRL');
        });
    });

    describe('config()', function () {
        it('returns the correct config for ARS', function () {
            $cfg = CurrencyService::config('ARS');

            expect($cfg['symbol'])->toBe('$')
                ->and($cfg['locale'])->toBe('es-AR')
                ->and($cfg['decimal'])->toBe(',')
                ->and($cfg['thousands'])->toBe('.');
        });

        it('returns the correct config for USD', function () {
            $cfg = CurrencyService::config('USD');

            expect($cfg['symbol'])->toBe('US$')
                ->and($cfg['locale'])->toBe('en-US')
                ->and($cfg['decimal'])->toBe('.')
                ->and($cfg['thousands'])->toBe(',');
        });

        it('falls back to ARS for an unknown currency code', function () {
            $cfg = CurrencyService::config('XYZ');

            expect($cfg)->toBe(CurrencyService::CURRENCIES['ARS']);
        });
    });

    describe('symbol()', function () {
        it('returns $ for ARS', function () {
            expect(CurrencyService::symbol('ARS'))->toBe('$');
        });

        it('returns R$ for BRL', function () {
            expect(CurrencyService::symbol('BRL'))->toBe('R$');
        });

        it('returns € for EUR', function () {
            expect(CurrencyService::symbol('EUR'))->toBe('€');
        });

        it('returns $U for UYU', function () {
            expect(CurrencyService::symbol('UYU'))->toBe('$U');
        });
    });

    describe('locale()', function () {
        it('returns es-AR for ARS', function () {
            expect(CurrencyService::locale('ARS'))->toBe('es-AR');
        });

        it('returns pt-BR for BRL', function () {
            expect(CurrencyService::locale('BRL'))->toBe('pt-BR');
        });

        it('returns en-US for USD', function () {
            expect(CurrencyService::locale('USD'))->toBe('en-US');
        });
    });

    describe('format()', function () {
        it('formats a number correctly for ARS (comma decimal, dot thousands)', function () {
            expect(CurrencyService::format(1234.5, 'ARS'))->toBe('$ 1.234,50');
        });

        it('formats a number correctly for USD (dot decimal, comma thousands)', function () {
            expect(CurrencyService::format(1234.5, 'USD'))->toBe('US$ 1,234.50');
        });

        it('formats zero correctly', function () {
            expect(CurrencyService::format(0, 'ARS'))->toBe('$ 0,00');
        });

        it('formats integer amounts', function () {
            expect(CurrencyService::format(1000, 'EUR'))->toBe('€ 1.000,00');
        });
    });
});
