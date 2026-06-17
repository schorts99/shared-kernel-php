<?php

namespace Schorts\SharedKernel\Logger;

use Schorts\SharedKernel\Logger\Logger;

class CompositeLogger extends Logger
{
  private array $loggers;

  public function __construct(array $loggers)
  {
    $this->loggers = $loggers;
  }

  public function log(string $message, array $context = []): void
  {
    foreach ($this->loggers as $logger) {
      $logger->log($message, $context);
    }
  }

  public function info(string $message, array $context = []): void
  {
    foreach ($this->loggers as $logger) {
      $logger->info($message, $context);
    }
  }

  public function debug(string $message, array $context = []): void
  {
    foreach ($this->loggers as $logger) {
      $logger->debug($message, $context);
    }
  }

  public function warn(string $message, array $context = []): void
  {
    foreach ($this->loggers as $logger) {
      $logger->warn($message, $context);
    }
  }

  public function error(string $message, array $context = [], ?\Throwable $error = null): void
  {
    foreach ($this->loggers as $logger) {
      $logger->error($message, $context, $error);
    }
  }
}
