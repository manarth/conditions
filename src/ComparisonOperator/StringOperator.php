<?php

declare(strict_types=1);

namespace Drupal\conditions\ComparisonOperator;

/**
 * Perform a comparison between strings.
 */
enum StringOperator : string implements ComparisonOperatorInterface {
  const DEFINITION = 0x100;

  case Is         = 'is equal to';
  case Contains   = 'contains';
  case Regex      = 'matches regex';
  case StartsWith = 'starts with';
  case EndsWith   = 'ends with';
  case IsOneOf    = 'is one of';

  /**
   * Perform a case-sensitive comparison between two values.
   *
   * @param string $value1
   *   The condition context.
   * @param string|string[] $value2
   *   The value to compare against.
   *
   * @return bool
   *   TRUE if the values match against the selected rule.
   */
  public function compare($value1, $value2) : bool {
    return match($this) {
      self::Contains   => str_contains($value1, $value2),
      self::Is         => ($value1 == $value2),
      self::Regex      => preg_match($value2, $value1),
      self::StartsWith => str_starts_with($value1, $value2),
      self::EndsWith   => str_ends_with($value1, $value2),
      self::IsOneOf    => in_array($value1, $value2),
    };
  }

  /**
   * Perform a case-insensitive comparison between two values.
   *
   * @param string $value1
   *   The condition context.
   * @param string|string[] $value2
   *   The value to compare against.
   *
   * @return bool
   *   TRUE if the values match against the selected rule.
   */
  public function compareInsensitive($value1, $value2) : bool {
    if ($this == self::Regex) {
      // Append the ignore case flag to the pattern.
      // Do not lower-case both values, because some regex character classes
      // use case to specify behaviour, such as "\s" vs "\S".
      $value1 .= 'i';
      return $this->compare($value1, $value2);
    }

    $value1 = strtolower($value1);
    if (is_string($value2)) {
      $value2 = strtolower($value2);
    }
    if (is_array($value2)) {
      $value2 = array_map(fn ($val) => strtolower($val), $value2);
    }
    return $this->compare($value1, $value2);
  }

}
