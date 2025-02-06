<?php

namespace Feature;

use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Enums\FileStoragePathEnum;
use App\Enums\ResponseStatusEnum;
use App\Repositories\WidgetRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadCustomCoverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }
    /**
     * @covers \App\Http\Controllers\ClientController::uploadCustomCover
     */
    public function testUploadCustomCover(): void
    {
        $widget = $this->createMockWidget();

        // Создаем заглушку для UploadedFile
        $file = UploadedFile::fake()->image('test.jpg');

        // Замокаем WidgetRepositoryInterface
        $widgetRepositoryMock = $this->createMock(WidgetRepository::class);
        $widgetRepositoryMock->expects($this->once())
            ->method('find')
            ->with($widget->id)
            ->willReturn($widget);

        // Привязываем мок репозитория к контейнеру зависимостей
        App::instance(WidgetRepositoryInterface::class, $widgetRepositoryMock);

        $data = [
            'file' => $file
        ];
        $response = $this->post('/widget/' . $widget->id . '/upload-cover', $data);
        $response->assertStatus(200);

        $json = $response->json();

        self::assertEquals(ResponseStatusEnum::ok->name, $json['status']);
        self::assertNull($json['message']);
        self::assertIsArray($json['data']);
        self::assertStringContainsString('.jpg', $json['data']['fileName']);
        self::assertStringContainsString(FileStoragePathEnum::CoverDir->value, $json['data']['fileUrl']);
        self::assertStringContainsString(FileStoragePathEnum::CustomCoverDir->value, $json['data']['fileUrl']);
        self::assertStringContainsString(FileStoragePathEnum::UserFiles->value, $json['data']['fileUrl']);
        self::assertStringStartsWith('https', $json['data']['fileUrl']);
    }

}
