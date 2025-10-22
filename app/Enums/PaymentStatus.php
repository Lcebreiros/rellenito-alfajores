<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PARTIAL = 'partial';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::PAID => 'Pagado',
            self::PARTIAL => 'Pago Parcial',
            self::REFUNDED => 'Reembolsado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PAID => 'green',
            self::PARTIAL => 'blue',
            self::REFUNDED => 'red',
        };
    }
}

