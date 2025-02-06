<?php

namespace App\Traits;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\WidgetInterface;
use App\Enums\FileStoragePathEnum;
use Exception;

trait PathGeneratorTrait
{

    /**
     * @throws Exception
     */
    protected function getHttpHostFromPaymentData(OrderInterface $order): string
    {
        $paymentData = $order->payment_data;
        if (empty($paymentData['metadata']['http_host'])) {
            throw new Exception('Invalid payment data');
        }
        return str_replace('api.', '', $paymentData['metadata']['http_host']);
    }

    /**
     * URL на S3 к папке user_files для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getUserFilesDirUrl(WidgetInterface $widget): string
    {
        return self::getResourcesHttpHost() . FileStoragePathEnum::UserFiles->value . '/' . hash('sha256', $widget->id);
    }

    /**
     * Получить путь к папке с обложками для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getCustomCoverFilesPath(WidgetInterface $widget): string
    {
        return hash('sha256', $widget->id) . FileStoragePathEnum::CoverDir->value . FileStoragePathEnum::CustomCoverDir->value;
    }

    /**
     * Получить имя папки с шаблонами для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getBaseTemplatePath(WidgetInterface $widget): string
    {
        return basename($widget->mailTemplate->filename);
    }

    /**
     * Получить url S3 хранилища
     *
     * @return string
     */
    protected static function getResourcesHttpHost(): string
    {
        return config('filesystems.disks.s3.aws_site');
    }

    /**
     * URL на S3 к папке logo для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getLogoDirUrl(WidgetInterface $widget): string
    {
        $userFilesDir = $this->getUserFilesDirUrl($widget);
        return $userFilesDir . FileStoragePathEnum::LogoDir->value;
    }

    /**
     * URL на S3 к папке cover для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getCoverDirUrl(WidgetInterface $widget): string
    {
        $userFilesDir = $this->getUserFilesDirUrl($widget);
        return $userFilesDir . FileStoragePathEnum::CoverDir->value;
    }

    /**
     * URL на S3 к папке с кастомными обложками для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getCustomCoverDirUrl(WidgetInterface $widget): string
    {
        $userFilesDir = $this->getUserFilesDirUrl($widget);
        return $userFilesDir . FileStoragePathEnum::CoverDir->value . FileStoragePathEnum::CustomCoverDir->value;
    }

    /**
     * URL на S3 к папке favicon для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getFaviconDirUrl(WidgetInterface $widget): string
    {
        $userFilesDir = $this->getUserFilesDirUrl($widget);
        return $userFilesDir . '/' . FileStoragePathEnum::FaviconDir->value . '/';
    }

    /**
     * URL на S3 к папке background для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getBackgroundDirUrl(WidgetInterface $widget): string
    {
        $userFilesDir = $this->getUserFilesDirUrl($widget);
        return $userFilesDir . FileStoragePathEnum::BackgroundDir->value;
    }

    /**
     * URL на S3 к папке css для виджета
     *
     * @param WidgetInterface $widget
     * @return string
     */
    protected function getCssDirUrl(WidgetInterface $widget): string
    {
        $userFilesDir = $this->getUserFilesDirUrl($widget);
        return $userFilesDir . FileStoragePathEnum::CssDir->value;
    }

    /**
     * URL на S3 к папке с иконками скидок для виджета
     *
     * @return string
     */
    protected static function getSaleImageDirUrl(): string
    {
        return self::getResourcesHttpHost() . FileStoragePathEnum::SaleImageDir->value;
    }

}
