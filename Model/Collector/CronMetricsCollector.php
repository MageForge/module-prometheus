<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\FlagManager;
use MageForge\Prometheus\Model\Metric;
use MageForge\Prometheus\Model\MetricFactory;

class CronMetricsCollector extends AbstractMetricsCollector
{
    public const LAST_PROCESSED_FINISH_DATE = 'mageforge_prometheus_last_processed_finish_date';

    public function __construct(
        ResourceConnection $resourceConnection,
        MetricFactory $metricFactory,
        protected readonly FlagManager $flagManager
    )
    {
        parent::__construct($resourceConnection, $metricFactory);
    }

    public function collect(): array
    {
        $lastProcessedFinishDate = $this->flagManager->getFlagData(self::LAST_PROCESSED_FINISH_DATE) ?? date('Y-m-d H:i:s', time() - 86400);

        $lastFinishDate = $this->getLastCronFinishDate();

        $this->flagManager->saveFlag(self::LAST_PROCESSED_FINISH_DATE, $lastFinishDate);

        return array_merge(
            $this->getCronDurationMetrics($lastProcessedFinishDate, $lastFinishDate),
            $this->getLastCronDelayMetrics()
        );
    }

    protected function getCronDurationMetrics(string $fromFinishDate, string $toFinishDate): array
    {
        $metrics = [];

        $table = $this->getConnection()->getTableName('cron_schedule');

        $query = <<<SQL
            select job_code, COUNT(*) as total_count, SUM(TIMESTAMPDIFF(SECOND, executed_at, finished_at)) as total_duration
            from %s
            where finished_at > '%s' and finished_at <= '%s' and status='success'
            group by job_code
            SQL;

        $query = $this->getConnection()->query(sprintf($query, $table, $fromFinishDate, $toFinishDate));
        $results = $query->fetchAll();

        foreach ($results as $result) {
            $metrics[] = $this->metricFactory->create([
                'name' => 'cron_duration_seconds_count',
                'value' => intval($result['total_count']),
                'labels' => ['job_code' => $result['job_code']],
                'type' => Metric::TYPE_COUNTER
            ]);

            $metrics[] = $this->metricFactory->create([
                'name' => 'cron_duration_seconds_sum',
                'value' => intval($result['total_duration']),
                'labels' => ['job_code' => $result['job_code']],
                'type' => Metric::TYPE_COUNTER
            ]);
        }

        return $metrics;
    }

    protected function getLastCronDelayMetrics(): array
    {
        $metrics = [];

        $table = $this->getConnection()->getTableName('cron_schedule');

        $query = <<<SQL
            select TIMESTAMPDIFF(SECOND, MAX(finished_at), NOW()) as last_execution_delay, job_code
            FROM %s
            WHERE status='success' and finished_at IS NOT NULL
            GROUP by job_code
            SQL;

        $query = $this->getConnection()->query(sprintf($query, $table));
        $results = $query->fetchAll();

        foreach ($results as $result) {
            $metrics[] = $this->metricFactory->create([
                'name' => 'cron_last_successful_finish_delay',
                'value' => intval($result['last_execution_delay']),
                'labels' => ['job_code' => $result['job_code']],
                'type' => Metric::TYPE_GAUGE
            ]);
        }

        return $metrics;
    }

    protected function getLastCronFinishDate(): string
    {
        $query = $this->getConnection()->query("SELECT MAX(finished_at) FROM cron_schedule WHERE status='success' and finished_at IS NOT NULL");
        $result = $query->fetchColumn();

        return $result ?? '2999-12-31 23:59:59';
    }
}
