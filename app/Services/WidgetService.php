<?php

namespace App\Services;

use App\Contracts\Services\WidgetInfoServiceInterface;
use App\Contracts\Services\WidgetServiceInterface;
use App\DTO\InfoWidgetDataDto;
use App\Http\Requests\GetWidgetInfoRequest;
use App\Http\Resources\WidgetInfoResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Contracts\Repositories\WidgetRepositoryInterface;


readonly class WidgetService implements WidgetServiceInterface
{
    public function __construct(
        private WidgetRepositoryInterface $widgetRepository,
        private WidgetInfoServiceInterface $widgetInfoService,
    )
    {
    }

    /**
     * @param GetWidgetInfoRequest $request
     * @param string|null $term
     * @return WidgetInfoResource
     */
    public function getWidgetInfo(GetWidgetInfoRequest $request, string $term = null): WidgetInfoResource
    {
        if(null === $term) {
            $term = $request->headers->get('refferer');
        }
        $widget = $this->widgetRepository->findWidgetByDomainOrId($term);
        if(null === $widget) {
            throw new NotFoundHttpException('widget Not found, with id or domain: ' . $term);
        }

        $activeNominal = $request->input('active_nominal', null);
        $productIdFilter = $request->input('product_id', null);

        $rules = $this->widgetInfoService->getRules($widget);
        $covers = $this->widgetInfoService->getSortedCovers($widget);

        $limits = $this->widgetInfoService->getLimits($widget);
        $amounts = $this->widgetInfoService->getAmounts($widget);
        $amounts = $this->widgetInfoService->addSaleLabelToAmounts($widget, $amounts);

        $usageRules = $this->widgetInfoService->getUsageRules($widget);

        $infoWidgetDto = new InfoWidgetDataDto(
            widget: $widget,
            rules: $rules,
            usageRules: $usageRules,
            activeNominal: (int) $activeNominal,
            covers: $covers,
            amounts: $amounts,
            limits: $limits
        );

        return new WidgetInfoResource($infoWidgetDto);
    }

}
