<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model;

interface MetricsCollectorInterface
{
    /**
     * @return Metric[]
     */
    public function collect(): array;
}
