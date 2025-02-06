<?php

namespace App\Contracts\Services;

use App\Contracts\Models\WidgetInterface;
use App\DTO\UsageRuleItemDto;
use Illuminate\Support\Collection;

interface WidgetInfoServiceInterface
{
    public function addSaleLabelToAmounts(WidgetInterface $widget, array $amounts): array;

    public function getSortedCovers(WidgetInterface $widget): array;

    public function getRules(WidgetInterface $widget): string;

    public function getLimits(WidgetInterface $widget): array;

    public function getAmounts(WidgetInterface $widget): array;

    /**
     * @param WidgetInterface $widget
     * @return Collection<UsageRuleItemDto>
     */
    public function getUsageRules(WidgetInterface $widget): Collection;

}
