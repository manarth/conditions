<?php

declare(strict_types=1);

namespace Drupal\conditions\ComparisonOperator;

/**
 * Identify an enum from the operator case string.
 */
class Operators {

  /**
   * All comparison operator enums.
   */
  const OPERATORS = [
    BitwiseOperator::class,
    NumericOperator::class,
    StringOperator::class,
  ];

  /**
   * Get the comparison operator which matches a specific case string.
   *
   * @param string $case
   *   The enum case.
   *
   * @return \Drupal\conditions\ComparisonOperator\ComparisonOperatorInterface
   *   A comparison operator enum.
   *
   * @throws \Exception
   *   If the operator cannot be identified.
   */
  public static function getOperatorFor(string $case) : ComparisonOperatorInterface {
    foreach (self::OPERATORS as $clazz) {
      $operators = array_column($clazz::cases(), 'name');
      if (in_array($case, $operators)) {
        return constant("{$clazz}::{$case}");
      }
    }
    throw new \Exception('Unrecognised operator.');
  }

  /**
   * Get the list of all comparison operator cases.
   *
   * @return array
   *   Array of case names.
   */
  public static function cases() : array {
    $cases = [];
    foreach (self::OPERATORS as $clazz) {
      $cases = array_merge($cases, $clazz::cases());
    }
    return $cases;
  }

}
