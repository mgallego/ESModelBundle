<?php

namespace MGP\ESModelBundle\Model\ES\Repository;

use MGP\ESModelBundle\Model\ES\Repository\ESRepositoryInterface;

/**
 * ESRepository
 *
 * @copyright Moisés Gallego 2014 (See LICENSE file to more information)
 * @author Moisés Gallego <moisesgallego@gmail.com>
 */
class ESRepository implements ESRepositoryInterface
{

    /**
     * @var int
     */
    protected $docsPerPage;

    /**
     * setDocsPerPage
     *
     *@param int $docsPerPage
     */
    public function setDocsPerPage($docsPerPage = 10)
    {
        $this->docsPerPage = $docsPerPage;
    }
        
    /**
     * Paginate a query
     *
     * @param $query
     * @param int $currentPage
     *
     * @return array
     */
    public function paginate(\Elastica\Query $query, $currentPage)
    {
        $itemsPerPage = $this->docsPerPage;
        $totalPages = $this->typeClient->search($query)->getTotalHits() + 1; 
        $pagesToShow = [];

        $firstPage = $currentPage - ($itemsPerPage/2);
        if ($firstPage <= 0) {
            $firstPage = 1;
        }

        foreach (range($firstPage, $firstPage + $itemsPerPage) as $i) {
            if ($i <= $totalPages) {
                $pagesToShow[] = $i;
            }
        }

        $pagination = [];
        $pagination['prev_page'] = $totalPages - 1;
        if ($currentPage <= $totalPages) {
            $pagination['next_page'] = $currentPage + 1;
            $pagination['prev_page'] = $currentPage - 1;
            $pagination['pages_to_show'] = $pagesToShow;
            $pagination['last_page'] = $totalPages;
        }
        $query->setFrom(($currentPage -1) * $itemsPerPage);
        $result['data'] = $this->typeClient->search($query, $itemsPerPage)->getResults();
        $result['pagination'] = $pagination;
        return $result;
    }

    /**
     * to array
     *
     * @param \Elastica\ResultSet $resultSet
     * @return array
     */
    public function toArray(\Elastica\ResultSet $resultSet)
    {
        $result = [];
        foreach ($resultSet as $hit) {
            $result[] = $hit->getSource();
        }
        return $result;
    }
}