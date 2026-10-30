<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\ValueObjects\Exceptions;

use InvalidArgumentException;
use Throwable;

class ValuesNotValid extends InvalidArgumentException
{
  /**
   * @param array<ValueNotValid> $errors
   */
  public function __construct(
    public readonly array $errors,
    string $message = '',
    int $code = 0,
    ?Throwable $previous = null,
  ) {
    if ($message === '') {
      $details = [];
      foreach ($errors as $error) {
        $details[] = $error->getMessage() !== ''
          ? sprintf('%s (%s)', $error->attributeName, $error->getMessage())
          : $error->attributeName;
      }
      $message = !empty($details)
        ? sprintf('Invalid values: %s', implode(', ', $details))
        : 'Values are not valid.';
    }

    parent::__construct($message, $code, $previous);
  }
}
