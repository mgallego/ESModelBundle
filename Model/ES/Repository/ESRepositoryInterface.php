<?php

namespace MGP\ESModelBundle\Model\ES\Repository;

/**
 * ESRepositoryInterface
 *
 * @copyright Moisés Gallego 2014 (See LICENSE file to more information)
 * @author Moisés Gallego <moisesgallego@gmail.com>
 */
interface ESRepositoryInterface
{
    public function setDocsPerPage($docsPerPage = 10);
    
    public function paginate(\Elastica\Query $query, $currentPage);

    public function toArray(\Elastica\ResultSet $resultSet);
}
