<?php

namespace Prometheus\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;

interface LabelValueProviderInterface
{
    public function __invoke(Envelope $envelope, StackInterface $stack): array;
}
