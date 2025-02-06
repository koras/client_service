<?php

namespace App\Providers;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\HistorySendInterface;
use App\Contracts\Models\MailTemplateInterface;
use App\Contracts\Models\NotificationInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Models\PromoCodesInterface;
use App\Contracts\Models\SystemSettingInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Models\WidgetUserInterface;
use App\Contracts\Models\LifeCycleInterface;
use App\Models\Certificate;
use App\Models\HistorySend;
use App\Models\MailTemplate;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCodes;
use App\Models\SystemSetting;
use App\Models\Widget;
use App\Models\WidgetUser;
use Illuminate\Support\ServiceProvider;
use App\Models\LifeCycle;

class ModelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CertificateInterface::class, Certificate::class);
        $this->app->bind(HistorySendInterface::class, HistorySend::class);
        $this->app->bind(MailTemplateInterface::class, MailTemplate::class);
        $this->app->bind(NotificationInterface::class, Notification::class);
        $this->app->bind(OrderInterface::class, Order::class);
        $this->app->bind(OrderItemInterface::class, OrderItem::class);
        $this->app->bind(WidgetInterface::class, Widget::class);
        $this->app->bind(WidgetUserInterface::class, WidgetUser::class);
        $this->app->bind(SystemSettingInterface::class, SystemSetting::class);
        $this->app->bind(LifeCycleInterface::class, LifeCycle::class);
        $this->app->bind(PromoCodesInterface::class, PromoCodes::class);
    }
}
