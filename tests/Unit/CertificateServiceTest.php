<?php

namespace Unit;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Services\CertificateServiceInterface;
use App\Enums\DeliveryTypeEnum;
use App\Http\Resources\ShowCertificateResource;
use App\Repositories\CertificateRepository;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CertificateServiceTest extends TestCase
{
    private CertificateServiceInterface $certificateService;
    private CertificateInterface $certificate;
    private CertificateRepositoryInterface $certificateRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->certificate = $this->createMockCertificate();
        $this->certificateRepository = $this->createMock(CertificateRepository::class);
        App::instance(CertificateRepositoryInterface::class, $this->certificateRepository);
        $this->certificateService = app(CertificateServiceInterface::class);
    }

    /**
     * @covers \App\Services\CertificateService::getDataForShowCertificate
     * @return void
     */
    public function testGetDataForShowCertificate(): void
    {
        $this->certificateRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->certificate->id)
            ->willReturn($this->certificate);

        $dataResource = $this->certificateService->getDataForShowCertificate($this->certificate->id);

        self::assertInstanceOf(ShowCertificateResource::class, $dataResource);
        $arrayData = $dataResource->jsonSerialize();
        self::assertIsArray($arrayData);
        self::assertArrayHasKey('cover', $arrayData);
        self::assertArrayHasKey('sender_name', $arrayData);
        self::assertArrayHasKey('recipient_name', $arrayData);
        self::assertArrayHasKey('recipient_type', $arrayData);
        self::assertArrayHasKey('message', $arrayData);
        self::assertArrayHasKey('faq', $arrayData);
        self::assertArrayHasKey('favicon', $arrayData);
        self::assertArrayHasKey('support_email', $arrayData);
        self::assertArrayHasKey('support_msisdn', $arrayData);
        self::assertArrayHasKey('template', $arrayData);

        $recipientType = DeliveryTypeEnum::tryFrom($arrayData['recipient_type']);
        self::assertInstanceOf(DeliveryTypeEnum::class, $recipientType);

        self::assertEquals($this->certificate->id, $arrayData['id']);
        self::assertEquals($this->certificate->serial, $arrayData['serial']);
        self::assertEquals($this->certificate->expire_at, $arrayData['expire_at']);
        self::assertEquals($this->certificate->amount, $arrayData['amount']);
        self::assertEquals($this->certificate->pin, $arrayData['pin']);

        self::assertStringStartsWith('data:image/png;base64', $arrayData['qr']);
        self::assertStringStartsWith('data:image/png;base64', $arrayData['barcode']);
    }



}
