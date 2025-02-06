<?php

namespace App\Models;

use App\Casts\Serializeble;
use App\Casts\WidgetSaleLabel;
use App\Casts\YooKassaDsn;
use App\Contracts\Models\WidgetInterface;
use App\Traits\PathGeneratorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Widget extends Model implements WidgetInterface
{
    use HasFactory;
    use PathGeneratorTrait;

    protected $table = 'widget';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'client_id', 'mail_template_id', 'name', 'domain', 'ykassa_dsn',
        'primary_background_color', 'primary_color', 'icons_color',
        'circles_color', 'stars_color', 'ribbon_color', 'logotype_image',
        'logotype_link', 'favicon_image', 'meta_title', 'font',
        'external_css_file', 'support_tel_number', 'support_email',
        'delivery_variants', 'send_to_friend', 'order_service', 'offer',
        'faq', 'hiw_create_title', 'hiw_create_text', 'hiw_receive_title',
        'hiw_receive_text', 'hiw_glad_title', 'hiw_glad_text', 'rules_text',
        'products', 'covers', 'order_service_dsn_login',
        'order_service_dsn_password', 'order_service_dsn_url',
        'order_service_dsn_port', 'id_metric_google', 'id_metric_yandex',
        'custom_design', 'usage_rules', 'background_image', 'sale_image',
        'sale_color', 'sale_product_ids', 'meta_description',
        'can_wholesale', 'bx_corporate_script', 'enable_bx_corporate_script', 'sms_pin', 'supplier_id', 'flexible_nominal', 'max_nominal', 'min_nominal', 'promo'
    ];

    protected $casts = [
        'delivery_variants' => Serializeble::class,
        'covers' => Serializeble::class,
        'sale_product_ids' => 'json',
        'usage_rules' => 'json',
        'faq' => Serializeble::class,
        'sale_label' => WidgetSaleLabel::class,
        'ykassa_dsn' => YooKassaDsn::class,
    ];

    /**
     * @return BelongsTo
     */
    public function mailTemplate(): BelongsTo
    {
        return $this->belongsTo(MailTemplate::class, 'mail_template_id', 'id');
    }

    /**
     * @return array
     */
    public function getProductsAsArray(): array
    {
        return explode(',', $this->products);
    }

    public function getWholesaleExamplePhone(): ?string
    {
        return $this->can_wholesale ? self::getResourcesHttpHost() . config('wholesale.example_file_phone') : null;
    }

    public function getWholesaleExampleEmail(): ?string
    {
        return $this->can_wholesale ? self::getResourcesHttpHost() . config('wholesale.example_file_email') : null;
    }

    public function getBackgroundImage(): ?string
    {
        return $this->background_image ? $this->getBackgroundDirUrl($this) . $this->background_image : null;
    }

    public function getCssFile(): ?string
    {
        return $this->external_css_file ? $this->getCssDirUrl($this) . $this->external_css_file : null;
    }

    public function getFaviconImage(): ?string
    {
        return $this->favicon_image ? $this->getFaviconDirUrl($this) . $this->favicon_image : null;
    }

    public function getLogotypeImage(): ?string
    {
        return $this->logotype_image ? $this->getLogoDirUrl($this) . $this->logotype_image : null;
    }
}
