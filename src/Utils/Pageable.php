<?php

namespace App\Utils;

use Knp\Component\Pager\Pagination\PaginationInterface;

class Pageable
{
    public iterable $content;
    public int $totalElements, $pageSize, $totalPages, $number;

    /**
     * @param iterable $content
     * @param int $totalElements
     * @param int $pageSize
     * @param int $number
     */
    public function __construct(iterable $content, int $totalElements, int $pageSize, int $number)
    {
        $this->content = $content;
        $this->totalElements = $totalElements;
        $this->pageSize = $pageSize;
        $this->number = $number;
        $this->totalPages = intval(ceil($totalElements/$pageSize));

    }

    public static function createFrom(PaginationInterface $pagination): array
    {
        return (array) new self(
            $pagination->getItems(),
            $pagination->getTotalItemCount(),
            $pagination->getItemNumberPerPage(),
            $pagination->getCurrentPageNumber()
        );
    }
}