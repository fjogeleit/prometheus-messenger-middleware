<?php

declare(strict_types=1);

namespace PrometheusMiddleware\Tests\Example\LabelValueProvider;

use PrometheusMiddleware\LabelValueProviderInterface;
use PrometheusMiddleware\Tests\Example\FooMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;

class FooLabelValueProvider implements LabelValueProviderInterface
{
    public function __invoke(Envelope $envelope, StackInterface $stack): array
    {
        /** @var FooMessage $message */
        $message = $envelope->getMessage();

        return [
            \get_class($message),
            substr(strrchr(get_class($message), '\\'), 1),
            $message->getBar()
        ];
    }
}
