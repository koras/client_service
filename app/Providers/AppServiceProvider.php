<?php

namespace App\Providers;

use App\Contracts\Repositories\HistorySendRepositoryInterface;
use App\Contracts\Repositories\PromoCodesRepositoryInterface;
use App\Contracts\Services\BarcodeGeneratorInterface;
use App\Contracts\Services\BarcodeGeneratorPdf417Interface;
use App\Contracts\Services\Bitrix24\BitrixApiServiceInterface;
use App\Contracts\Services\Bitrix24\BitrixTaskDtoBuilderInterface;
use App\Contracts\Services\Bitrix24\BitrixTaskServiceInterface;
use App\Contracts\Services\CertificatePdfServiceInterface;
use App\Contracts\Services\CommitOrderPCDtoFactoryInterface;
use App\Contracts\Services\FileServiceInterface;
use App\Contracts\Services\FileStorageServiceInterface;
use App\Contracts\Services\HistorySendServiceInterface;
use App\Contracts\Services\LifeCycleServiceInterface;
use App\Contracts\Services\OrderItemDtoBuilderInterface;
use App\Contracts\Services\OrderSendingServiceInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Contracts\Services\PaymentServiceInterface;
use App\Contracts\Services\PCApiServiceInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\Contracts\Services\ProductsDataServiceInterface;
use App\Contracts\Services\QrCodeGeneratorInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\Contracts\Services\RabbitMQInterface;
use App\Contracts\Services\UsageRulesServiceInterface;
use App\Contracts\Services\ValidationServiceInterface;
use App\Contracts\Services\GenerationPdfApiServiceInterface;
use App\Contracts\Services\VendorOrderServiceInterface;
use App\Contracts\Services\WidgetInfoServiceInterface;
use App\Contracts\Services\XlsParseServiceInterface;

use App\Logging\Contracts\RabbitMQLogConnectorInterface;
use App\Logging\RabbitMQLogConnector;
use App\Repositories\HistorySendRepository;
use App\Repositories\PromoCodesRepository;
use App\Services\BarcodeGenerator;
use App\Services\BarcodeGeneratorPdf417;
use App\Services\Bitrix24\BitrixApiService;
use App\Services\Bitrix24\BitrixTaskDtoBuilder;
use App\Services\Bitrix24\BitrixTaskService;
use App\Services\CertificatePdfService;
use App\Services\CommitOrderPCDtoFactory;
use App\Services\FileService;
use App\Services\FileStorageService;
use App\Services\HistorySendService;
use App\Services\OrderItemDtoBuilder;
use App\Services\PCOrderService;
use App\Services\OrderSendingService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PCApiService;
use App\Services\ProductsApiService;
use App\Services\ProductsDataService;
use App\Services\QrCodeGenerator;
use App\Services\QueueProducer;
use App\Services\RabbitMQ;
use App\Services\UsageRulesService;
use App\Services\ValidationService;
use App\Services\GenerationPdfApiService;
use App\Services\WidgetInfoService;
use App\Services\XlsParseService;
use App\Services\LifeCycleService;
use Tests\ForTest\PaymentTESTService;
use Tests\ForTest\PCApiTESTService;
use Tests\ForTest\QueueTESTProducer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\CertificateServiceInterface;
use App\Contracts\Services\WidgetServiceInterface;

use App\Services\CertificateService;
use App\Services\WidgetService;
use Tests\ForTest\FileStorageTESTService;
use Tests\ForTest\GenerationPdfTESTApiService;
use Tests\ForTest\ProductsApiTESTService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RabbitMQLogConnectorInterface::class, RabbitMQLogConnector::class);

        $this->app->bind(WidgetServiceInterface::class, WidgetService::class);
        $this->app->bind(CertificateServiceInterface::class, CertificateService::class);
        $this->app->bind(FileServiceInterface::class, FileService::class);
        $this->app->bind(OrderSendingServiceInterface::class, OrderSendingService::class);
        $this->app->bind(XlsParseServiceInterface::class, XlsParseService::class);
        $this->app->bind(ValidationServiceInterface::class, ValidationService::class);
        $this->app->bind(BarcodeGeneratorInterface::class, BarcodeGenerator::class);
        $this->app->bind(BarcodeGeneratorPdf417Interface::class, BarcodeGeneratorPdf417::class);
        $this->app->bind(QrCodeGeneratorInterface::class, QrCodeGenerator::class);
        $this->app->bind(CertificatePdfServiceInterface::class, CertificatePdfService::class);
        $this->app->bind(WidgetInfoServiceInterface::class, WidgetInfoService::class);
        $this->app->bind(ProductsDataServiceInterface::class, ProductsDataService::class);
        $this->app->bind(UsageRulesServiceInterface::class, UsageRulesService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(OrderItemDtoBuilderInterface::class, OrderItemDtoBuilder::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(RabbitMQInterface::class, RabbitMQ::class);
        $this->app->bind(VendorOrderServiceInterface::class, PCOrderService::class);
        $this->app->bind(CommitOrderPCDtoFactoryInterface::class, CommitOrderPCDtoFactory::class);
        $this->app->bind(BitrixApiServiceInterface::class, BitrixApiService::class);
        $this->app->bind(BitrixTaskServiceInterface::class, BitrixTaskService::class);
        $this->app->bind(BitrixTaskDtoBuilderInterface::class, BitrixTaskDtoBuilder::class);
        $this->app->bind(HistorySendServiceInterface::class, HistorySendService::class);
        $this->app->bind(HistorySendRepositoryInterface::class, HistorySendRepository::class);
        $this->app->bind(PromoCodesRepositoryInterface::class, PromoCodesRepository::class);
        $this->app->bind(LifeCycleServiceInterface::class, LifeCycleService::class);
        $this->app->bind(LifeCycleServiceInterface::class, LifeCycleService::class);


        if (App::environment('testing')) {
            $this->app->bind(FileStorageServiceInterface::class, FileStorageTESTService::class);
            $this->app->bind(GenerationPdfApiServiceInterface::class, GenerationPdfTESTApiService::class);
            $this->app->bind(ProductsApiServiceInterface::class, ProductsApiTESTService::class);
            $this->app->bind(PCApiServiceInterface::class, PCApiTESTService::class);
            $this->app->bind(QueueProducerInterface::class, QueueTESTProducer::class);
            $this->app->bind(PaymentServiceInterface::class, PaymentTESTService::class);
        } else {
            $this->app->bind(FileStorageServiceInterface::class, FileStorageService::class);
            $this->app->bind(GenerationPdfApiServiceInterface::class, GenerationPdfApiService::class);
            $this->app->bind(ProductsApiServiceInterface::class, ProductsApiService::class);
            $this->app->bind(PCApiServiceInterface::class, PCApiService::class);
            $this->app->bind(QueueProducerInterface::class, QueueProducer::class);
            $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
