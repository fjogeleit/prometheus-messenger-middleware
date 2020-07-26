<?php

declare(strict_types=1);

namespace Prometheus\Messenger\Exception;

use InvalidArgumentException;
use Prometheus\Collector;

class InvalidNameException extends InvalidArgumentException
{
    public static function with(string $busName, string $metricName): self
    {
        return new self(sprintf(
            'Invalid character in your BusName %s or MetricName %s, ensure this values pass the following RegEx %s',
            $busName,
            $metricName,
            Collector::RE_METRIC_LABEL_NAME
        ));
    }
}
