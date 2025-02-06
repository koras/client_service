<?php

namespace Unit;

use App\Contracts\Services\UsageRulesServiceInterface;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UsageRulesServiceTest extends TestCase
{
    private UsageRulesServiceInterface $usageRulesService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usageRulesService = app(UsageRulesServiceInterface::class);
    }

    /**
     * @covers \App\Services\UsageRulesService::convertUsageRulesToFrontend
     * @return void
     */
    public function testConvertUsageRulesToFrontend(): void
    {
        $widget = $this->createMockWidget();
        $widget->usage_rules = self::TEST_USAGE_RULES_DATA;

        $usageRules = $this->usageRulesService->convertUsageRulesToFrontend($widget);
        self::assertInstanceOf(Collection::class, $usageRules);

        $firstRule = $usageRules->first();
        $lastRule = $usageRules->last();

        self::assertCount(count(self::TEST_USAGE_RULES_DATA), $usageRules);
        self::assertEquals(self::TEST_USAGE_RULES_DATA[0]['text'], $firstRule->text);
        self::assertEquals(self::TEST_USAGE_RULES_DATA[1]['text'], $lastRule->text);
        self::assertStringStartsWith('<svg ', $firstRule->icon);
        self::assertStringStartsWith('<svg ', $lastRule->icon);
    }



}
