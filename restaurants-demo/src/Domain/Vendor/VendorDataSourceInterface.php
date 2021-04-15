<?php

namespace Domain\Vendor;

interface VendorDataSourceInterface {
    /**
     * @return Vendor[]
     */
    public function getVendors(): array;
}
