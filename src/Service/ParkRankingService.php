<?php

namespace App\Service;

use App\Entity\Park;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ParkRatingService
 * @package App\Service
 */
class ParkRankingService
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
     * Update averageRating for all coasters
     *
     * @return int
     */
    public function updateRanking(bool $dryRun = false): array
    {
        $countToUpdate = 1;

        $parks = $this->em->getRepository(Park::class)->findAll();
        foreach ($parks as $park) {
            $scores = [];
            $coasters = $park->getCoasters();
            foreach($coasters as $coaster) {
                if($coaster->getStatus()->getName() == 'status.operating') {
                    $scores[] = $coaster->getScore();
                }
            }

            if(count($scores) > 0) {
                rsort($scores, SORT_NUMERIC);
                $value1 = array_map(array($this, 'powFunction'), $scores);
                $countCoasters = count($value1);
                $value2 = array_map(array($this, 'powFunction'), range(50/$countCoasters, (50*((2*$countCoasters)-1))/$countCoasters, 100/$countCoasters));
                rsort($value2, SORT_NUMERIC);
                $dum = array_map(function($x, $y)  { return $x * $y; }, $value1, $value2);
                $qualityScore = 100*log(array_sum($dum) / array_sum($value2),100);

                $park->setQualityScore($qualityScore);
                $this->em->persist($park);
            }

            $countToUpdate++;
            if ($countToUpdate % 1000 == 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
        $this->em->clear();
        return $parks;
    }

    public function powFunction($value) : float
    {
        return pow(100, $value/100);
    }


}
