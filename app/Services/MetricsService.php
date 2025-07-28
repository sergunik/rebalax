<?php

declare(strict_types=1);

namespace App\Services;

use Prometheus\CollectorRegistry;

final readonly class MetricsService
{
    private const NAMESPACE = 'prometheus_metrics_service';

    public function __construct(private CollectorRegistry $registry)
    {
    }

    public function recordCommandExecutionTime(string $command, float $duration): void
    {
        $histogram = $this->registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'command_execution_duration_seconds',
            'Duration of command execution in seconds',
            ['command'],
            [0.1, 0.5, 1, 2.5, 5, 10, 25, 50, 100, 250, 500, 1000] // buckets in seconds
        );

        $histogram->observe($duration, [$command]);
    }

    public function incrementCommandExecutions(string $command, string $status = 'success'): void
    {
        $counter = $this->registry->getOrRegisterCounter(
            self::NAMESPACE,
            'command_executions_total',
            'Total number of command executions',
            ['command', 'status']
        );

        $counter->incBy(1, [$command, $status]);
    }

    public function recordPortfoliosProcessed(string $command, int $count): void
    {
        $counter = $this->registry->getOrRegisterCounter(
            self::NAMESPACE,
            'portfolios_processed_total',
            'Total number of portfolios processed',
            ['command']
        );

        $counter->incBy($count, [$command]);
    }

    public function recordBatchProcessed(string $command, int $batchSize, int $processedCount): void
    {
        // Record batch size histogram
        $batchSizeHistogram = $this->registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'batch_size_distribution',
            'Distribution of batch sizes',
            ['command'],
            [10, 25, 50, 100, 250, 500, 1000]
        );
        $batchSizeHistogram->observe($batchSize, [$command]);

        // Record processed count per batch
        $processedHistogram = $this->registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'batch_processed_count_distribution',
            'Distribution of items processed per batch',
            ['command'],
            [0, 10, 25, 50, 100, 250, 500, 1000]
        );
        $processedHistogram->observe($processedCount, [$command]);
    }

    public function setActiveCommand(string $command, int $active = 1): void
    {
        $gauge = $this->registry->getOrRegisterGauge(
            self::NAMESPACE,
            'active_commands',
            'Currently active commands',
            ['command']
        );

        $gauge->set($active, [$command]);
    }

    public function recordCommandTimeout(string $command): void
    {
        $counter = $this->registry->getOrRegisterCounter(
            self::NAMESPACE,
            'command_timeouts_total',
            'Total number of command timeouts',
            ['command']
        );

        $counter->incBy(1, [$command]);
    }
}
