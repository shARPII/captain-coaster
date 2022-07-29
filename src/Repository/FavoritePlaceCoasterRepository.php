<?php

namespace App\Repository;

use App\Entity\Coaster;
use App\Entity\FavoritePlaceCoaster;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FavoritePlaceCoaster>
 *
 * @method FavoritePlaceCoaster|null find($id, $lockMode = null, $lockVersion = null)
 * @method FavoritePlaceCoaster|null findOneBy(array $criteria, array $orderBy = null)
 * @method FavoritePlaceCoaster[]    findAll()
 * @method FavoritePlaceCoaster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavoritePlaceCoasterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FavoritePlaceCoaster::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(FavoritePlaceCoaster $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(FavoritePlaceCoaster $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Coaster $coaster
     * @return mixed|string
     */
    public function getHeatMapData(Coaster $coaster)
    {
        try {
            return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('\'pos\' + f.value as place')
                ->addSelect('count(f.value) as num')
                ->from('App:FavoritePlaceCoaster', 'f')
                ->where('f.coaster = :coaster')
                ->setParameter('coaster', $coaster)
                ->groupBy('f.value')
                ->orderBy('f.value', 'asc')
                ->getQuery()
                ->getArrayResult();
        } catch (\Exception $e) {
            return ['nb' => 0, 'name' => 'Unknown'];
        }
    }

    /**
     * @param Coaster $coaster
     * @param User $user
     * @return mixed|string
     */
    public function addOrUpdate(Coaster $coaster, User $user, int $position)
    {
        try {
            $favoritePlace = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('f')
                ->from('App:FavoritePlaceCoaster', 'f')
                ->where('f.coaster = :coaster')
                ->andWhere('f.user = :user')
                ->setParameter('coaster', $coaster)
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            if(empty($favoritePlace)) {
                $favoritePlace = new FavoritePlaceCoaster();
                $favoritePlace->setCoaster($coaster);
                $favoritePlace->setUser($user);
                $favoritePlace->setValue($position);

                $this->add($favoritePlace);
            } else {
                $favoritePlace = $favoritePlace[0];
                $favoritePlace->setValue($position);
                $this->getEntityManager()->persist($favoritePlace);
                $this->getEntityManager()->flush();
            }

        } catch (\Exception $e) {
            return ['nb' => 0, 'name' => 'Unknown'];
        }

        return [];
    }
}
