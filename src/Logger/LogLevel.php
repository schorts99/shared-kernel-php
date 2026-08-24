<?php

namespace Schorts\SharedKernel\Logger;

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
