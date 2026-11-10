<?php

namespace Tests\Unit;

use App\Services\WhatsappMessageBuilder;
use PHPUnit\Framework\TestCase;

class WhatsappMessageBuilderTest extends TestCase
{
    public function test_format_nomor_awalan_nol_diubah_ke_62(): void
    {
        $builder = new WhatsappMessageBuilder();

        $this->assertSame('6281234567890', $builder->formatNumber('0812-3456-7890'));
        $this->assertSame('6281234567890', $builder->formatNumber('81234567890'));
        $this->assertSame('6281234567890', $builder->formatNumber('+62 812 3456 7890'));
    }

    public function test_format_nomor_tidak_valid_mengembalikan_null(): void
    {
        $builder = new WhatsappMessageBuilder();

        $this->assertNull($builder->formatNumber(null));
        $this->assertNull($builder->formatNumber(''));
        $this->assertNull($builder->formatNumber('12345'));
        $this->assertNull($builder->formatNumber('+1 555 000 1111'));
    }

    public function test_chat_id_memakai_akhiran_c_us(): void
    {
        $builder = new WhatsappMessageBuilder();

        $this->assertSame('6281234567890@c.us', $builder->chatId('081234567890'));
        $this->assertNull($builder->chatId('bukan nomor'));
    }
}
