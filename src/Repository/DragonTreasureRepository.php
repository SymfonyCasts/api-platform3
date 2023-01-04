<?php

namespace App\Repository;

use App\Entity\DragonTreasure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DragonTreasure>
 *
 * @method DragonTreasure|null find($id, $lockMode = null, $lockVersion = null)
 * @method DragonTreasure|null findOneBy(array $criteria, array $orderBy = null)
 * @method DragonTreasure[]    findAll()
 * @method DragonTreasure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DragonTreasureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DragonTreasure::class);
    }

    public function save(DragonTreasure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DragonTreasure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DragonTreasure[] Returns an array of DragonTreasure objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DragonTreasure
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
