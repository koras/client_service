<?php

namespace App\Contracts\Models;

use App\ValueObjects\WidgetSaleLabelObj;
use App\ValueObjects\YooKassaDsnObj;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $mail_template_id
 * @property string $products
 * @property array $delivery_variants
 * @property MailTemplateInterface $mailTemplate
 * @property ?array $usage_rules
 * @property ?string $background_image
 * @property ?string $icons_color
 * @property ?array $faq
 * @property string $support_email
 * @property string $support_tel_number
 * @property string $favicon_image
 * @property bool $send_to_friend
 * @property string $offer
 * @property string $hiw_create_title
 * @property string $hiw_create_text
 * @property string $hiw_receive_title
 * @property string $hiw_receive_text
 * @property string $hiw_glad_title
 * @property string $hiw_glad_text
 * @property string $id_metric_yandex
 * @property string $id_metric_google
 * @property bool $custom_design
 * @property bool $can_wholesale
 * @property bool $enable_bx_corporate_script
 * @property string $bx_corporate_script
 * @property string $name
 * @property string $domain
 * @property string $meta_title
 * @property string $primary_color
 * @property string $primary_background_color
 * @property string $circles_color
 * @property string $stars_color
 * @property string $logotype_link
 * @property string $font
 * @property string $rules_text
 * @property array $covers
 * @property ?string $external_css_file
 * @property string $logotype_image
 * @property WidgetSaleLabelObj $sale_label
 * @property YooKassaDsnObj $ykassa_dsn
 * @property bool $ai_text_enable
 * @property bool $ai_image_enable
 * @property string $clientId
 * @property string $send_sms_pin
 * @property string $supplier_id
 * @property string $flexible_nominal
 * @property string $max_nominal
 * @property string $min_nominal
 * @property string $promo
 */
interface WidgetInterface
{
    public function mailTemplate(): BelongsTo;

    public function getProductsAsArray(): array;

    public function getWholesaleExamplePhone(): ?string;

    public function getWholesaleExampleEmail(): ?string;

    public function getBackgroundImage(): ?string;

    public function getCssFile(): ?string;

    public function getFaviconImage(): ?string;

    public function getLogotypeImage(): ?string;

}
