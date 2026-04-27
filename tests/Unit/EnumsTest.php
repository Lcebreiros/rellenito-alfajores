<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\App;

uses(Tests\TestCase::class);

describe('OrderStatus enum', function () {

    describe('label() in Spanish', function () {
        beforeEach(fn () => App::setLocale('es'));

        it('returns correct labels for all cases', function (OrderStatus $status, string $expected) {
            expect($status->label())->toBe($expected);
        })->with([
            [OrderStatus::DRAFT,     'Borrador'],
            [OrderStatus::PENDING,   'Pendiente'],
            [OrderStatus::SCHEDULED, 'Agendado'],
            [OrderStatus::COMPLETED, 'Completada'],
            [OrderStatus::CANCELED,  'Cancelada'],
        ]);
    });

    describe('label() in English', function () {
        beforeEach(fn () => App::setLocale('en'));

        it('returns correct labels for all cases', function (OrderStatus $status, string $expected) {
            expect($status->label())->toBe($expected);
        })->with([
            [OrderStatus::DRAFT,     'Draft'],
            [OrderStatus::PENDING,   'Pending'],
            [OrderStatus::SCHEDULED, 'Scheduled'],
            [OrderStatus::COMPLETED, 'Completed'],
            [OrderStatus::CANCELED,  'Canceled'],
        ]);
    });

    it('color() returns the correct Tailwind color per status', function (OrderStatus $status, string $color) {
        expect($status->color())->toBe($color);
    })->with([
        [OrderStatus::DRAFT,     'gray'],
        [OrderStatus::PENDING,   'yellow'],
        [OrderStatus::SCHEDULED, 'blue'],
        [OrderStatus::COMPLETED, 'green'],
        [OrderStatus::CANCELED,  'red'],
    ]);

    it('values() returns all raw string values', function () {
        expect(OrderStatus::values())
            ->toContain('draft', 'pending', 'scheduled', 'completed', 'canceled');
    });
});

describe('PaymentMethod enum', function () {

    describe('label() in Spanish', function () {
        beforeEach(fn () => App::setLocale('es'));

        it('returns correct labels for all cases', function (PaymentMethod $method, string $expected) {
            expect($method->label())->toBe($expected);
        })->with([
            [PaymentMethod::CASH,     'Efectivo'],
            [PaymentMethod::CARD,     'Tarjeta'],
            [PaymentMethod::TRANSFER, 'Transferencia'],
            [PaymentMethod::MIXED,    'Mixto'],
            [PaymentMethod::CRYPTO,   'Criptomoneda'],
        ]);
    });

    describe('label() in English', function () {
        beforeEach(fn () => App::setLocale('en'));

        it('returns correct labels for all cases', function (PaymentMethod $method, string $expected) {
            expect($method->label())->toBe($expected);
        })->with([
            [PaymentMethod::CASH,     'Cash'],
            [PaymentMethod::CARD,     'Card'],
            [PaymentMethod::TRANSFER, 'Bank transfer'],
            [PaymentMethod::MIXED,    'Mixed'],
            [PaymentMethod::CRYPTO,   'Cryptocurrency'],
        ]);
    });
});

describe('PaymentStatus enum', function () {

    describe('label() in Spanish', function () {
        beforeEach(fn () => App::setLocale('es'));

        it('returns correct labels for all cases', function (PaymentStatus $status, string $expected) {
            expect($status->label())->toBe($expected);
        })->with([
            [PaymentStatus::PENDING,  'Pendiente'],
            [PaymentStatus::PAID,     'Pagado'],
            [PaymentStatus::PARTIAL,  'Pago Parcial'],
            [PaymentStatus::REFUNDED, 'Reembolsado'],
        ]);
    });

    describe('label() in English', function () {
        beforeEach(fn () => App::setLocale('en'));

        it('returns correct labels for all cases', function (PaymentStatus $status, string $expected) {
            expect($status->label())->toBe($expected);
        })->with([
            [PaymentStatus::PENDING,  'Pending'],
            [PaymentStatus::PAID,     'Paid'],
            [PaymentStatus::PARTIAL,  'Partial payment'],
            [PaymentStatus::REFUNDED, 'Refunded'],
        ]);
    });
});
