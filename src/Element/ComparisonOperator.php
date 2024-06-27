<?php

declare(strict_types=1);

namespace Drupal\conditions\Element;

use Drupal\conditions\ComparisonOperator\BitwiseOperator;
use Drupal\conditions\ComparisonOperator\NumericOperator;
use Drupal\conditions\ComparisonOperator\Operators;
use Drupal\conditions\ComparisonOperator\StringOperator;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provide a form element to select a comparison operator.
 *
 * @see \Drupal\Core\Render\Element
 *
 * @FormElement("comparison_operator")
 */
class ComparisonOperator extends FormElement {

  /**
   * Allow all operators to be used.
   */
  const int ALL_OPERATORS = BitwiseOperator::DEFINITION | NumericOperator::DEFINITION | StringOperator::DEFINITION;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#input' => TRUE,
      '#title_display' => 'attribute',

      '#compare_string' => TRUE,
      '#compare_numeric' => FALSE,
      '#compare_bitwise' => FALSE,

      '#compare_string_case' => TRUE,
      '#case_sensitive' => FALSE,

      '#theme_wrappers' => [
        'form_element',
      ],

      '#process' => [
        [$this, 'processOperator'],
        [$this, 'addCaseSensitiveField'],
      ],
    ];
  }

  /**
   * Prepare the form element with the addition of a 'case sensitive' field.
   *
   * @param array $element
   *   The renderable array of the root comparison operator.
   *
   * @return array
   *   The consolidated fields.
   */
  public function processOperator($element) {
    $element['#tree'] = TRUE;
    $element['operator'] = [
      '#type' => 'select',
      '#title' => $element['#title'],
      '#title_display' => $element['#title_display'],
      '#options' => $element['#options'] ?? $this->getOptions($this->evaluateOperators($element)),
      '#default_value' => $element['#default_value'],
    ];
    unset($element['#title']);
    return $element;
  }

  /**
   * Evaluate the operators which should be provided.
   *
   * @param array $element
   *   The render array.
   *
   * @return int
   *   The bitwise value representing the operators to be included.
   */
  public function evaluateOperators($element) {
    if (!array_key_exists('#options', $element)) {
      $operators = 0;
      if ($element['#compare_string']) {
        $operators = $operators | StringOperator::DEFINITION;
      }
      if ($element['#compare_numeric']) {
        $operators = $operators | NumericOperator::DEFINITION;
      }
      if ($element['#compare_bitwise']) {
        $operators = $operators | BitwiseOperator::DEFINITION;
      }
    }
    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  public function addCaseSensitiveField($element, &$form_state) {
    if ($element['#compare_string_case'] && $element['#compare_string']) {
      $tree = $element['#parents'];
      $tree[] = 'operator';
      $field = $this->getFieldNameForTree($tree);
      $element['case_sensitive'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Case sensitive'),
        '#default_value' => $element['#case_sensitive'],

        '#states' => [
          'enabled' => [
            ':input[name="' . $field . '"]' => [],
          ],
          'unchecked' => [
            ':input[name="' . $field . '"]' => [],
          ],
        ],
      ];

      // Conditions under which the 'Case sensitive' checkbox can be used.
      $key = &$element['case_sensitive']['#states']['enabled'][':input[name="' . $field . '"]'];
      foreach (StringOperator::cases() as $case) {
        $key[] = ['value' => $case->name];
        $key[] = 'or';
      }
      array_pop($key);

      // Conditions under which the 'Case sensitive' checkbox is checked.
      $key = &$element['case_sensitive']['#states']['unchecked'][':input[name="' . $field . '"]'];
      $cases = array_filter(Operators::cases(), fn ($case) => !($case instanceof StringOperator));
      foreach ($cases as $case) {
        $key[] = ['value' => $case->name];
        $key[] = 'or';
      }
      array_pop($key);
    }
    return $element;
  }

  /**
   * Prepare the options array for a select field.
   *
   * @param int $operators
   *   Bitwise definition for the operators to use.
   *
   * @return array
   *   The options allowed for a select field.
   */
  protected function getOptions(int $operators = self::ALL_OPERATORS) {
    $options = [];

    $string = (string) $this->t('String operators');
    $numeric = (string) $this->t('Numeric operators');
    $bitwise = (string) $this->t('Bitwise operators');

    if ($operators & StringOperator::DEFINITION) {
      $options[$string] = [];
      foreach (StringOperator::cases() as $operator) {
        // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
        $options[$string][$operator->name] = (string) $this->t($operator->value);
      }
    }

    if ($operators & NumericOperator::DEFINITION) {
      $options[$numeric] = [];
      foreach (NumericOperator::cases() as $operator) {
        $options[$numeric][$operator->name] = $operator->value;
      }
    }

    if ($operators & BitwiseOperator::DEFINITION) {
      $options[$bitwise] = [];
      foreach (BitwiseOperator::cases() as $operator) {
        $options[$bitwise][$operator->name] = $operator->value;
      }
    }

    // Remove the optgroup if there's only one group.
    if (count($options) === 1) {
      $options = reset($options);
    }
    return $options;
  }

  /**
   * Get the fieldname identifier for a form tree structure.
   *
   * @param array $tree
   *   Array of parent element identifiers.
   *
   * @return string
   *   The name to use as an identifier for that element.
   */
  protected function getFieldNameForTree(array $tree) : string {
    $fieldName = array_shift($tree);
    while (count($tree)) {
      $identifier = array_shift($tree);
      $fieldName .= "[{$identifier}]";
    }
    return $fieldName;
  }

}
