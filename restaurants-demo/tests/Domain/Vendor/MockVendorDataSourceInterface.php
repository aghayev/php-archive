<?php

namespace Domain\Vendor;

class MockVendorDataSourceInterface implements VendorDataSourceInterface
{
    private array $vendors;

    public function __construct(array $vendors = [])
    {
        $this->vendors = $vendors;
    }

    /**
     * @param Vendor[] $vendors
     */
    public function setVendors(array $vendors): void
    {
        $this->vendors = $vendors;
    }

    public function getVendors(): array
    {
        return $this->vendors;
    }
}
