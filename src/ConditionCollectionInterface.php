<?php

declare(strict_types=1);

namespace Drupal\conditions;

use Drupal\Core\Condition\ConditionInterface;

/**
 * Store and evaluate a collection of collections with logical operators.
 */
interface ConditionCollectionInterface extends \SeekableIterator, \ArrayAccess, \Serializable, \Countable {

  /**
   * Evaluate whether the conditions and operators resolve to TRUE or FALSE.
   *
   * Operators are calculated in logical precedence:
   * - Not
   * - And
   * - Xor
   * - Or.
   *
   * @return bool
   *   The result of evaluating each condition and operator.
   */
  public function evaluate() : bool;

  /**
   * Add a condition.
   *
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   A condition plugin.
   *
   * @return static
   *   Return $this for method chaining.
   */
  public function condition(ConditionInterface $condition) : ConditionCollectionInterface;

  /**
   * Start a nested logical condition group.
   *
   * @return static
   *   A new, nested, empty condition collection.
   */
  public function startGroup() : ConditionCollectionInterface;

  /**
   * Close a logical condition group.
   *
   * @return static
   *   The parent collection which created the group.
   */
  public function endGroup() : ConditionCollectionInterface;

  /**
   * Logical AND.
   *
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   (optional) Condition to add following the logical operator.
   *
   * @return static
   *   Return $this for method chaining.
   */
  public function and(?ConditionInterface $condition = NULL) : ConditionCollectionInterface;

  /**
   * Logical NOT.
   *
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   (optional) Condition to add following the logical operator.
   *
   * @return static
   *   Return $this for method chaining.
   */
  public function not(?ConditionInterface $condition = NULL) : ConditionCollectionInterface;

  /**
   * Logical OR.
   *
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   (optional) Condition to add following the logical operator.
   *
   * @return static
   *   Return $this for method chaining.
   */
  public function or(?ConditionInterface $condition = NULL) : ConditionCollectionInterface;

  /**
   * Logical EXCLUSIVE OR.
   *
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   (optional) Condition to add following the logical operator.
   *
   * @return static
   *   Return $this for method chaining.
   */
  public function xor(?ConditionInterface $condition = NULL) : ConditionCollectionInterface;

  /**
   * Set the parent collection container, when creating a grouped colllection.
   *
   * @param \Drupal\conditions\ConditionCollectionInterface $parent
   *   The parent collection.
   *
   * @return static
   *   Return $this for method chaining.
   */
  public function setParent(ConditionCollectionInterface $parent) : ConditionCollectionInterface;

}
