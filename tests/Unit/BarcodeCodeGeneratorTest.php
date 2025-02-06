<?php

namespace Unit;

use App\Contracts\Services\BarcodeGeneratorInterface;
use Tests\TestCase;

class BarcodeCodeGeneratorTest extends TestCase
{
    private string $data;
    private string $base64StartString;

    private string $assertingBase64String;

    private BarcodeGeneratorInterface $barcodeGenerator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->base64StartString = 'data:image/png;base64,';
        $this->data = 'MGC208967200';
        $this->assertingBase64String = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAbMAAABQAQMAAACphAlcAAAABlBMVEX///8AAABVwtN+AAAAAXRSTlMAQObYZgAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAGFJREFUWIXty7ENgCAQQNEzFna6AAlr0LESbOAEuhIda5AwwXUUxNMRHOAnr30yU5Bs/oxuKR9/xW61pdh1mJb1LnPfZrb+BK/DJZlH8Vqb8Hg8Ho/H4/F4PB6Px+Pxfr0X88t3qGImyZwAAAAASUVORK5CYII=';
        $this->barcodeGenerator = app(BarcodeGeneratorInterface::class);
    }

    /**
     * @covers \App\Services\BarcodeGenerator::getBase64
     * @return void
     */
    public function testGetBase64ReturnsNullWhenCodeNotGenerated()
    {
        $result = $this->barcodeGenerator->getBase64();
        $this->assertNull($result);
    }

    /**
     * @covers \App\Services\BarcodeGenerator::generate
     * @covers \App\Services\BarcodeGenerator::getBase64
     * @return void
     */
    public function testGetBase64ReturnsBase64StringWhenCodeGenerated()
    {
        $result = $this->barcodeGenerator
            ->generate($this->data)
            ->getBase64();

        self::assertStringStartsWith($this->base64StartString, $result);
        self::assertEquals($this->assertingBase64String, $result);
    }
}
