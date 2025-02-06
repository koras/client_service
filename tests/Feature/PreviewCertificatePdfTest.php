<?php

namespace Feature;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Repositories\CertificateRepository;
use App\Repositories\WidgetRepository;
use App\Services\GenerationPdfApiService;
use Illuminate\Support\Facades\App;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class PreviewCertificatePdfTest extends TestCase
{
    private WidgetInterface $widget;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widget = $this->createMockWidget();

        $widgetRepository = $this->createMock(WidgetRepository::class);
        $widgetRepository->expects($this->once())
            ->method('find')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        // Привязываем мок репозитория к контейнеру зависимостей
        App::instance(WidgetRepositoryInterface::class, $widgetRepository);
    }

    /**
     * @covers \App\Http\Controllers\ClientController::previewCertificatePdf
     * @covers \App\Services\CertificatePdfService::getPreviewPdf
     * @return void
     */
    public function testPreviewCertificatePdf()
    {
        $response = $this->get('/widget/' . $this->widget->id . '/preview' . '?template_id=0&product_id=' . ProductsApiTESTService::PRODUCT_ID_1000);
        $response->assertStatus(200);

        $headers = $response->headers;
        self::assertEquals(GenerationPdfApiService::CONTENT_TYPE_APPLICATION_PDF, $headers->get('content-type'));
    }
}