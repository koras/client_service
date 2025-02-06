<?php

namespace App\Services;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\UsageRulesServiceInterface;
use App\DTO\UsageRuleItemDto;
use Illuminate\Support\Collection;

class UsageRulesService implements UsageRulesServiceInterface
{
    private array $availableIcons;

    public function __construct()
    {
        $this->availableIcons = [
            'cart' => config('usage-rules-icons.CART'),
            'download_mob' => config('usage-rules-icons.CART'),
            'pin' => config('usage-rules-icons.CART'),
            'registration' => config('usage-rules-icons.CART'),
            'happy' => config('usage-rules-icons.CART'),
            'help' => config('usage-rules-icons.CART'),
            'download' => config('usage-rules-icons.CART'),
            'pay' => config('usage-rules-icons.CART'),
            'select' => config('usage-rules-icons.CART'),
            'cards' => config('usage-rules-icons.CART'),
            'money' => config('usage-rules-icons.CART'),
            'orders' => config('usage-rules-icons.CART'),
            'check' => config('usage-rules-icons.CART'),
        ];
    }

    /**
     * @param WidgetInterface $widget
     * @return Collection<UsageRuleItemDto>
     */
    public function convertUsageRulesToFrontend(WidgetInterface $widget): Collection
    {
        $result = new Collection();
        $usageRules = $widget->usage_rules;
        if (empty($usageRules)){
            return $result;
        }

        foreach ($usageRules as $rule){
            $icon = $this->availableIcons[$rule['icon']];
            $ruleDto = new UsageRuleItemDto($icon, $rule['text']);
//            $result[] = ['icon' => $icon, 'text' => $rule['text']];
            $result->add($ruleDto);
        }
        return $result;
    }

}