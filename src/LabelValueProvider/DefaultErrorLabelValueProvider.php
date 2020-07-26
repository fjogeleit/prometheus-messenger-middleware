<?php

declare(strict_types=1);

namespace PrometheusMiddleware\LabelValueProvider;

use PrometheusMiddleware\ErrorLabelValueProviderInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Throwable;

class DefaultErrorLabelValueProvider implements ErrorLabelValueProviderInterface
{
    public function __invoke(Envelope $envelope, StackInterface $stack, Throwable $exception): array
    {
        $message = $envelope->getMessage();

        return [
            \get_class($message),
            substr((string)strrchr(get_class($message), '\\'), 1)
        ];
    }
}
