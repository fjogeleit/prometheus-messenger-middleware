<?php

declare(strict_types=1);

namespace PrometheusMiddleware;

use InvalidArgumentException;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use PrometheusMiddleware\Exception\InvalidNameException;
use PrometheusMiddleware\LabelValueProvider\DefaultErrorLabelValueProvider;
use PrometheusMiddleware\LabelValueProvider\DefaultLabelValueProvider;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Throwable;

class PrometheusMiddleware implements MiddlewareInterface
{
    /**
     * @var CollectorRegistry
     */
    private $collectorRegistry;

    /**
     * @var string
     */
    private $metricName;

    /**
     * @var string
     */
    private $helpText;

    /**
     * @var string
     */
    private $errorHelpText;

    /**
     * @var array
     */
    private $labels;

    /**
     * @var LabelValueProviderInterface
     */
    private $labelValueProvider;

    /**
     * @var array
     */
    private $errorLabels;

    /**
     * @var ErrorLabelValueProviderInterface
     */
    private $errorLabelValueProvider;

    public function __construct(
        CollectorRegistry $collectorRegistry,
        string $metricName = 'message',
        string $helpText = null,
        array $labels = null,
        LabelValueProviderInterface $labelValueProvider = null,
        string $errorHelpText = null,
        array $errorLabels = null,
        ErrorLabelValueProviderInterface $errorLabelValueProvider = null
    ) {
        $this->collectorRegistry = $collectorRegistry;
        $this->helpText = $helpText ?? 'Executed Messages';
        $this->labels = $labels ?? ['message', 'label'];
        $this->labelValueProvider = $labelValueProvider ?? new DefaultLabelValueProvider();
        $this->metricName = $metricName;
        $this->errorHelpText = $errorHelpText ?? 'Failed Messages';
        $this->errorLabels = $errorLabels ?? ['message', 'label'];
        $this->errorLabelValueProvider = $errorLabelValueProvider ?? new DefaultErrorLabelValueProvider();
    }

    /**
     * @param Envelope       $envelope
     * @param StackInterface $stack
     *
     * @return Envelope
     *
     * @throws Throwable
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $busName = 'default_messenger';

        /** @var BusNameStamp|null $stamp */
        $stamp = $envelope->last(BusNameStamp::class);

        if (true === $stamp instanceof BusNameStamp) {
            $busName = str_replace('.', '_', $stamp->getBusName());
        }

        $counter = $this->getCounter(
            $busName,
            $this->metricName,
            $this->helpText,
            $this->labels
        );

        $values = ($this->labelValueProvider)($envelope, $stack);

        try {
            $counter->inc($values);

            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (Throwable $exception) {
            $counter = $this->getCounter(
                $busName,
                $this->metricName . '_error',
                $this->errorHelpText,
                $this->errorLabels
            );

            $errorValues = ($this->errorLabelValueProvider)($envelope, $stack, $exception);

            $counter->inc($errorValues);

            throw $exception;
        }

        return $envelope;
    }

    private function getCounter(string $busName, string $name, string $helperText, array $labels = null): Counter
    {
        try {
            return $this->collectorRegistry->getOrRegisterCounter(
                $busName,
                $name,
                $helperText,
                $labels
            );
        } catch (InvalidArgumentException $exception) {
            throw InvalidNameException::with($busName, $this->metricName);
        }
    }
}
