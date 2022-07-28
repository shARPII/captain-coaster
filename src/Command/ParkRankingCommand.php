<?php

namespace App\Command;

use App\Entity\Coaster;
use App\Service\ParkRankingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ParkRankingCommand extends Command
{
    /**
     * @var ParkRankingService
     */
    private $parkRankingService;

    /**
     * ParkRankingCommand constructor.
     * @param ParkRankingService $parkRankingService
     */
    public function __construct(ParkRankingService $parkRankingService)
    {
        parent::__construct();
        $this->parkRankingService = $parkRankingService;
    }

    protected function configure()
    {
        $this
            ->setName('parkRanking:update')
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
            ->addOption('output', null, InputOption::VALUE_NONE);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('parkRanking');

        $output->writeln('Starting update park ranking command.');

        $dryRun = $input->getOption('dry-run');

        if ((new \DateTime())->format('j') !== '1' && !$dryRun) {
            $output->writeln('We are not first day of month. We do it dry-run anyway.');
            $dryRun = true;
        }

        $result = $this->parkRankingService->updateRanking($dryRun);

        if ($input->getOption('output')) {
            foreach ($result as $park) {
                $output->writeln($park->getName() . ' => quality score : ' . $park->getQualityScore());
            }
        }

        $output->writeln(count($result).' parks updated.');
        $output->writeln((string)$stopwatch->stop('parkRanking'));
        $output->writeln('Dry-run: '.$dryRun);
    }
}
