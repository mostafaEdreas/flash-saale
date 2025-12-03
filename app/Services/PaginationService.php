<?php

namespace App\Services;

use App\Interfaces\Pagination;

class PaginationService implements Pagination
{
    private $paginator;
    public function __construct($paginator)
    {
        $this-> paginator =$paginator;
    }

    public function __invoke(): array
    {
        return $this->shortDetails();
    }
    
    public function shortDetails(): array
    {
        return [
                'current_page' => $this-> paginator->currentPage(),
                'last_page' => $this-> paginator->lastPage(),
                'next_page_url' => $this-> paginator->nextPageUrl(),
                'prev_page_url' => $this-> paginator->previousPageUrl(),
                'last_page_url' => $this-> paginator->url($this-> paginator->lastPage()),
                'per_page' => $this-> paginator->perPage(),
                'total' => $this-> paginator->total(),
                'has_more_pages' => $this-> paginator->hasMorePages(),
        ];
    }
}