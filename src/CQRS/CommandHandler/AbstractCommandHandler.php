<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandHandler;

use DateTimeImmutable;
use Schorts\SharedKernel\CQRS\Command\Command;
use Schorts\SharedKernel\CQRS\CommandHandler\Exceptions\CommandAuthorizationError;
use Schorts\SharedKernel\CQRS\CommandHandler\Exceptions\CommandExecutionError;
use Schorts\SharedKernel\CQRS\CommandHandler\Exceptions\CommandMaxRetriesExceeded;
use Schorts\SharedKernel\CQRS\CommandHandler\Exceptions\CommandValidationError;
use Schorts\SharedKernel\Idempotency\IdempotencyStore;
use Schorts\SharedKernel\Logger\Logger;
use Throwable;

abstract class AbstractCommandHandler implements CommandHandler
{
  protected readonly CommandHandlerOptions $options;
  protected readonly ?IdempotencyStore $idempotencyStore;
  protected readonly ?Logger $logger;

  public function __construct(?CommandHandlerOptions $options = null)
  {
    $this->options = $options ?? new CommandHandlerOptions();
    $this->idempotencyStore = $this->options->idempotencyStore;
    $this->logger = $this->options->logger;
  }

  public function getOptions(): CommandHandlerOptions
  {
    return $this->options;
  }

  public function handle(Command $command, ?CommandHandlerContext $context = null): mixed
  {
    $startTime = new DateTimeImmutable();
    $correlationId = $command->getMetadata()->correlationId;

    $handlerContext = $context ?? new CommandHandlerContext(
      metadata: $command->getMetadata(),
      options: $this->options,
      startTime: $startTime,
      correlationId: $correlationId,
    );

    $idempotencyKey = $this->getIdempotencyKey($command);
    if ($this->options->idempotent && $this->idempotencyStore !== null && $idempotencyKey !== null) {
      if ($this->idempotencyStore->isProcessed($idempotencyKey)) {
        return $this->idempotencyStore->getResult($idempotencyKey);
      }
    }

    $this->validate($command);
    $this->authorize($command);

    $maxRetries = $this->options->maxRetries;
    $attempts = 0;
    $maxAttempts = $maxRetries + 1;
    $lastError = null;

    while ($attempts < $maxAttempts) {
      $attempts++;
      try {
        if ($this->options->logging && $this->logger !== null) {
          $this->logger->info(sprintf(
            "Executing command '%s' [correlationId=%s, attempt=%d]",
            $command->getType(),
            $correlationId,
            $attempts
          ));
        }

        $result = $this->execute($command, $handlerContext);

        if ($this->options->idempotent && $this->idempotencyStore !== null && $idempotencyKey !== null) {
          $this->idempotencyStore->markProcessed($idempotencyKey, $result);
        }

        return $result;
      } catch (CommandValidationError | CommandAuthorizationError $e) {
        throw $e;
      } catch (Throwable $e) {
        $lastError = $e;
        if ($this->options->logging && $this->logger !== null) {
          $this->logger->error(sprintf(
            "Error executing command '%s' (attempt %d/%d): %s",
            $command->getType(),
            $attempts,
            $maxAttempts,
            $e->getMessage()
          ));
        }

        if (!$this->canRetry($command, $e) || $attempts >= $maxAttempts) {
          break;
        }
      }
    }

    if ($maxRetries > 0 && $attempts >= $maxAttempts) {
      throw new CommandMaxRetriesExceeded($command->getType(), $maxRetries, $lastError);
    }

    if ($lastError instanceof CommandExecutionError) {
      throw $lastError;
    }

    throw new CommandExecutionError(
      $command->getType(),
      $lastError?->getMessage() ?? 'Command execution failed.',
      0,
      $lastError
    );
  }

  public function validate(Command $command): void {}

  public function authorize(Command $command): void {}

  public function getIdempotencyKey(Command $command): ?string
  {
    return $command->getMetadata()->id;
  }

  public function canRetry(Command $command, Throwable $error): bool
  {
    return true;
  }

  abstract protected function execute(Command $command, CommandHandlerContext $context): mixed;
}
