<?php

namespace App\Service;

use App\Entity\Park;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ParkRatingService
 * @package App\Service
 */
class CaptainScoreService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * RatingService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Update captain score for different entities
     *
     * @param int $type
     * @param bool $checkUpdateStatusDb
     * @return int
     */
    public function updateCaptainScore(int $type, bool $checkUpdateStatusDb = false): int
    {
        $countToUpdate = 1;

        if($type == CoasterFromList::Parks) {
            $parks = $this->em->getRepository(Park::class)->findAll();
            foreach ($parks as $park) {
                $scores = [];
                $coasters = $park->getCoasters();
                foreach($coasters as $coaster) {
                    if ($coaster->getStatus()->getName() == 'status.operating') {
                        $scores[] = $coaster->getScore();
                    }
                }
                if(count($scores) > 0) {
                    $this->em->persist($this->calc($scores, $park));
                    $countToUpdate = $this->updateDatabase($countToUpdate);
                }
            }
        } else if($type == CoasterFromList::Users) {
            if($checkUpdateStatusDb)
                $users = $this->em->getRepository(User::class)->findBy(['shouldUpdateUserScore' => 1]);
            else
                $users = $this->em->getRepository(User::class)->findAll();
            foreach($users as $user) {
                $scores = [];
                $list2 = $user->getRatings();
                foreach($list2 as $rating) {
                    $scores[] = $rating->getCoaster()->getScore();
                }
                if(count($scores) > 0) {
                    $objectToUpdate = $this->calc($scores, $user);
                    $objectToUpdate->setShouldUpdateUserScore(false);
                    $this->em->persist($objectToUpdate);

                    $countToUpdate = $this->updateDatabase($countToUpdate);
                }
            }
        }

        if($countToUpdate > 1)
            $countToUpdate = $this->updateDatabase($countToUpdate, true);

        return $countToUpdate;
    }

    public function powFunction($value) : float
    {
        return pow(100, $value/100);
    }

    public function log100Function($value) : float
    {
        return 100*log($value,100);
    }

    public function calc($scores, $objectToUpdate) {
        rsort($scores, SORT_NUMERIC);
        $value1 = array_map(array($this, 'powFunction'), $scores);
        $countCoasters = count($value1);
        $value2 = array_map(array($this, 'powFunction'), range(50/$countCoasters, (50*((2*$countCoasters)-1))/$countCoasters, 100/$countCoasters));
        rsort($value2, SORT_NUMERIC);
        $dum = array_map(function($x, $y)  { return $x * $y; }, $value1, $value2);
        echo print_r($value1);
        echo print_r($value2);
        echo print_r($dum);
        echo array_sum($dum) . ' ' . array_sum($value2);
        $qualityScore = $this->log100Function(array_sum($dum) / array_sum($value2));
        $strengthScore = $qualityScore + $this->log100Function($countCoasters);
        echo ' ' . $qualityScore;
        echo ' ' . $strengthScore;

        $objectToUpdate->setQualityScore($qualityScore);
        $objectToUpdate->setStrengthScore($strengthScore);

        return $objectToUpdate;
    }

    public function updateDatabase($count, bool $force = false) : int
    {
        if ($count % 1000 == 0 && !$force)
            $this->em->flush();

        if($force) {
            $this->em->flush();
            $this->em->clear();
        } else {
            $count++;
        }

        return $count;
    }
}

/**
 * Class CoasterFromList
 * @package App\Service
 */
abstract class CoasterFromList
{
    const Parks = 0;
    const Users = 1;
}