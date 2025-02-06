<?php

namespace App\Http\Resources;

use App\DTO\InfoWidgetDataDto;
use App\Traits\PathGeneratorTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class WidgetInfoResource extends JsonResource
{
    use PathGeneratorTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var InfoWidgetDataDto $this */
        $widget = $this->widget;

        return [
            'main' => [
                'version' => 1.0,
                'uuid' => $widget->id,
                'name' => $widget->name,
                'domain' => $widget->domain,
                'meta_title' => $widget->meta_title,
                'support_tel_number' => $widget->support_tel_number,
                'support_email' => $widget->support_email,
                'delivery_variants' => $widget->delivery_variants,
                'send_to_friend' => $widget->send_to_friend,
                'offer_text' => $widget->offer,
                'faq' => $widget->faq,
                'rules_text' => $this->rules,
                'hiw_create_title' => $widget->hiw_create_title,
                'hiw_create_text' => $widget->hiw_create_text,
                'hiw_receive_title' => $widget->hiw_receive_title,
                'hiw_receive_text' => $widget->hiw_receive_text,
                'hiw_glad_title' => $widget->hiw_glad_title,
                'hiw_glad_text' => $widget->hiw_glad_text,
                'template' => $widget->mailTemplate->name,
                'id_metric_yandex' => $widget->id_metric_yandex,
                'id_gtm' => $widget->id_metric_google,
                'custom_design' => $widget->custom_design,
                'usage_rules' => $this->usageRules,
                'icons_color' => $widget->icons_color,
                'active_nominal' => $this->activeNominal,
                'can_wholesale' => $widget->can_wholesale,
                'wholesale_example_phone' => $widget->getWholesaleExamplePhone(),
                'wholesale_example_email' => $widget->getWholesaleExampleEmail(),
                'corporate_purchase' => $widget->enable_bx_corporate_script,
                'script_from_admin' => $widget->enable_bx_corporate_script ? $widget->bx_corporate_script : null,
                'ai_image_enable' => $widget->ai_image_enable,
                'ai_text_enable' => $widget->ai_text_enable,
                'sms_pin' => $widget->send_sms_pin,
                'supplier_id' => $widget->supplier_id,
                'flexible_nominal' => $widget->flexible_nominal,
                'min_nominal' => $widget->min_nominal,
                'max_nominal' => $widget->max_nominal,
                'promo' => $widget->promo,
            ],
            'style' => [
                'primary_color' => $widget->primary_color,
                'primary_background_color' => $widget->primary_background_color,
                'icons_color' => $widget->icons_color,
                'circles_color' => $widget->circles_color,
                'stars_color' => $widget->stars_color,
                'logotype_image' => $widget->getLogotypeImage(),
                'logotype_link' => $widget->logotype_link,
                'favicon_image' => $widget->getFaviconImage(),
                'font' => $widget->font,
                'external_css_file' => $widget->getCssFile(),
                'products' => [
                    'covers' => $this->covers,
                ],
                'amounts' => $this->amounts,
                'limits' => $this->limits,
                'backgroundImage' => $widget->getBackgroundImage(),
            ]
        ];
    }
}
