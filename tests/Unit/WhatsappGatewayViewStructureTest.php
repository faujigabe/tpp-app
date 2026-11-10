<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WhatsappGatewayViewStructureTest extends TestCase
{
    public function test_halaman_tpp_memiliki_pengiriman_via_gateway(): void
    {
        $view = (string) file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/tpp/index.blade.php'
        );

        $this->assertStringContainsString('id="btnKirimWhatsappGateway"', $view);
        $this->assertStringContainsString('id="btnHentikanGateway"', $view);
        $this->assertStringContainsString('id="gatewayProgressBar"', $view);
        $this->assertStringContainsString('id="gatewayStatusText"', $view);
        $this->assertStringContainsString('id="gatewayLog"', $view);
        $this->assertStringContainsString("route('tpp.whatsapp.gateway.send'", $view);
        $this->assertStringContainsString("route('tpp.whatsapp.gateway.status'", $view);
        $this->assertStringContainsString('Kirim via Gateway', $view);
        // Fitur lama (wa.me) tetap ada dan tidak berubah.
        $this->assertStringContainsString('id="btnKirimWhatsappMassal"', $view);
        $this->assertStringContainsString('Buka Semua Chat', $view);
    }

    public function test_route_gateway_terdaftar_untuk_admin_dan_operator(): void
    {
        $routes = (string) file_get_contents(
            dirname(__DIR__, 2) . '/routes/web.php'
        );

        $this->assertStringContainsString('WhatsappGatewayController', $routes);
        $this->assertStringContainsString("name('tpp.whatsapp.gateway.status')", $routes);
        $this->assertStringContainsString("name('tpp.whatsapp.gateway.send')", $routes);
    }
}
