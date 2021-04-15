<?php

namespace Domain\Vendor;

class HardcodedVendorDataSource implements VendorDataSourceInterface
{
    /**
     * @return Vendor[]
     */
    public function getVendors(): array
    {
        return [
            new Vendor('Patty & Bun', 'Best burgers in London', ['Cheese burger', 'Chicken burger', 'Vegan burger'], 2, 100),
            new Vendor('Zakuski', 'Russian deli', ['Vegan melting pot', 'Mishmash'], 48, 1000)
        ];
    }
}
