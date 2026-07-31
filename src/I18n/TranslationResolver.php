<?php

namespace Schorts\SharedKernel\I18n;

interface TranslationResolver
{
  public function resolve(string $key, ?array $params = null): string;
}
