<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\ConfigOptionsList\Cache as FrontendCache;
use Prometheus\CollectorRegistry as PrometheusCollectorRegistry;
use Prometheus\RenderTextFormat as PrometheusRenderTextFormat;
use Prometheus\Storage\Adapter as PrometheusStorageAdapter;
use Prometheus\Storage\Redis as PrometheusRedisStorage;
use Psr\Log\LoggerInterface;
use Throwable;
use MageForge\Prometheus\Model\Metric;

class MetricsService
{
    /** @var string */
    public const METRIC_NAMESPACE = 'magento';

    /** @var Metric[] */
    protected array $metrics = [];

    protected ?PrometheusCollectorRegistry $prometheusRegistry = null;
    protected ?PrometheusStorageAdapter $prometheusStorageAdapter = null;

    public function __construct(
        protected readonly DeploymentConfig $deploymentConfig,
        protected readonly LoggerInterface $logger
    )
    {
    }

    public function incrementCounter(string $name, int $value = 1, array $labels = [], bool $throw = false): void
    {
        try {
            $counter = $this->getPrometheusRegistry()->getOrRegisterCounter(
                self::METRIC_NAMESPACE,
                $name,
                $name,
                array_keys($labels)
            );

            $counter->incBy($value, array_values($labels));
        } catch (Throwable $t) {
            if ($throw === true) {
                throw $t;
            } else {
                $this->logger->error(sprintf('Exception while incrementing counter %s: %s', $name, $t->getMessage()), ['exception' => $t]);
            }
        }
    }

    public function setGauge(string $name, $value, array $labels = [], bool $throw = false): void
    {
        try {
            $gauge = $this->getPrometheusRegistry()->getOrRegisterGauge(
                self::METRIC_NAMESPACE,
                $name,
                $name,
                array_keys($labels)
            );

            $gauge->set(floatval($value), array_values($labels));
        } catch (Throwable $t) {
            if ($throw === true) {
                throw $t;
            } else {
                $this->logger->error(sprintf('Exception while incrementing counter %s: %s', $name, $t->getMessage()), ['exception' => $t]);
            }
        }
    }

    /**
     * @deprecated
     */
    public function addMetric(Metric $metric): void
    {
        if ($metric->getType() === Metric::TYPE_COUNTER) {
            $this->incrementCounter(
                $metric->getName(),
                $metric->getValue(),
                $metric->getLabels()
            );
        } elseif ($metric->getType() === Metric::TYPE_GAUGE) {
            $this->setGauge(
                $metric->getName(),
                $metric->getValue(),
                $metric->getLabels()
            );
        }
    }

    public function getTextOutput(): string
    {
        $renderer = new PrometheusRenderTextFormat();
        return $renderer->render($this->getPrometheusRegistry()->getMetricFamilySamples());
    }

    public function clean(): void
    {
        $this->getPrometheusStorageAdapter()->wipeStorage();
    }

    protected function getPrometheusRegistry(): PrometheusCollectorRegistry
    {
        if ($this->prometheusRegistry === null) {
            $this->prometheusRegistry = new PrometheusCollectorRegistry($this->getPrometheusStorageAdapter());
        }

        return $this->prometheusRegistry;
    }

    protected function getPrometheusStorageAdapter(): PrometheusStorageAdapter
    {
        if ($this->prometheusStorageAdapter === null) {
            $redisStorage = new PrometheusRedisStorage([
                'host' => $this->deploymentConfig->get('cache/frontend/default/backend_options/remote_backend_options/server') ?? '127.0.0.1',
                'port' => intval($this->deploymentConfig->get('cache/frontend/default/backend_options/remote_backend_options/port') ?? 6379),
                'database' => intval($this->deploymentConfig->get('cache/frontend/default/backend_options/remote_backend_options/database') ?? 0),
            ]);

            $redisStorage::setPrefix(sprintf('%s_PROMETHEUS_', $this->deploymentConfig->get(FrontendCache::CONFIG_PATH_CACHE_ID_PREFIX, 'MF')));

            $this->prometheusStorageAdapter = $redisStorage;
        }

        return $this->prometheusStorageAdapter;
    }
}
