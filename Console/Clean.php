<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Console;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MageForge\Prometheus\Service\MetricsService;

class Clean extends Command
{
    public function __construct(
        protected readonly MetricsService $metricsService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('mageforge:prometheus:clean');
        $this->setDescription('Clean all collected Prometheus metrics');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->metricsService->clean();

        $output->writeln('<info>Metrics cleaned</info>');

        return Cli::RETURN_SUCCESS;
    }
}
