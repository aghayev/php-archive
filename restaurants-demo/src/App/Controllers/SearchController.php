<?php

namespace App\Controllers;

use App\Request;
use Domain\Vendor\Vendor;
use Domain\Vendor\VendorRepositoryInterface;

use DateTime;

class SearchController implements Controller
{
private VendorRepositoryInterface $vendorRepository;

    public function __construct(
        VendorRepositoryInterface $vendorRepository
    )
    {
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * @return string[]
     */
    public function searchVendors(Request $request): array
    {
        $deliveryDate = new DateTime($request->get(0).' '.$request->get(1));
        $deliveryHeadcount = $request->get(2);
        $searchTerm = $request->get(3);
        $vendors = $this->vendorRepository->findVendors($deliveryDate, $deliveryHeadcount, $searchTerm);
        return $this->buildResponse($vendors);
    }

    /**
     * @param Vendor[] $vendors
     * @return string[]
     */
    private function buildResponse(array $vendors): array
    {
        $response = [];
        foreach ($vendors as $vendor) {
            $response[] = $vendor->getHeader() . ': ' . $vendor->getMenu();
        }
        return $response;
    }
}
