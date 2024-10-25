<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model;

class MetricsCollectors
{
    /**
     * @param MetricsCollectorInterface[] $collectors
     */
    public function __construct(protected array $collectors)
    {
    }

    public function collect(): array
    {
        $allMetrics = [];

        foreach ($this->collectors as $collector) {
            $metrics = $collector->collect();
            foreach ($metrics as $metric) {
                $allMetrics[] = $metric;
            }
        }

        return $allMetrics;
    }
}
