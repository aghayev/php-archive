<?php

namespace Domain\Vendor;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VendorTest extends TestCase
{
    public function testGetHeader_ReturnsNameAndDescriptionConcatWithDash()
    {
        $vendor = new Vendor('Patty & Bun', 'Best burgers in London', [], 3, 10);

        $this->assertEquals('Patty & Bun - Best burgers in London', $vendor->getHeader());
    }

    public function testGetMenu_VendorWithItems_ReturnsItemsConcatWithComma()
    {
        $vendor = new Vendor('Vendor', 'Good food', ['Burger', 'Chicken'], 3, 10);

        $this->assertEquals('Burger, Chicken', $vendor->getMenu());
    }

    public function testGetMenu_VendorWithoutItems_ReturnsEmptyString()
    {
        $vendor = new Vendor('Vendor', 'Good food', [], 3, 10);

        $this->assertEquals('', $vendor->getMenu());
    }

    public function testCanDeliver_DeliveryDateBeforeNotice_ReturnsTrue()
    {
        $vendor = new Vendor('Vendor', 'Good food', [], 3, 10);

        $deliverDate = new DateTimeImmutable('2020-01-10 12:30');
        $nowDate = new DateTimeImmutable('2020-01-10 8:30');

        $this->assertTrue($vendor->canDeliver($deliverDate, $nowDate));
    }

    public function testCanDeliver_DeliveryDateAtNotice_ReturnsTrue()
    {
        $vendor = new Vendor('Vendor', 'Good food', [], 3, 10);

        $deliverDate = new DateTimeImmutable('2020-01-10 12:30');
        $nowDate = new DateTimeImmutable('2020-01-10 9:30');

        $this->assertTrue($vendor->canDeliver($deliverDate, $nowDate));
    }

    public function testCanDeliver_DeliveryDateAfterNotice_ReturnsFalse()
    {
        $vendor = new Vendor('Vendor', 'Good food', [], 3, 10);

        $deliverDate = new DateTimeImmutable('2020-01-10 12:30');
        $nowDate = new DateTimeImmutable('2020-01-10 9:31');

        $this->assertFalse($vendor->canDeliver($deliverDate, $nowDate));
    }

    public function testCanHeadcount_DeliveryHeadcountBelowMax_ReturnsTrue()
    {
        $vendor = new Vendor('Vendor', 'Good food', [], 3, 10);

        $this->assertTrue($vendor->canHeadcount(5));
    }

    public function testCanHeadcount_DeliveryHeadcountAtMax_ReturnsTrue()
    {
        $vendor = new Vendor('Vendor', 'Good food', [], 3, 10);

        $this->assertTrue($vendor->canHeadcount(10));
    }

    public function testCanHeadcount_DeliveryHeadcountAboveMax_ReturnsFalse()
    {
        $vendor = new Vendor('Vendor', 'Good food', [], 3, 10);

        $this->assertFalse($vendor->canHeadcount(15));
    }

    public function testmatchTerm_SearchTermPizza_ReturnsTrue()
    {
        $vendor = new Vendor('Vendor', 'Good food', ['Pizza Napoletana', 'Neapolitan Pizza','Best pizza in the world'], 3, 10);

        $this->assertTrue($vendor->matchTerm('pizza'));
    }

    public function testmatchTerm_SearchTermBurger_ReturnsFalse()
    {
        $vendor = new Vendor('Vendor', 'Good food', ['Pizza Napoletana', 'Neapolitan Pizza','Best pizza in the world'], 3, 10);

        $this->assertFalse($vendor->matchTerm('burger'));
    }
}