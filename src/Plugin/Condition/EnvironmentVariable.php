<?php

declare(strict_types=1);

namespace Drupal\conditions\Plugin\Condition;

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
    $form['#attached']['library'][] = 'conditions/theme';

    $form['wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Environment variable'),
    ];
    $form['wrapper']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('Environment variable names are not case-sensitive.'),
      '#default_value' => $this->configuration['name'],
    ];
    $form['wrapper']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#default_value' => $this->configuration['value'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() : bool {
    return strcasecmp(
      getenv($this->configuration['name']),
      $this->configuration['value']
    ) == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $params = [
      '@name' => $this->configuration['name'],
      '@value' => $this->configuration['value'],
    ];
    if ($this->isNegated()) {
      return $this->t('Environment variable @name is not @value.', $params);
    }
    return $this->t('Environment variable @name is equal to @value.', $params);
  }

}
