<?php

namespace Feature;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Repositories\CertificateRepository;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class ShowCertificateTest extends TestCase
{
    private WidgetInterface $widget;
    private CertificateInterface $certificate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widget = $this->createMockWidget();
        $this->certificate = $this->createMockCertificate();
        $this->certificate->widget = $this->widget;

        $certificateRepositoryMock = $this->createMock(CertificateRepository::class);
        $certificateRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->certificate->id)
            ->willReturn($this->certificate);

        // Привязываем мок репозитория к контейнеру зависимостей
        App::instance(CertificateRepositoryInterface::class, $certificateRepositoryMock);
    }

    /**
     * @covers \App\Http\Controllers\ClientController::showCertificate
     * @return void
     */
    public function testShowCertificate()
    {
        $response = $this->get($this->widget->id . '/view/' . $this->certificate->id);
        $response->assertStatus(200);
        $json = $response->json();

        self::assertEquals($this->certificate->id, $json['id']);

        self::assertIsString($json['serial']);
        self::assertEquals($this->certificate->serial, $json['serial']);

        self::assertNotFalse(strtotime($json['expire_at']));
        self::assertEquals($this->certificate->expire_at, $json['expire_at']);

        self::assertIsInt($json['amount']);
        self::assertEquals($this->certificate->amount, $json['amount']);

        self::assertEquals(4, strlen($json['pin']));
        self::assertEquals($this->certificate->pin, $json['pin']);

        self::assertStringStartsWith('https://', $json['cover']);
        self::assertStringEndsWith('.png', $json['cover']);
        self::assertStringContainsString($this->certificate->cover_path, $json['cover']);

        self::assertEquals($this->certificate->orderItem->sender_name, $json['sender_name']);

        self::assertEquals($this->certificate->orderItem->recipient_name, $json['recipient_name']);

        self::assertEquals($this->certificate->orderItem->recipient_type, $json['recipient_type']);

        self::assertEquals($this->certificate->orderItem->message, $json['message']);

        self::assertEquals($this->certificate->orderItem->order->widget->faq, $json['faq']);

        self::assertStringStartsWith('https://', $json['favicon']);
        self::assertStringContainsString($this->certificate->orderItem->order->widget->favicon_image, $json['favicon']);

        self::assertEquals($this->certificate->orderItem->order->widget->support_email, $json['support_email']);

        self::assertEquals($this->certificate->orderItem->order->widget->support_tel_number, $json['support_msisdn']);

        self::assertStringStartsWith('data:image/png;base64', $json['qr']);
        self::assertStringStartsWith('data:image/png;base64', $json['barcode']);

    }
}