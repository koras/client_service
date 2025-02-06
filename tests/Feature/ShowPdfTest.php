<?php

namespace Feature;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Repositories\CertificateRepository;
use App\Services\GenerationPdfApiService;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class ShowPdfTest extends TestCase
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
     * @covers \App\Http\Controllers\ClientController::showPdf
     * @covers \App\Services\CertificatePdfService::getPdfByCertificateId
     * @return void
     */
    public function testShowPdf()
    {
        $response = $this->get('/certificate/pdf/' . $this->certificate->id);
        $response->assertStatus(200);

        $headers = $response->headers;
        self::assertEquals(GenerationPdfApiService::CONTENT_TYPE_APPLICATION_PDF, $headers->get('content-type'));
    }
}