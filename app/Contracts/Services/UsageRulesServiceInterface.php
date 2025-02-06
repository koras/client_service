<?php

namespace App\Contracts\Services;

use App\Contracts\Models\WidgetInterface;
use App\DTO\UsageRuleItemDto;
use Illuminate\Support\Collection;

interface UsageRulesServiceInterface
{
    /**
     * @param WidgetInterface $widget
     * @return Collection<UsageRuleItemDto>
     */
    public function convertUsageRulesToFrontend(WidgetInterface $widget): Collection;
}