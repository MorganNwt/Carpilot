<?php

namespace App\Repository;

use App\Entity\User\Agent;
use App\Entity\User\Seller;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retrieves a paginated list of all users.
     */
    public function findPaginatedUsers(int $page, int $limit): Paginator
    {
        return $this->createPaginatedQueryBuilder($page, $limit)->getQueryAndPaginator();
    }

    /**
     * Retrieves a paginated list of Sellers only.
     */
    public function findPaginatedSellers(int $page, int $limit): Paginator
    {
        $query = $this->createPaginatedQueryBuilder($page, $limit, Seller::class);
        return $query->getQueryAndPaginator();
    }

    /**
     * Retrieves a paginated list of Agents only.
     */
    public function findPaginatedAgents(int $page, int $limit): Paginator
    {
        $query = $this->createPaginatedQueryBuilder($page, $limit, Agent::class);
        return $query->getQueryAndPaginator();
    }


    /**
     * Creates a base paginated query, optionally filtering by user type.
     *
     * @param int $page The page number.
     * @param int $limit The number of items per page.
     * @param string|null $userClass The specific user class to filter by (Seller::class).
     * @return self A new instance of this class for chaining.
     */
    private function createPaginatedQueryBuilder(int $page, int $limit, ?string $userClass = null): self
    {
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        $this->queryBuilder = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($userClass) {
            $this->queryBuilder
                ->andWhere('u INSTANCE OF :userType')
                ->setParameter('userType', $this->getEntityManager()->getClassMetadata($userClass));
        }

        return $this;
    }

    /**
     * Gets the query from the builder and returns a Paginator.
     *
     * @return Paginator
     */
    private function getQueryAndPaginator(): Paginator
    {
        return new Paginator($this->queryBuilder->getQuery(), true);
    }

    private $queryBuilder;
}