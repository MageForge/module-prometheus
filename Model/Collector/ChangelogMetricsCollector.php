<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model\Collector;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\CollectionFactory as MviewCollectionFactory;
use Magento\Framework\Mview\View\StateInterface;
use MageForge\Prometheus\Model\Metric;
use MageForge\Prometheus\Model\MetricFactory;

class ChangelogMetricsCollector extends AbstractMetricsCollector
{
    /** @var string */
    private const METRIC_TABLE_COUNT = 'changelog_table_count';

    /** @var string */
    private const METRIC_TABLE_DISTINCT_COUNT = 'changelog_table_distinct_count';

    public function __construct(
        ResourceConnection $resourceConnection,
        MetricFactory $metricFactory,
        protected readonly MviewCollectionFactory $mviewCollectionFactory
    ) {
        parent::__construct($resourceConnection, $metricFactory);
    }

    public function collect(): array
    {
        $metrics = [];
        $tableCl = $this->getConnection()->getTables('%_cl');
        $listViewState = $this->getListStateVersion();

        foreach ($tableCl as $tableName) {
            $listTableMetrics = $this->getTableCount($tableName, $listViewState);
            foreach ($listTableMetrics as $tableMetric) {
                $metrics[] = $tableMetric;
            }
        }

        return $metrics;
    }

    private function getTableCount(string $tableName, array $listViewState): array
    {
        $rowCount = $rowDistinctCount = 0;
        $connection = $this->getConnection();
        $versionId = $this->getVersionId($tableName, $listViewState);

        if ($versionId !== 0) {
            $queryCount = $connection->select()
                ->from($connection->getTableName($tableName), 'COUNT(*)')
                ->where('version_id > ?', $versionId);
            $rowCount = (int) $connection->query($queryCount)->fetchColumn();
            $queryCountDistinct = $connection->select()
                ->from($connection->getTableName($tableName), 'COUNT(DISTINCT entity_id)')
                ->where('version_id > ?', $versionId);
            $rowDistinctCount = (int) $connection->query($queryCountDistinct)->fetchColumn();
        }

        return [
            $this->metricFactory->create([
                'name' => self::METRIC_TABLE_COUNT,
                'value' => $rowCount,
                'labels' => ['table' => $tableName],
                'type' => Metric::TYPE_GAUGE
            ]),
            $this->metricFactory->create([
                'name' => self::METRIC_TABLE_DISTINCT_COUNT,
                'value' => $rowDistinctCount,
                'labels' => ['table' => $tableName],
                'type' => Metric::TYPE_GAUGE
            ])
        ];
    }

    private function getVersionId(string $tableName, array $listViewState): int
    {
        $tableName = str_replace('_cl', '', $tableName);
        foreach ($listViewState as $viewId => $versionId) {
            if (preg_match('/' . $viewId . '$/', $tableName)) {
                return (int) $versionId;
            }
        }

        return 0;
    }

    private function getListStateVersion(): array
    {
        $listViewState = [];
        $mviewState = $this->mviewCollectionFactory->create();
        $mviewStateItems = $mviewState->getViewsByStateMode(StateInterface::MODE_ENABLED);

        foreach ($mviewStateItems as $stateItem) {
            $listViewState[$stateItem->getState()->getViewId()] = $stateItem->getState()->getVersionId();
        }

        return $listViewState;
    }
}
