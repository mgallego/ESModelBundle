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
        $totalItems = $this->typeClient->search($query)->getTotalHits() + 1; 
        $totalPages = ceil(($totalItems/$this->docsPerPage)); 
        $numPagesToShow = 5;
        $pagesToShow = [];

        $firstPage = $currentPage - $numPagesToShow;
        if ($firstPage <= 0) {
            $firstPage = 1;
        }

        foreach (range($firstPage, $firstPage + ($numPagesToShow * 2) ) as $i) {
            if ($i <= $totalPages) {
                $pagesToShow[] = $i;
            }
        }

        $pagination = [];
        $pagination['prev_page'] = $totalPages - 1;
        if ($currentPage <= $totalPages) {
            $pagination['next_page'] = $currentPage + 1;
            $pagination['current_page'] = (int) $currentPage;
            $pagination['prev_page'] = $currentPage - 1;
            $pagination['pages_to_show'] = $pagesToShow;
            $pagination['last_page'] = (int) $totalPages;
            $pagination['total_items'] = $totalItems;
        }

        $from = (($currentPage - 1) * $itemsPerPage) - 1; //The first document is 0
        if ($from < 0) {
            $from = 0;
        }

        $query->setFrom($from); 
        $resultSet = $this->typeClient->search($query, $itemsPerPage);
        $result['facets'] = $resultSet->getFacets();
        $result['data'] = $resultSet->getResults();
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

    /**
     * Modifies a string to remove all non ASCII characters and spaces.
     */
    static public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    
        // trim
        $text = trim($text, '-');
        
        // transliterate
        if (function_exists('iconv'))
            {
                $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            }
 
            // lowercase
        $text = strtolower($text);
 
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
 
        if (empty($text))
            {
                return 'n-a';
            }
 
        return $text;
    }

}