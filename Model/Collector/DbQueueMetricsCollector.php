<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use Magento\MysqlMq\Model\QueueManagement;
use MageForge\Prometheus\Model\Metric;

class DbQueueMetricsCollector extends AbstractMetricsCollector
{
    public const QUEUE_MESSAGE_STATUS = [
        QueueManagement::MESSAGE_STATUS_NEW => 'new',
        QueueManagement::MESSAGE_STATUS_IN_PROGRESS => 'in_progress',
        QueueManagement::MESSAGE_STATUS_COMPLETE => 'complete',
        QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED => 'retry_required',
        QueueManagement::MESSAGE_STATUS_ERROR => 'error',
        QueueManagement::MESSAGE_STATUS_TO_BE_DELETED => 'to_be_deleted',
    ];
    public function collect(): array
    {
        return $this->getDbQueueMetrics();
    }

    private function getDbQueueMetrics(): array
    {
        $metrics = [];

        $query = $this->getConnection()->query('SELECT COUNT(*) AS count, qms.status AS status, q.name AS queue FROM queue_message_status qms JOIN queue q ON qms.queue_id = q.id GROUP BY q.name, qms.status');
        $results = $query->fetchAll();

        foreach ($results as $result) {
            $metrics[] = $this->metricFactory->create([
                'name' => 'queue_messages',
                'value' => $result['count'],
                'labels' => ['queue' => $result['queue'], 'state' => strval(self::QUEUE_MESSAGE_STATUS[$result['status']] ?? 0)],
                'type' => Metric::TYPE_GAUGE
            ]);
        }

        return $metrics;
    }
}
