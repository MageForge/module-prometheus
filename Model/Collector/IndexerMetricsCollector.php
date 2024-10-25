<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use Magento\Framework\Indexer\StateInterface as IndexerStateInterface;
use Magento\Framework\Mview\View\StateInterface as MviewStateInterface;
use MageForge\Prometheus\Model\Metric;

class IndexerMetricsCollector extends AbstractMetricsCollector
{
    public function collect(): array
    {
        return array_merge($this->getIndexerStateMetrics(), $this->getMviewStateMetrics());
    }

    private function getIndexerStateMetrics(): array
    {
        $metrics = [];

        $states = [IndexerStateInterface::STATUS_WORKING, IndexerStateInterface::STATUS_VALID, IndexerStateInterface::STATUS_INVALID];

        $query = $this->getConnection()->query('SELECT * FROM indexer_state');
        $results = $query->fetchAll();

        foreach ($results as $result) {
            foreach ($states as $state) {
                $metrics[] = $this->metricFactory->create([
                    'name' => 'indexer_state',
                    'value' => $result['status'] === $state ? 1 : 0,
                    'labels' => ['indexer' => $result['indexer_id'], 'state' => $state],
                    'type' => Metric::TYPE_GAUGE
                ]);
            }
        }

        return $metrics;
    }

    private function getMviewStateMetrics(): array
    {
        $metrics = [];

        $states = [
            MviewStateInterface::STATUS_IDLE,
            MviewStateInterface::STATUS_SUSPENDED,
            MviewStateInterface::STATUS_WORKING,
        ];

        $query = $this->getConnection()->query('SELECT * FROM mview_state WHERE mode="enabled"');
        $results = $query->fetchAll();

        foreach ($results as $result) {
            foreach ($states as $state) {
                $metrics[] = $this->metricFactory->create([
                    'name' => 'mview_state',
                    'value' => $result['status'] === $state ? 1 : 0,
                    'labels' => ['view' => $result['view_id'], 'state' => $state],
                    'type' => Metric::TYPE_GAUGE
                ]);
            }
        }

        return $metrics;
    }
}
