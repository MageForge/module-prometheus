<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use MageForge\Prometheus\Model\Metric;

class HeartbeatMetricsCollector extends AbstractMetricsCollector
{
    public function collect(): array
    {
        return [
            $this->metricFactory->create([
                'name' => 'mageforge_prometheus_collect_heartbeat',
                'value' => time(),
                'type' => Metric::TYPE_GAUGE
            ])
        ];
    }
}
