<?php

namespace Tests;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\MailTemplateInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Models\WidgetInterface;
use App\Enums\DeliveryTypeEnum;
use App\Models\MailTemplate;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery\MockInterface;
use Tests\ForTest\ProductsApiTESTService;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected const string WIDGET_COVER_STRING = 'a:6:{i:0;a:2:{s:9:"sortOrder";s:1:"1";s:4:"file";s:23:"card1-61a7877a06408.png";}i:1;a:2:{s:9:"sortOrder";s:1:"0";s:4:"file";s:23:"card3-61a7877a06fbf.png";}i:2;a:2:{s:9:"sortOrder";s:1:"2";s:4:"file";s:23:"card6-61a7877a075b8.png";}i:3;a:2:{s:9:"sortOrder";s:1:"3";s:4:"file";s:23:"card4-61a7877a07246.png";}i:4;a:2:{s:9:"sortOrder";s:1:"4";s:4:"file";s:23:"card5-61a7877a07405.png";}i:5;a:2:{s:9:"sortOrder";s:1:"5";s:4:"file";s:23:"card2-61a7877a06d27.png";}}';
    protected const string WIDGET_DELIVERY_VARIANTS_STRING = 'a:2:{i:0;s:5:"Email";i:1;s:3:"SMS";}';

    protected const array TEST_USAGE_RULES_DATA = [
        [
            'icon' => 'cards',
            'text' => 'some description 1'
        ],
        [
            'icon' => 'cards',
            'text' => 'some description 12'
        ]
    ];

    public const array REQUEST_ORDER_ITEM = [
        "certificate" => [
            "template_id" => "0",
            "template_src" => "9eefbd263f53941a0d4d3a83131f367d.png",
            "message" => "happy birth",
            "basket_key" => "d171476a-f690-44a5-9a79-234566f488eb",
            "product" => [
                "quantity" => "1",
                "product_id" => ProductsApiTESTService::PRODUCT_ID_1000,
                "amount" => "1000"
            ],
            "delivery_type" => ["sms"]
        ],
        "recipient" => [
            "type" => "other",
            "name" => "Ivan",
            "msisdn" => "+7 (910) 281-47-60"
        ],
        "time_to_send" => "",
        "utm" => "null",
        "sender" => [
            "name" => "TESTIVSAN",
            "email" => "cla55ik@yandex.ru"
        ]
    ];

    protected function getFakePaymentData(): array
    {
        return [
            "id" => "2d2f934e-000f-5000-9000-1099aea90944",
            "paid" => false,
            "test" => true,
            "amount" => [
                "value" => "1000.00",
                "currency" => "RUB"
            ],
            "status" => "pending",
            "metadata" => [
                "host" => "api.widget.mgc-loyalty.ru",
                "order_id" => "5f15ad9c-67b4-4c38-ac2a-f0bb4d6113e9",
                "http_host" => "api.widget.mgc-loyalty.ru"
            ],
            "recipient" => [
                "account_id" => "857636",
                "gateway_id" => "1914620"
            ],
            "transfers" => [],
            "created_at" => "2024-01-09T17:17:02.975+00:00",
            "refundable" => false,
            "description" => "u041eu043fu043bu0430u0442u0430 u0437u0430u043au0430u0437u0430 u21165f15ad9c-67b4-4c38-ac2a-f0bb4d6113e9 u0434u043bu044f cla55ik@yandex.ru",
            "confirmation" => [
                "type" => "embedded",
                "confirmation_token" => "ct-2d2f934e-000f-5000-9000-1099aea90944"
            ]
        ];
    }

    protected function createMockOrder(): MockInterface
    {
        $widget = $this->createMockWidget();
        $order = \Mockery::mock(OrderInterface::class);
        $order->id = fake()->uuid;
        $order->widget_id = $widget->id;
        $order->widget = $widget;
        $order->payment_data = $this->getFakePaymentData();

        return $order;
    }

    protected function createMockOrderItem(): MockInterface
    {
        $order = $this->createMockOrder();

        $orderItem = \Mockery::mock(OrderItemInterface::class);
        $orderItem->order = $order;
        $orderItem->widgetOrder = $order;
        $orderItem->widget_order_id = $order->id;
        $orderItem->id = fake()->numerify('####');
        $orderItem->message = fake()->word;
        $orderItem->basket_key = fake()->uuid;
        $orderItem->product_id = fake()->numerify('#######');
        $orderItem->quantity = fake()->numerify('#');
        $orderItem->widget_order_id = $order->id;
        $orderItem->tiberium_order_id = fake()->numerify('#############');
        $orderItem->sender_email = fake()->email;
        $orderItem->sender_name = fake()->name;
        $orderItem->recipient_email = fake()->email;
        $orderItem->recipient_msisdn = fake()->phoneNumber;
        $orderItem->recipient_name = fake()->name;
        $orderItem->recipient_type = fake()->randomElement(DeliveryTypeEnum::values());

        return $orderItem;
    }

    protected function createMockCertificate(): MockInterface
    {
        $orderItem = $this->createMockOrderItem();

        $certificate = \Mockery::mock(CertificateInterface::class);
        $certificate->id = fake()->uuid;
        $certificate->orderItem = $orderItem;
        $certificate->order_item_id = $orderItem->id;
        $certificate->serial = fake()->uuid;
        $certificate->cover_path = fake()->word . '.png';
        $certificate->expire_at = fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d H:i:s');
        $certificate->amount = fake()->numberBetween(1,50) * 100;
        $certificate->pin = fake()->numerify('####');

        return $certificate;
    }

    protected function createMockWidget(): MockInterface
    {
        $fakeMailTemplate = $this->createMockMailTemplate();
        $widget = \Mockery::mock(WidgetInterface::class);
        $widget->id = fake()->uuid;
        $widget->clientId = fake()->uuid;
        $widget->mailTemplateId = $fakeMailTemplate->id;
        $widget->mailTemplate = $fakeMailTemplate;
        $widget->usageRules = null;
        $widget->backgroundImage = null;
        $widget->iconsColor = '#310e6d';
        $widget->name = fake()->word;
        $widget->faq = fake()->word();
        $widget->favicon_image = fake()->word();
        $widget->support_email = fake()->email;
        $widget->support_tel_number = fake()->phoneNumber;
        $widget->rules_text = fake()->text;
        $widget->covers = unserialize(self::WIDGET_COVER_STRING);
        $widget->delivery_variants = unserialize(self::WIDGET_DELIVERY_VARIANTS_STRING);
        $widget->domain = fake()->word();
        $widget->meta_title = fake()->word();
        $widget->send_to_friend = fake()->boolean;
        $widget->offer = fake()->text;
        $widget->hiw_create_title = fake()->text;
        $widget->hiw_create_text = fake()->text;
        $widget->hiw_receive_title = fake()->text;
        $widget->hiw_receive_text = fake()->text;
        $widget->hiw_glad_title = fake()->text;
        $widget->hiw_glad_text = fake()->text;
        $widget->id_metric_yandex = fake()->numerify('######');
        $widget->id_metric_google = fake()->numerify('######');
        $widget->custom_design = fake()->boolean;
        $widget->icons_color = fake()->hexColor;
        $widget->can_wholesale = fake()->boolean;
        $widget->corporate_purchase = fake()->boolean;
        $widget->enable_bx_corporate_script = fake()->boolean;
        $widget->bx_corporate_script = fake()->text;
        $widget->primary_color = fake()->hexColor;
        $widget->primary_background_color = fake()->hexColor;
        $widget->circles_color = fake()->hexColor;
        $widget->stars_color = fake()->hexColor;
        $widget->logotype_link = fake()->url();
        $widget->font = fake()->word;
        $widget->ai_text_enable = fake()->boolean;
        $widget->ai_image_enable = fake()->boolean;

        $widget
            ->shouldReceive('getWholesaleExamplePhone')
            ->andReturn(fake()->url());
        $widget
            ->shouldReceive('getWholesaleExampleEmail')
            ->andReturn(fake()->url());
        $widget
            ->shouldReceive('getLogotypeImage')
            ->andReturn(fake()->url());
        $widget
            ->shouldReceive('getFaviconImage')
            ->andReturn(fake()->url());
        $widget
            ->shouldReceive('getCssFile')
            ->andReturn(fake()->url());
        $widget
            ->shouldReceive('getBackgroundImage')
            ->andReturn(fake()->url());

        return $widget;
    }

    protected function createMockMailTemplate(): MailTemplateInterface
    {
        $fakeName = 'basePin';

        $mailTemplate = new MailTemplate([
            'id' => fake()->numerify('##'),
            'name' => $fakeName,
            'filename' => '/app/public/templates/_base_pin',
            'is_enabled' => true,
            'sort_order' => 0,
        ]);

        return $mailTemplate;
    }

}
