<?php

namespace Schorts\SharedKernel\Formatters;

class PascalCamelToSnake
{
  public static function format(string $text): string
  {
    $snake = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $text);
    $snake = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $snake);

    return strtolower($snake);
  }

  public static function formatObject($obj)
  {
    if (is_array($obj)) {
      return array_map([self::class, 'formatObject'], $obj);
    }

    if (is_object($obj)) {
      $result = [];

      foreach (get_object_vars($obj) as $key => $value) {
        $snakeKey = self::format($key);
        $result[$snakeKey] = self::formatObject($value);
      }

      return (object) $result;
    }

    return $obj;
  }
}

function pascalCamelToSnake(string $text): string
{
  return PascalCamelToSnake::format($text);
}
