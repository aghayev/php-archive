<?php

namespace App;

use DateTimeImmutable;
use Domain\Vendor\MockVendorDataSourceInterface;
use Domain\Vendor\Vendor;
use PHPUnit\Framework\TestCase;
use Utils\ClockMock;

final class AppTest extends TestCase
{
    private App $underTest;
    private MockVendorDataSourceInterface $mockDataSource;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockDataSource = new MockVendorDataSourceInterface([
            new Vendor('Vendor 1', 'Great vendor', ['Item 1', 'Item 2'], 2, 10)
        ]);

        $middleware = [
            'search' => [
                'App\Middleware\SearchMiddleware'
            ],
        ];
        $this->underTest = new App($middleware, $this->mockDataSource);

        ClockMock::freezeTime(new DateTimeImmutable('2020-01-01 12:00'));
    }

    public function tearDown()
    {
        ClockMock::unfreezeTime();
    }

    public function testSearch_DeliveryDateAtVendorNotice_HeadcountBelowVendorMax_FindsVendor(): void
    {
        $response = $this->underTest->run('search', ['2020-01-01', '14:00', 5]);

        $this->assertEquals("Vendor 1 - Great vendor: Item 1, Item 2\n", $response);
    }

    public function testSearch_DeliveryDateAboveVendorNotice_HeadcountBelowVendorMax_FindsVendor(): void
    {
        $response = $this->underTest->run('search', ['2020-01-01', '14:01', 5]);

        $this->assertEquals("Vendor 1 - Great vendor: Item 1, Item 2\n", $response);
    }

    public function testSearch_DeliveryDateBelowVendorNotice_HeadcountBelowVendorMax_FindsNothing(): void
    {
        $response = $this->underTest->run('search', ['2020-01-01', '13:59', 5]);

        $this->assertEquals("\n", $response);
    }

    public function testSearch_MultipleVendorsAvailable_HeadcountBelowAtVendorMax_FindsAllAvailableVendors(): void
    {
        $this->mockDataSource->setVendors([
            new Vendor('Vendor 1', 'Great vendor', ['Item 1', 'Item 2'], 2, 10),
            new Vendor('Vendor 2', 'Not so great vendor', ['Item 3', 'Item 4'], 4, 50),
        ]);

        $response = $this->underTest->run('search', ['2020-02-01', '14:01', 10]);

        $this->assertEquals("Vendor 1 - Great vendor: Item 1, Item 2\nVendor 2 - Not so great vendor: Item 3, Item 4\n", $response);
    }

    public function testSearchVendors_IncorrectDateQuery_ThrowsException(): void
    {
        $response = $this->underTest->run('search', ['not-a-date', '13:59', 5]);

        $this->assertEquals("Problem when processing request: date must be a string in YYYY-MM-DD format\n", $response);
    }

    public function testSearch_SearchTermBurger_FindsVendor(): void
    {
        $this->mockDataSource->setVendors([
            new Vendor('Vendor 1', 'Great vendor', ['Item 1', 'Item 2', 'Item burger'], 2, 5),
            new Vendor('Vendor 2', 'Not so great vendor', ['Item 3', 'Item 4'], 4, 10),
        ]);

        $response = $this->underTest->run('search', ['2020-02-01', '14:01', 5, "burger"]);

        $this->assertEquals("Vendor 1 - Great vendor: Item 1, Item 2, Item burger\n", $response);
    }
}