<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Exception;

use InvalidArgumentException;
use Prometheus\Collector;

class InvalidNameException extends InvalidArgumentException
{
    public static function with(string $busName, string $metricName): self
    {
        // Compatibility with promphp/prometheus_client_php < 2.8.0
        if (defined('\Prometheus\Collector::RE_METRIC_LABEL_NAME')) {
            return new self(sprintf(
                'Invalid character in your BusName %s or MetricName %s, ensure this values pass the following RegEx %s',
                $busName,
                $metricName,
                \Prometheus\Collector::RE_METRIC_LABEL_NAME
            ));
        }

        return new self(sprintf(
            'Invalid character in your BusName %s or MetricName %s, ensure the BusName value passes the RegEx %s and MetricName — %s',
            $busName,
            $metricName,
            Collector::RE_LABEL_NAME,
            Collector::RE_METRIC_NAME,
        ));
    }
}
