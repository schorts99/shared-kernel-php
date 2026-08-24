<?php

namespace Schorts\SharedKernel\Logger;

abstract class Logger
{
  public abstract function log(string $message, array $context = []): void;
  public abstract function info(string $message, array $context = []): void;
  public abstract function debug(string $message, array $context = []): void;
  public abstract function warn(string $message, array $context = []): void;
  public abstract function error(string $message, array $context = [], ?\Throwable $error = null): void;

  public function child(array $context): Logger
  {
    return new ScopedLogger($this, $context);
  }
}

class ScopedLogger extends Logger
{
  private Logger $base;
  private array $baseContext;

  public function __construct(Logger $base, array $baseContext)
  {
    $this->base = $base;
    $this->baseContext = $baseContext;
  }

  public function log(string $message, array $context = []): void
  {
    $this->base->log($message, array_merge($this->baseContext, $context));
  }

  public function info(string $message, array $context = []): void
  {
    $this->base->info($message, array_merge($this->baseContext, $context));
  }

  public function debug(string $message, array $context = []): void
  {
    $this->base->debug($message, array_merge($this->baseContext, $context));
  }

  public function warn(string $message, array $context = []): void
  {
    $this->base->warn($message, array_merge($this->baseContext, $context));
  }

  public function error(string $message, array $context = [], ?\Throwable $error = null): void
  {
    $this->base->error($message, array_merge($this->baseContext, $context), $error);
  }
}
