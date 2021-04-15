<?php

namespace Domain\Vendor;

use DateInterval;
use DateTimeInterface;

class Vendor
{
    private string $name;
    private string $description;
    private array $menuItems;
    private int $noticeHours;
    private int $maxHeadcount;

    public function __construct(
        string $name,
        string $description,
        array $menuItems,
        int $notice,
        int $maxHeadcount
    )
    {
        $this->name = $name;
        $this->description = $description;
        $this->menuItems = $menuItems;
        $this->noticeHours = $notice;
        $this->maxHeadcount = $maxHeadcount;
    }

    public function getHeader(): string
    {
        return $this->name.' - '.$this->description;
    }

    public function getMenu(): string
    {
        return join(', ', $this->menuItems);
    }

    public function canDeliver(DateTimeInterface $deliveryDate, DateTimeInterface $nowDate): bool
    {
        return $deliveryDate >= $nowDate->add($this->createNoticeInterval());
    }

    public function canHeadcount(int $deliveryHeadcount): bool
    {
        return $deliveryHeadcount <= $this->maxHeadcount;
    }

    public function matchTerm(string $searchTerm): bool
    {
        return !!preg_grep('/'.$searchTerm.'/i', $this->menuItems);
    }

    private function createNoticeInterval(): DateInterval
    {
        return new DateInterval('PT'.$this->noticeHours.'H');
    }
}