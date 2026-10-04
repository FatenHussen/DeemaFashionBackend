<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case REQUIRES_ACTION = 'requires_action';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function labelAr(): string
    {
        return match ($this) {
            self::PENDING => 'بانتظار الدفع',
            self::REQUIRES_ACTION => 'يتطلب تأكيد',
            self::PROCESSING => 'قيد المعالجة',
            self::SUCCEEDED => 'تم الدفع',
            self::FAILED => 'فشل الدفع',
            self::CANCELLED => 'ملغى',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::PENDING => 'Pending payment',
            self::REQUIRES_ACTION => 'Requires action',
            self::PROCESSING => 'Processing',
            self::SUCCEEDED => 'Paid',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
