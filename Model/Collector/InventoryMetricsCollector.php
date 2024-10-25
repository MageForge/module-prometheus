<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use MageForge\Prometheus\Model\Metric;

class InventoryMetricsCollector extends AbstractMetricsCollector
{
    public function collect(): array
    {
        return $this->getInventoryReservationMetrics();
    }

    private function getInventoryReservationMetrics(): array
    {
        $metrics = [];

        $irQuery = $this->getConnection()
            ->query('SELECT sum(quantity) AS qty, stock_id FROM inventory_reservation GROUP BY stock_id');
        $irResults = $irQuery->fetchAll();

        foreach ($irResults as $irResult) {
            $metrics[] = $this->metricFactory->create([
                'name' => 'inventory_reservation',
                'value' => intval($irResult['qty']) * -1,
                'labels' => ['stock_id' => $irResult['stock_id']],
                'type' => Metric::TYPE_GAUGE
            ]);
        }

        return $metrics;
    }
}
