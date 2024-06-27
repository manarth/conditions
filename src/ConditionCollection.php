<?php

declare(strict_types=1);

namespace Drupal\conditions;

use Drupal\Core\Condition\ConditionInterface;

/**
 * Store and evaluate a collection of collections with logical operators.
 */
class ConditionCollection implements ConditionCollectionInterface {

  /**
   * Parent collection, when used with nested groups.
   *
   * @var \Drupal\conditions\ConditionCollectionInterface
   */
  protected ?ConditionCollectionInterface $parent = NULL;

  /**
   * Sequence of Conditions and operators.
   *
   * @var array
   */
  protected array $sequence = [];

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   (optional) Initiate the collection with a starting condition.
   */
  public function __construct(?ConditionInterface $condition = NULL) {
    if ($condition) {
      $this->condition($condition);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() : bool {
    $this->evaluateConditions();
    return $this->evaluateLogic();
  }

  /**
   * {@inheritdoc}
   */
  public function condition(ConditionInterface $condition) : ConditionCollectionInterface {
    $this->validateAction(__FUNCTION__);
    $this->sequence[] = $condition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function startGroup() : ConditionCollectionInterface {
    $this->validateAction(__FUNCTION__);
    $conditionCollection = new ConditionCollection();
    $conditionCollection->setParent($this);
    $this->sequence[] = $conditionCollection;
    return $conditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function endGroup() : ConditionCollectionInterface {
    $this->validateAction(__FUNCTION__);
    return $this->parent;
  }

  /**
   * {@inheritdoc}
   */
  public function and(?ConditionInterface $condition = NULL) : ConditionCollectionInterface {
    return $this->add(
      __FUNCTION__,
      LogicalOperator::And,
      $condition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function not(?ConditionInterface $condition = NULL) : ConditionCollectionInterface {
    return $this->add(
      __FUNCTION__,
      LogicalOperator::Not,
      $condition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function or(?ConditionInterface $condition = NULL) : ConditionCollectionInterface {
    return $this->add(
      __FUNCTION__,
      LogicalOperator::Or,
      $condition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function xor(?ConditionInterface $condition = NULL) : ConditionCollectionInterface {
    return $this->add(
      __FUNCTION__,
      LogicalOperator::Xor,
      $condition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setParent(ConditionCollectionInterface $parent) : ConditionCollectionInterface {
    $this->parent = $parent;
    return $this;
  }

  /**
   * Add an operator and an optional condition.
   *
   * @param string $action
   *   Method-name of the operator being called.
   * @param \Drupal\conditions\LogicalOperator $operator
   *   The operator to add.
   * @param \Drupal\Core\Condition\ConditionInterface $condition
   *   (optional) A condition to add following the operator.
   *
   * @return static
   *   Return $this for method chaining.
   */
  protected function add(string $action, LogicalOperator $operator, ?ConditionInterface $condition = NULL) : ConditionCollectionInterface {
    $this->validateAction($action);
    $this->sequence[] = $operator;
    if ($condition) {
      $this->condition($condition);
    }
    return $this;
  }

  /**
   * Evaluate each of the conditions, resolving each to a boolean.
   */
  protected function evaluateConditions() : void {
    foreach ($this->sequence as $key => $entry) {
      if ($entry instanceof ConditionInterface) {
        $result = $entry->evaluate();
        $this->sequence[$key] = $entry->isNegated() ? !$result : $result;
      }
      if ($entry instanceof ConditionCollectionInterface) {
        $this->sequence[$key] = $entry->evaluate();
      }
    }
  }

  /**
   * Evaluate the logic in operator precedence order.
   *
   * @return bool
   *   The result of performing the logical operators on the sequence.
   */
  protected function evaluateLogic() : bool {
    $evaluation = array_map([$this, 'getToken'], $this->sequence);
    $evaluation = implode(' ', $evaluation);
    $evaluation = sprintf('return %s;', $evaluation);

    // As the `$evaluation` variable will only contain the defined, safe
    // keywords provided by `getToken()`, it is safe to use `eval()`.
    // phpcs:ignore
    return eval($evaluation);
  }

  /**
   * Evaluate a sequence entry to a logical token.
   *
   * @param \Drupal\conditions\LogicalOperator|bool $entry
   *   The entry in the sequence.
   *
   * @return string
   *   A token to represent the entry in a logical evaluation.
   */
  protected function getToken(LogicalOperator|bool $entry) : string {
    if (is_bool($entry)) {
      return ($entry) ? 'true' : 'false';
    }
    if ($entry instanceof LogicalOperator) {
      return $entry->value;
    }
    throw new \Exception('Unexpected value in sequence.');
  }

  /**
   * Validate that an action is appropriate for the current state.
   *
   * @param string $action
   *   The method name.
   *
   * @throws \Exception
   *   An exception is thrown when an action cannot be performed.
   */
  protected function validateAction(string $action) {
    $latest = end($this->sequence);

    switch ($action) {
      case 'condition':
      case 'startGroup':
        if ($latest && $latest instanceof ConditionInterface) {
          throw new \Exception('A condition must be preceded by an operator.');
        }
        break;

      case 'endGroup':
        if (empty($this->parent)) {
          throw new \Exception('No group to close.');
        }
        break;

      case 'and':
      case 'or':
      case 'xor':
        if (!($latest && $latest instanceof ConditionInterface)) {
          throw new \Exception('A logical operator must be preceded by a condition.');
        }
        break;

      case 'not':
        if ($latest == LogicalOperator::Not) {
          throw new \Exception('Double-negatives are not supported.');
        }
        break;
    }
  }

}
