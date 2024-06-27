<?php

declare(strict_types=1);

namespace Drupal\conditions;

use Drupal\Core\Condition\ConditionInterface;

/**
 * Store and evaluate a collection of collections with logical operators.
 */
class ConditionCollection implements ConditionCollectionInterface {

  /**
   * Sequence of Conditions and operators.
   *
   * @var array
   */
  protected array $sequence = [];

  /**
   * Constructor.
   *
   * @param \Drupal\conditions\ConditionCollection $parent
   *   When collection-groups are used, this references the parent collection.
   */
  public function __construct(protected ?ConditionCollectionInterface $parent = NULL) {
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
    $conditionCollection = new ConditionCollection($this);
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
  public function and() : ConditionCollectionInterface {
    $this->validateAction(__FUNCTION__);
    $this->sequence[] = LogicalOperator::And;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function not() : ConditionCollectionInterface {
    $this->validateAction(__FUNCTION__);
    $this->sequence[] = LogicalOperator::Not;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function or() : ConditionCollectionInterface {
    $this->validateAction(__FUNCTION__);
    $this->sequence[] = LogicalOperator::Or;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function xor() : ConditionCollectionInterface {
    $this->validateAction(__FUNCTION__);
    $this->sequence[] = LogicalOperator::Xor;
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
