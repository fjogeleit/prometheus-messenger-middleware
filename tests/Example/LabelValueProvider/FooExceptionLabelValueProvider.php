<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Example\LabelValueProvider;

use PrometheusMiddleware\ErrorLabelValueProviderInterface;
use PrometheusMiddleware\Tests\Example\FooMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;

class FooExceptionLabelValueProvider implements ErrorLabelValueProviderInterface
{
    public function __invoke(Envelope $envelope, StackInterface $stack, \Throwable $exception): array
    {
        /** @var FooMessage $message */
        $message = $envelope->getMessage();

        return [
            \get_class($message),
            substr(strrchr(get_class($message), '\\'), 1),
            $exception->getMessage()
        ];
    }
}
