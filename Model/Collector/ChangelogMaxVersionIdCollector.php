<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use Zend_Db_Expr;
use MageForge\Prometheus\Model\Metric;

class ChangelogMaxVersionIdCollector extends AbstractMetricsCollector
{
    public function collect(): array
    {
        $metrics = [];
        $tableCl = $this->getConnection()->getTables('%_cl');

        foreach ($tableCl as $tableName) {
            $tableName = $this->getConnection()->getTableName($tableName);
            $query = $this->getConnection()->select()->from(
                $tableName,
                new Zend_Db_Expr('MAX(`version_id`) as max_version_id')
            );
            $result = $this->getConnection()->fetchRow($query);

            $value = 0;
            if (!empty($result) && isset($result['max_version_id']) && $result['max_version_id'] > 0) {
                $value = $result['max_version_id'];
            }

            $metrics[] = $this->metricFactory->create([
                'name' => 'changelog_max_version_id',
                'value' => $value,
                'labels' => ['table' => $tableName],
                'type' => Metric::TYPE_GAUGE
            ]);
        }

        return $metrics;
    }
}
