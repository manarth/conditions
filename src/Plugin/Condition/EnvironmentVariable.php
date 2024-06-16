<?php

declare(strict_types=1);

namespace Drupal\conditions\Plugin\Condition;

use Drupal\conditions\ComparisonOperator\Operators;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A condition which compares an environment variable against a value.
 *
 * @Condition(
 *   id = "environment_variable",
 *   label = @Translation("Environment Variable"),
 * )
 */
class EnvironmentVariable extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'conditions/admin';

    $form['wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Environment variable'),
      '#attributes' => [
        'class' => [
          'condition',
          $this->getPluginId(),
        ],
      ],
    ];

    // @todo Provide an autocomplete for the available environment variables.
    $form['wrapper']['variable_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Variable name'),
      '#description' => $this->t('Environment variable names are not case-sensitive.'),
      '#default_value' => $this->configuration['variable_name'],
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'variable_name',
        ],
        'placeholder' => 'SERVER_PORT',
      ],
    ];

    $form['wrapper']['comparison_operator'] = [
      '#type' => 'comparison_operator',
      '#title' => $this->t('Operator'),
      '#title_display' => 'before',
      '#default_value' => $this->configuration['comparison_operator'],
      '#compare_numeric' => TRUE,
    ];

    $form['wrapper']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#default_value' => $this->configuration['value'],
      '#attributes' => [
        'placeholder' => '80',
      ],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() : bool {
    $operator = Operators::getOperatorFor($this->configuration['comparison_operator']);
    return $operator->compare(
      getenv($this->configuration['variable_name']),
      $this->configuration['value'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $params = [
      '@name' => $this->configuration['variable_name'],
      '@value' => $this->configuration['value'],
    ];
    if ($this->isNegated()) {
      return $this->t('Environment variable @name is not @value.', $params);
    }
    return $this->t('Environment variable @name is equal to @value.', $params);
  }

}
