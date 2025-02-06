<?php

namespace App\Services;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\ProductsDataServiceInterface;
use App\Contracts\Services\UsageRulesServiceInterface;
use App\Contracts\Services\WidgetInfoServiceInterface;
use App\DTO\ProductDto;
use App\DTO\UsageRuleItemDto;
use App\Enums\WidgetDeliveryVariantsEnum;
use App\Traits\PathGeneratorTrait;
use App\ValueObjects\WidgetSaleLabelObj;
use Illuminate\Support\Collection;

class WidgetInfoService implements WidgetInfoServiceInterface
{
    use PathGeneratorTrait;

    public function __construct(
        private readonly ProductsDataServiceInterface $productsDataService,
        private readonly UsageRulesServiceInterface $usageRulesService,
    )
    {
    }

    public function getSortedCovers(WidgetInterface $widget): array
    {
        $covers = [];
        foreach ($widget->covers as $cover) {
            $covers[] = [
                'id' => $cover['sortOrder'],
                'src' => $this->getCoverDirUrl($widget) . $cover['file']
            ];
        }

        uasort($covers, function ($a,$b) { return $a['id'] >= $b['id'] ? 1 : -1; } );
        return $covers;
    }

    public function getRules(WidgetInterface $widget): string
    {
        return str_replace("\r\n", "<br>", $widget->rules_text);
    }

    public function getLimits(WidgetInterface $widget): array
    {
        $limits = [];
        $firstDeliveryVariant = $widget->delivery_variants[0];
        if (WidgetDeliveryVariantsEnum::isCharity($firstDeliveryVariant)) {
            // TODO: $tiberium->getCardsList();
            $limits['min'] = 100;
            $limits['max'] = 5000;
        }

        return $limits;
    }

    public function getAmounts(WidgetInterface $widget): array
    {
        $amounts = [];
        $availableProducts = $this->productsDataService->getAvailableProductsByWidget($widget);
        $sortedProducts = $this->productsDataService->sortProductsByWidgetSettings($widget, $availableProducts);

        /** @var ProductDto $product */
        foreach ($sortedProducts as $product) {

            $nominal = $widget->flexible_nominal ? 0 : $product->nominal;
            $price = $widget->flexible_nominal ? 0 :$product->price;

                $amounts[] = [
                'id' => $product->id,
                'amount' => $price,
                'nominal' => $nominal,
                'currency' => $product->currency,
                'saleImage' => null,
                'saleColor' => null
            ];
        }

        return $amounts;
    }

    public function addSaleLabelToAmounts(WidgetInterface $widget, array $amounts): array
    {
        /** @var WidgetSaleLabelObj $saleLabel */
        $saleLabel = $widget->sale_label;
        if (is_null($saleLabel) || $saleLabel->issetNullProperties()) {
            return $amounts;
        }

        foreach ($amounts as $index => $amountData) {
            if (in_array($amountData['id'], $saleLabel->saleProductIds)) {
                $amounts[$index]['saleImage'] = $saleLabel->saleImage;
                $amounts[$index]['saleColor'] = $saleLabel->saleColor;
            }
        }

        return $amounts;
    }

    /**
     * @param WidgetInterface $widget
     * @return Collection<UsageRuleItemDto>
     */
    public function getUsageRules(WidgetInterface $widget): Collection
    {
        return $this->usageRulesService->convertUsageRulesToFrontend($widget);
    }

}
