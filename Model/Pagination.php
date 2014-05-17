<?php


namespace Net\Dontdrinkandroot\Symfony\ExtensionBundle\Model;


class Pagination
{

    private $currentPage;

    private $perPage;

    private $total;

    public function __construct($currentPage, $perPage, $total)
    {
        if ($currentPage < 1) {
            throw new \InvalidArgumentException("CurrentPage must be greater than 0");
        }

        if ($perPage < 1) {
            throw new \InvalidArgumentException("PerPage mustbe greater than 0");
        }

        if ($total < 0) {
            throw new \InvalidArgumentException("Total must be greater equals 0");
        }

        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->total = $total;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getTotalPages()
    {
        if ($this->total == 0) {
            return 0;
        }

        return (int)(($this->total - 1) / $this->perPage + 1);
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

}