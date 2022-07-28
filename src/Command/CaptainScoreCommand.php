<?php

namespace App\Command;

use App\Service\CaptainScoreService;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class CaptainScoreCommand extends Command
{
    /**
     * @var CaptainScoreService
     */
    private $captainScoreService;

    /**
     * ParkRankingCommand constructor.
     * @param CaptainScoreService $captainScoreService
     */
    public function __construct(CaptainScoreService $captainScoreService)
    {
        parent::__construct();
        $this->captainScoreService = $captainScoreService;
    }

    protected function configure()
    {
        $this
            ->setName('captainScore:update')
            ->addOption('type', null, InputOption::VALUE_REQUIRED)
            ->addOption('checkUpdateStatusDb', null, InputOption::VALUE_NONE)
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
        $stopwatch->start('captainScore');

        $oClass = new ReflectionClass('App\Service\CoasterFromList');
        $searchType = $input->getOption('type');
        $type = $oClass->getConstants()[$searchType];

        $output->writeln('Starting update captain score command for ' . $searchType);
        $checkUpdateStatusDb = $input->getOption('checkUpdateStatusDb');

        $result = $this->captainScoreService->updateCaptainScore($type, $checkUpdateStatusDb);

        $output->writeln(($result - 1). ' ' . $searchType . ' updated.');
        $output->writeln((string)$stopwatch->stop('captainScore'));
    }
}
