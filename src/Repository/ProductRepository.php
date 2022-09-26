<?php

namespace App\Repository;

use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Entity\Product;
use App\Utils\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);

        if ($flush) $this->_em->flush();
    }

    public function remove(Product $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);

        if ($flush) $this->_em->flush();
    }

    public function findLast(int $size = 16): array
    {
        return $this->visibleQuery()
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($size)
            ->getQuery()->getResult();
    }

    /**
     * Find all products paginated
     *
     * @param int $page
     * @param Search $search
     * @param int $items
     * @return PaginationInterface
     */
    public function findAllPaginated(int $page, Search $search, int $items = 20): PaginationInterface
    {
        $qr = $this->visibleQuery();

        if($search->getMaxPrice()) $qr->andWhere('p.price <= :max')->setParameter('max', $search->getMaxPrice());
        if($search->getKeyWord()) $qr->andWhere('p.name LIKE :term')->setParameter('term', "%".$search->getKeyWord()."%");
        foreach($search->getInOptions() as $key => $value) $qr->andWhere(":in$key MEMBER OF p.tags")->setParameter("in$key", $value);

        return $this->paginator->paginate($qr->getQuery(), $page, $items);
    }

    public function findByIds(array $value): array
    {
        return $this->visibleQuery()->andWhere("p.id in(:ids)")->setParameter("ids", $value)->getQuery()->getResult();
    }

    private function visibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->select('p','c')
            ->leftJoin('p.tags', 'c')
            ->andWhere('p.stock > 0')
            ->orderBy('p.createdAt', 'DESC');
    }
}
