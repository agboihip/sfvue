<?php

namespace App\Repository;

use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Entity\{Offer, Product};
use App\Utils\Pageable;
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
        if($search->getFromShop()) $qr->andWhere('u.id = :shop')->setParameter('shop', $search->getFromShop());
        foreach($search->getInOptions() as $key => $value) $qr->andWhere(":in$key MEMBER OF p.tags")->setParameter("in$key", $value);

        return $this->paginator->paginate($qr->getQuery(), $page, $items); //array_map(fn (Product $p) => $p->setPrice($this->applyOffer($p)), array(...
    }

    public function findByIds(array $value): array
    {
        return $this->visibleQuery()->andWhere("p.id in(:ids)")->setParameter("ids", $value)->getQuery()->getResult();
    }

    private function visibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')->select('p,u','c,o')
            ->join('p.owner', 'u')->leftJoin('p.tags', 'c')
            ->leftJoin('p.promos', 'o', 'WITH', 'o.started_at < CURRENT_TIMESTAMP() AND o.expired_at > CURRENT_TIMESTAMP()') //o.active = 1 AND
            ->andWhere('p.stock > 0')->orderBy('p.createdAt', 'DESC');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAllByPage(int $page, string $search, int $limit=50) : array
    {
        $sql = <<<SQL
            SELECT 
                v.id AS id,
                v.name AS title,
                v.description AS description,
                v.images AS images,
                v.price AS price,
                v.stock AS stock,
                v.created_at AS createdAt
            FROM product v 
            WHERE 
                v.description LIKE :search OR 
                v.name LIKE :search 
            ORDER BY v.created_at DESC
            LIMIT :limit OFFSET :offset
        SQL;

        $statement = $this->_em->getConnection()->prepare($sql);
        $statement->bindValue("search", "%$search%");
        $statement->bindValue("offset",  max(0, ($page-1) * $limit));
        $statement->bindValue("limit",  $limit);
        $statement->executeQuery();

        return $statement->fetchAllAssociative();
    }

    /**
     * Get offer price for a product
     * @param Product $product
     * @return float|null
     */
    public function applyOffer(Product $product): ?float
    {
        /** @var Offer $offer */
        $offer = $product->getPromos()->first(); $price = $product->getPrice();
        if ($offer && $offer->isActive()) $price -= $offer->isFixed() ? $offer->getValue() : (($offer->getValue() / 100) * $price);

        return $price;
    }
}
