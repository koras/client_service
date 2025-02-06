<?php

namespace App\Providers;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\HistorySendInterface;
use App\Contracts\Models\LifeCycleInterface;
use App\Contracts\Models\MailTemplateInterface;
use App\Contracts\Models\NotificationInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Models\SystemSettingInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Models\WidgetUserInterface;
use App\Contracts\Repositories\SystemSettingRepositoryInterface;
use App\Repositories\SystemSettingRepository;
use Tests\ForTest\OrderItemRepositoryTEST;
use Tests\ForTest\OrderRepositoryTEST;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Models\PromoCodesInterface;

use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Repositories\MailTemplateRepositoryInterface;
use App\Contracts\Repositories\HistorySendRepositoryInterface;
use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Repositories\WidgetUserRepositoryInterface;
use App\Contracts\Repositories\WidgetUserWidgetRepositoryInterface;
use App\Contracts\Repositories\WidgetWidgetUserRepositoryInterface;
use App\Contracts\Repositories\LifeCycleRepositoryInterface;
use App\Contracts\Repositories\PromoCodesRepositoryInterface;

use App\Repositories\CertificateRepository;
use App\Repositories\HistorySendRepository;
use App\Repositories\MailTemplateRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\WidgetRepository;
use App\Repositories\WidgetUserRepository;
use App\Repositories\WidgetUserWidgetRepository;
use App\Repositories\WidgetWidgetUserRepository;
use App\Repositories\LifeCycleRepository;
use App\Repositories\PromoCodesRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {


        $this->app->bind(PromoCodesRepositoryInterface::class, function ($app) {
            return new PromoCodesRepository($app->make(PromoCodesInterface::class));
        });

        $this->app->bind(LifeCycleRepositoryInterface::class, function ($app) {
            return new LifeCycleRepository($app->make(LifeCycleInterface::class));
        });

        $this->app->bind(WidgetRepositoryInterface::class, function ($app) {
            return new WidgetRepository($app->make(WidgetInterface::class));
        });

        $this->app->bind(MailTemplateRepositoryInterface::class, function ($app) {
            return new MailTemplateRepository($app->make(MailTemplateInterface::class));
        });

        $this->app->bind(HistorySendRepositoryInterface::class, function ($app) {
            return new HistorySendRepository($app->make(HistorySendInterface::class));
        });

        $this->app->bind(CertificateRepositoryInterface::class, function ($app) {
            return new CertificateRepository($app->make(CertificateInterface::class));
        });

        $this->app->bind(NotificationRepositoryInterface::class, function ($app) {
            return new NotificationRepository($app->make(NotificationInterface::class));
        });

        $this->app->bind(WidgetUserRepositoryInterface::class, function ($app) {
            return new WidgetUserRepository($app->make(WidgetUserInterface::class));
        });

        $this->app->bind(SystemSettingRepositoryInterface::class, function ($app) {
            return new SystemSettingRepository($app->make(SystemSettingInterface::class));
        });

        $this->app->bind(WidgetWidgetUserRepositoryInterface::class, WidgetWidgetUserRepository::class);
        $this->app->bind(WidgetUserWidgetRepositoryInterface::class, WidgetUserWidgetRepository::class);

        if (App::environment('testing')) {
            $this->app->bind(OrderRepositoryInterface::class, function ($app) {
                return new OrderRepositoryTEST($app->make(OrderInterface::class));
            });

            $this->app->bind(OrderItemRepositoryInterface::class, function ($app) {
                return new OrderItemRepositoryTEST($app->make(OrderItemInterface::class));
            });

        } else {
            $this->app->bind(OrderRepositoryInterface::class, function ($app) {
                return new OrderRepository($app->make(OrderInterface::class));
            });

            $this->app->bind(OrderItemRepositoryInterface::class, function ($app) {
                return new OrderItemRepository($app->make(OrderItemInterface::class));
            });

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
