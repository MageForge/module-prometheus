# Magento 2 Prometheus metrics module

Access `http://magento-host/metrics`

## Expose Prometheus metrics

This module allows to collect metrics using two methods:

* **Event based metrics**: metrics (counters most of the time) which value is updated during a given event (ie. a HTTP call)
* **Interval based metrics**: metrics (gauges most of the time) which value is the current value at the time it is collected (ie. number of records in a table)

### Event based metrics

Use one of the following methods anywhere in your code to instantly register :

* `\MageForge\Prometheus\Service\MetricsService::incrementCounter(string $name, int $value = 1, array $labels = [], bool $throw = false)`
* `\MageForge\Prometheus\Service\MetricsService::setGauge(string $name, int $value, array $labels = [], bool $throw = false)`
* `\MageForge\Prometheus\Service\MetricsService::addMetric(Metric $metric)`

### Interval based metrics

Create a collector class implementing `\MageForge\Prometheus\Model\MetricsCollectorInterface`, and register it in `collectors` argument of class `MageForge\Prometheus\Model\MetricsCollectors` using `di.xml`.

## Best practices

* Metrics should have labels whenever possible
* Metrics labels must be strings

## Compatibility

This module has been tested with Magento and Adobe Commerce 2.4.7.

## License

This module is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
