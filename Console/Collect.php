<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Console;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MageForge\Prometheus\Model\MetricsCollectors;
use MageForge\Prometheus\Service\MetricsService;

class Collect extends Command
{
    public function __construct(
        protected readonly MetricsService $metricsService,
        protected readonly MetricsCollectors $metricsCollectors
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('mageforge:prometheus:collect');
        $this->setDescription('Collect Prometheus metrics');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $metrics = $this->metricsCollectors->collect();

        foreach ($metrics as $metric) {
            $this->metricsService->addMetric($metric);
        }

        $output->writeln('Metrics collected');

        return Cli::RETURN_SUCCESS;
    }
}
