<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use MageForge\Prometheus\Model\MetricFactory;
use MageForge\Prometheus\Model\MetricsCollectorInterface;

abstract class AbstractMetricsCollector implements MetricsCollectorInterface
{
    public function __construct(
        protected readonly ResourceConnection $resourceConnection,
        protected readonly MetricFactory $metricFactory
    )
    {
    }

    protected function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }
}
