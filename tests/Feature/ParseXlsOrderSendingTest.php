<?php

namespace Feature;

use App\Enums\DeliveryTypeEnum;
use App\Enums\ResponseStatusEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParseXlsOrderSendingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }
    /**
     * @covers \App\Http\Controllers\ClientController::parseFromXlsx
     */
    public function testParseXlsWithEmail(): void
    {
        $path = 'tests/ForTest/sending/email.xls';
        // Создаем фейковый загруженный файл
        $file = UploadedFile::fake()->createWithContent(
            basename($path),
            file_get_contents($path)
        );

        $data = [
            'file' => $file,
            'recipientType' => DeliveryTypeEnum::Email->value
        ];
        $response = $this->post('/widget/order/xls', $data);
        $response->assertStatus(200);

        $json = $response->json();

        self::assertEquals(ResponseStatusEnum::ok->name, $json['status']);
        self::assertNull($json['message']);
        self::assertIsArray($json['data']);
        self::assertCount(2, $json['data']);
        $firstRow = $json['data'][0];
        self::assertIsArray($firstRow);
        self::assertEquals(0, $firstRow['index']);
        self::assertEquals('Миша', $firstRow['name']);
        self::assertEquals('ivan5420@yandex.ru', $firstRow['recipient']);
        self::assertEquals('Поздравление  для сертификата', $firstRow['message']);
    }

    /**
     * @covers \App\Http\Controllers\ClientController::parseFromXlsx
     * @return void
     */
    public function testParseXlsWithPhone(): void
    {
        $path = 'tests/ForTest/sending/phone.xls';
        // Создаем фейковый загруженный файл
        $file = UploadedFile::fake()->createWithContent(
            basename($path),
            file_get_contents($path)
        );

        $data = [
            'file' => $file,
            'recipientType' => DeliveryTypeEnum::Phone->value
        ];
        $response = $this->post('/widget/order/xls', $data);
        $response->assertStatus(200);

        $json = $response->json();

        self::assertEquals(ResponseStatusEnum::ok->name, $json['status']);
        self::assertNull($json['message']);
        self::assertIsArray($json['data']);
        self::assertCount(2, $json['data']);
        $firstRow = $json['data'][0];
        self::assertIsArray($firstRow);
        self::assertEquals(0, $firstRow['index']);
        self::assertEquals('Витя', $firstRow['name']);
        self::assertEquals('+79001112233', $firstRow['recipient']);
        self::assertEquals('Поздравление  для сертификата', $firstRow['message']);
    }

    /**
     * @covers \App\Http\Controllers\ClientController::parseFromXlsx
     * @return void
     */
    public function testFailParseXlsFromType()
    {
        $path = 'tests/ForTest/sending/phone.xls';
        // Создаем фейковый загруженный файл
        $file = UploadedFile::fake()->createWithContent(
            basename($path),
            file_get_contents($path)
        );

        $data = [
            'file' => $file,
            'recipientType' => DeliveryTypeEnum::Email->value
        ];
        $response = $this->post('/widget/order/xls', $data);
        $response->assertStatus(422);

        $json = $response->json();
        self::assertEquals(ResponseStatusEnum::error->name, $json['status']);
    }

}
