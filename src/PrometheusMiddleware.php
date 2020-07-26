<?php

declare(strict_types=1);

namespace Prometheus\Messenger;

use InvalidArgumentException;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Messenger\Exception\InvalidNameException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
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
    private $busName;

    /**
     * @var string
     */
    private $metricName;

    /**
     * @var string|null
     */
    private $helpText;

    /**
     * @var string|null
     */
    private $errorHelpText;

    /**
     * @var array|null
     */
    private $labels;

    /**
     * @var LabelValueProviderInterface|null
     */
    private $labelValueProvider;

    /**
     * @var array|null
     */
    private $errorLabels;

    /**
     * @var ErrorLabelValueProviderInterface|null
     */
    private $errorLabelValueProvider;

    public function __construct(
        CollectorRegistry $collectorRegistry,
        string $busName,
        string $metricName = 'message',
        string $helpText = null,
        array $labels = null,
        LabelValueProviderInterface $labelValueProvider = null,
        string $errorHelpText = null,
        array $errorLabels = null,
        ErrorLabelValueProviderInterface $errorLabelValueProvider = null
    ) {
        $this->collectorRegistry = $collectorRegistry;
        $this->busName = str_replace('.', '_', $busName);
        $this->helpText = $helpText;
        $this->labels = $labels;
        $this->labelValueProvider = $labelValueProvider;
        $this->metricName = $metricName;
        $this->errorHelpText = $errorHelpText;
        $this->errorLabels = $errorLabels;
        $this->errorLabelValueProvider = $errorLabelValueProvider;
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
        $counter = $this->getCounter(
            $this->metricName,
            $this->helpText ?? 'Executed Messages',
            $this->labels
        );

        $message = $envelope->getMessage();

        $defaultValues = [
            \get_class($message),
            substr((string)strrchr(get_class($message), '\\'), 1)
        ];

        $values = null === $this->labelValueProvider ? $defaultValues : ($this->labelValueProvider)($envelope, $stack);

        try {
            $counter->inc($values);

            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (Throwable $exception) {
            $counter = $this->getCounter(
                $this->metricName . '_error',
                $this->helpText ?? 'Failed Messages',
                $this->errorLabels
            );

            $errorValues = null === $this->errorLabelValueProvider ?
                $defaultValues : ($this->errorLabelValueProvider)($envelope, $stack, $exception);

            $counter->inc($errorValues);

            throw $exception;
        }

        return $envelope;
    }

    private function getCounter(string $name, string $helperText, array $labels = null): Counter
    {
        try {
            if (true === is_array($labels)) {
                return $this->collectorRegistry->getOrRegisterCounter(
                    $this->busName,
                    $name,
                    $helperText,
                    $labels
                );
            }

            return $this->collectorRegistry->getOrRegisterCounter(
                $this->busName,
                $name,
                $helperText,
                ['command', 'label']
            );
        } catch (InvalidArgumentException $exception) {
            throw InvalidNameException::with($this->busName, $this->metricName);
        }
    }
}