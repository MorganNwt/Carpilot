<?php

namespace App\Repository;

use App\Entity\User\Admin;
use App\Entity\User\Agent;
use App\Entity\User\Seller;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository
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
        return $this->paginate($this->createBaseQb(), $page, $limit);
    }

    /**
     * Retrieves a paginated list of Sellers only.
     */
    public function findPaginatedSellers(int $page, int $limit): Paginator
    {
        $qb = $this->createBaseQb();
        $this->filterByUserType($qb, Seller::class);

        return $this->paginate($qb, $page, $limit);
    }

    /**
     * Retrieves a paginated list of Agents only.
     */
    public function findPaginatedAgents(int $page, int $limit): Paginator
    {
        $qb = $this->createBaseQb();
        $this->filterByUserType($qb, Agent::class);

        return $this->paginate($qb, $page, $limit);
    }

    /**
     * Retrieves a paginated list of Admins only.
     */
    public function findPaginatedAdmins(int $page, int $limit): Paginator
    {
        $qb = $this->createBaseQb();
        $this->filterByUserType($qb, Admin::class);

        return $this->paginate($qb, $page, $limit);
    }

    /**
     * Count ALL users.
     */
    public function countAllUsers(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count Agents (inheritance).
     */
    public function countAgents(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.deletedAt IS NULL');

        $this->filterByUserType($qb, Agent::class);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count Sellers (inheritance).
     */
    public function countSellers(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.deletedAt IS NULL');

        $this->filterByUserType($qb, Seller::class);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count Admins (inheritance).
     */
    public function countAdmins(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.deletedAt IS NULL');

        $this->filterByUserType($qb, Admin::class);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Base query builder used for listings.
     */
    private function createBaseQb(): QueryBuilder
    {
        // Si createdAt peut être NULL, garde un ordre secondaire sur id.
        return $this->createQueryBuilder('u')
            ->andWhere('u.deletedAt IS NULL')
            ->orderBy('u.createdAt', 'DESC')
            ->addOrderBy('u.id', 'DESC');
    }

    /**
     * Adds a filter to only keep a given user subtype (Agent/Seller) using Doctrine inheritance.
     *
     * @param class-string<User> $userClass
     */
    private function filterByUserType(QueryBuilder $qb, string $userClass): void
    {
        // Ces classes internes sont fixes. Le paramètre ClassMetadata est mal
        // interprété par les requêtes réécrites du paginateur Doctrine.
        $qb->andWhere('u INSTANCE OF ' . $userClass);
    }

    /**
     * Paginates a QueryBuilder and returns a Doctrine Paginator.
     */
    private function paginate(QueryBuilder $qb, int $page, int $limit): Paginator
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        return new Paginator($qb->getQuery(), true);
    }
}
