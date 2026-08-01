<?php

namespace Schorts\SharedKernel\Logger;

use Schorts\SharedKernel\Logger\Logger;

enum LogLevel: string {
  case DEBUG = 'debug';
  case INFO  = 'info';
  case LOG   = 'log';
  case WARN  = 'warn';
  case ERROR = 'error';

  public function valueRank(): int {
    return match($this) {
      self::DEBUG => 0,
      self::INFO  => 1,
      self::LOG   => 2,
      self::WARN  => 3,
      self::ERROR => 4,
    };
  }
}

class ConsoleLogger extends Logger
{
  private int $levelValue;

  public function __construct(private LogLevel $level = LogLevel::INFO)
  {
    $this->levelValue = $level->valueRank();
  }

  public function log(string $message, array $context = []): void
  {
    if ($this->shouldLog(LogLevel::LOG)) {
      echo $this->format('LOG', $message, $context) . PHP_EOL;
    }
  }

  public function info(string $message, array $context = []): void
  {
    if ($this->shouldLog(LogLevel::INFO)) {
      echo $this->format('INFO', $message, $context) . PHP_EOL;
    }
  }

  public function debug(string $message, array $context = []): void
  {
    if ($this->shouldLog(LogLevel::DEBUG)) {
      echo $this->format('DEBUG', $message, $context) . PHP_EOL;
    }
  }

  public function warn(string $message, array $context = []): void
  {
    if ($this->shouldLog(LogLevel::WARN)) {
      fwrite(STDERR, $this->format('WARN', $message, $context) . PHP_EOL);
    }
  }

  public function error(string $message, array $context = [], ?\Throwable $error = null): void
  {
    if ($this->shouldLog(LogLevel::ERROR)) {
      $errorContext = $context;

      if ($error) {
        $errorContext['error'] = $error->getMessage();
        $errorContext['stack'] = $error->getTraceAsString();
      }

      fwrite(STDERR, $this->format('ERROR', $message, $errorContext) . PHP_EOL);
    }
  }

  private function shouldLog(LogLevel $level): bool
  {
    return $level->valueRank() >= $this->levelValue;
  }

  private function format(string $level, string $message, array $context = []): string
  {
    $timestamp = (new \DateTimeImmutable())->format(DATE_ATOM);
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '';

    return sprintf("[%s] [%s] %s%s", $timestamp, $level, $message, $contextStr);
  }
}
