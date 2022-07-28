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
                $output->writeln($this->formatMessage($park));
            }
        }

        $output->writeln(count($result).' parks updated.');
        foreach($result as $res) {
            $output->writeln($res->getName() . ' => quality score : ' . $res->getQualityScore());
        }


        $output->writeln((string)$stopwatch->stop('parkRanking'));
        $output->writeln('Dry-run: '.$dryRun);
    }

    /**
     * @param Coaster $coaster
     * @return string
     */
    private function formatMessage(Coaster $coaster): string
    {
        $format = '[%s] %s - %s (%s) %s updated.';

        if (is_null($coaster->getPreviousRank())) {
            $format = '[%s] <error>%s</error> - %s (%s) %s updated.';
        } elseif (abs($coaster->getRank() - $coaster->getPreviousRank()) > 0.25 * $coaster->getPreviousRank()) {
            $format = '[%s] <comment>%s</comment> - %s (%s) %s updated.';
        } elseif (abs($coaster->getRank() - $coaster->getPreviousRank()) > 0.1 * $coaster->getPreviousRank()) {
            $format = '[%s] <info>%s</info> - %s (%s) %s updated.';
        }

        return sprintf(
            $format,
            $coaster->getRank(),
            $coaster->getName(),
            $coaster->getPark()->getName(),
            $coaster->getPreviousRank() ?? 'new',
            number_format($coaster->getScore(), 2)
        );
    }
}
