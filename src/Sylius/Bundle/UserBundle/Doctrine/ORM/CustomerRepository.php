<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\UserBundle\Doctrine\ORM;

use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\UserInterface;

/**
 * @author Michał Marcinkowski <michal.marcinkowski@lakion.com>
 */
class CustomerRepository extends EntityRepository
{
    /**
     * Get the customer's data for the details page.
     *
     * @param int $id
     *
     * @return null|UserInterface
     */
    public function findForDetailsPage($id)
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq('o.id', ':id'))
            ->setParameter('id', $id)
        ;

        $result = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result;
    }

    /**
     * @param array $criteria
     * @param array $sorting
     *
     * @return Pagerfanta
     */
    public function createFilterPaginator($criteria = [], $sorting = [])
    {
        $queryBuilder = parent::getCollectionQueryBuilder()
            ->leftJoin($this->getPropertyName('user'), 'user');

        if (isset($criteria['query'])) {
            $queryBuilder
                ->where($queryBuilder->expr()->like($this->getPropertyName('emailCanonical'), ':query'))
                ->orWhere($queryBuilder->expr()->like($this->getPropertyName('firstName'), ':query'))
                ->orWhere($queryBuilder->expr()->like($this->getPropertyName('lastName'), ':query'))
                ->orWhere($queryBuilder->expr()->like('user.username', ':query'))
                ->setParameter('query', '%'.$criteria['query'].'%')
            ;
        }
        if (isset($criteria['enabled'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('user.enabled', ':enabled'))
                ->setParameter('enabled', $criteria['enabled'])
            ;
        }

        if (empty($sorting)) {
            if (!is_array($sorting)) {
                $sorting = [];
            }
            $sorting['updatedAt'] = 'desc';
        }

        $this->applySorting($queryBuilder, $sorting);

        return $this->getPaginator($queryBuilder);
    }
}
