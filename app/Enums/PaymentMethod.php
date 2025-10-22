<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case TRANSFER = 'transfer';
    case MIXED = 'mixed';
    case CRYPTO = 'crypto';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Efectivo',
            self::CARD => 'Tarjeta',
            self::TRANSFER => 'Transferencia',
            self::MIXED => 'Mixto',
            self::CRYPTO => 'Criptomoneda',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CASH => 'banknotes',
            self::CARD => 'credit-card',
            self::TRANSFER => 'arrows-right-left',
            self::MIXED => 'squares-plus',
            self::CRYPTO => 'currency-bitcoin',
        };
    }
}

