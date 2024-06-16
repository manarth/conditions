<?php

declare(strict_types=1);

namespace Drupal\conditions\ComparisonOperator;

/**
 * Compare two values using a bitwise comparison operator.
 */
enum BitwiseOperator : string implements ComparisonOperatorInterface {
  const DEFINITION = 0x001;

  case Or  = '|';
  case And = '&';
  case Xor = '^';

  /**
   * Perform a numeric comparison between two values.
   *
   * @param string|int|float $value1
   *   The condition context.
   * @param string|int|float $value2
   *   The value to compare against.
   *
   * @return bool
   *   TRUE if the values match against the selected rule.
   */
  public function compare($value1, $value2) : bool {
    return (bool) match($this) {
      self::Or  => $value1 | $value2,
      self::And => $value1 & $value2,
      self::Xor => $value1 ^ $value2,
    };
  }

}
