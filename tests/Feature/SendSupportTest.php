<?php

namespace Feature;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\Enums\DeliveryTypeEnum;
use App\Enums\ResponseStatusEnum;
use App\Repositories\WidgetRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class SendSupportTest extends TestCase
{
    private WidgetInterface $widget;
    private WidgetRepositoryInterface $widgetRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->widget = $this->createMockWidget();
        $this->widget->id = ProductsApiTESTService::WIDGET_ID;

        $this->widgetRepository = $this->createMock(WidgetRepository::class);
        App::instance(WidgetRepositoryInterface::class, $this->widgetRepository);

    }
    /**
     * @covers \App\Http\Controllers\SupportController::sendSupport
     */
    public function testSendSupportSuccess(): void
    {
        $this->widgetRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $data = [
            'Name' => 'Николай',
            'Email' => 'email@mail.ru',
            'Message' => 'Сообщение для поддержки',
            'Phone' => '+79001112233',
        ];
        $response = $this->post('/' . $this->widget->id . '/support', $data);
        $response->assertStatus(200);
        $json = $response->json();

        self::assertEquals(ResponseStatusEnum::ok->name, $json['status']);
        self::assertNull($json['message']);
        self::assertNull($json['data']);
    }

    /**
     * @covers \App\Http\Controllers\SupportController::sendSupport
     * @return void
     */
    public function testSendSupportFailRequest(): void
    {
        $this->widgetRepository
            ->expects($this->never())
            ->method('find')
            ->with($this->widget->id);

        $data = [
            'Name' => 'Николай',
            'Phone' => '+79001112233',
        ];
        $response = $this->post('/' . $this->widget->id . '/support', $data);
        $response->assertStatus(422);
        $json = $response->json();

        self::assertEquals(ResponseStatusEnum::error->name, $json['status']);
    }

}
