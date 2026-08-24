<?php

namespace Schorts\SharedKernel\Logger;

use Schorts\SharedKernel\Logger\Logger;

class NullLogger extends Logger
{
  public function log(string $message, array $context = []): void {}
  public function info(string $message, array $context = []): void {}
  public function debug(string $message, array $context = []): void {}
  public function warn(string $message, array $context = []): void {}
  public function error(string $message, array $context = [], ?\Throwable $error = null): void {}
}
