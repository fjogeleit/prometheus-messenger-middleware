<?php

declare(strict_types=1);

namespace PrometheusMiddleware\LabelValueProvider;

use PrometheusMiddleware\LabelValueProviderInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;

class DefaultLabelValueProvider implements LabelValueProviderInterface
{
    public function __invoke(Envelope $envelope, StackInterface $stack): array
    {
        $message = $envelope->getMessage();

        return [
            \get_class($message),
            substr((string)strrchr(get_class($message), '\\'), 1)
        ];
    }
}
