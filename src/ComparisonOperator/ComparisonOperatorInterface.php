<?php

declare(strict_types=1);

namespace Drupal\conditions\ComparisonOperator;

/**
 * Compare two values using a selected comparison operator.
 */
interface ComparisonOperatorInterface {

  /**
   * Compare two values using the defined operator.
   */
  public function compare($value1, $value2) : bool;

}
