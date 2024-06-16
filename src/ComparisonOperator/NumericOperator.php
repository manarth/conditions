<?php

declare(strict_types=1);

namespace Drupal\conditions\ComparisonOperator;

/**
 * Compare two values using a numeric comparison operator.
 */
enum NumericOperator : string implements ComparisonOperatorInterface {
  const DEFINITION = 0x010;

  case Equal              = '==';
  case LessThan           = '<';
  case LessThanOrEqual    = '<=';
  case GreaterThanOrEqual = '>=';
  case GreaterThan        = '>';

  /**
   * Perform a numeric comparison between two values.
   *
   * @param int|float $value1
   *   The condition context.
   * @param int|float $value2
   *   The value to compare against.
   *
   * @return bool
   *   TRUE if the values match against the selected rule.
   */
  public function compare($value1, $value2) : bool {
    return match($this) {
      self::LessThan           => $value1 < $value2,
      self::LessThanOrEqual    => $value1 <= $value2,
      self::Equal              => $value1 == $value2,
      self::GreaterThanOrEqual => $value1 >= $value2,
      self::GreaterThan        => $value1 > $value2,
    };
  }

}
