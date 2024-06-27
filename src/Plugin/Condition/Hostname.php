<?php

declare(strict_types=1);

namespace Drupal\conditions\Plugin\Condition;

use Drupal\conditions\ComparisonOperator\Operators;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A condition which compares the request hostname against a value.
 *
 * @Condition(
 *   id = "hostname",
 *   label = @Translation("Hostname"),
 * )
 */
class Hostname extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param string $hostname
   *   The hostname provided in the HTTP Request.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, protected string $hostname) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $hostname = $container
      ->get('request_stack')
      ->getCurrentRequest()
      ->getHttpHost();

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $hostname,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'conditions/admin';

    $form['wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Hostname'),
      '#attributes' => [
        'class' => [
          'condition',
          $this->getPluginId(),
        ],
      ],
    ];

    $form['wrapper']['comparison_operator'] = [
      '#type' => 'comparison_operator',
      '#title' => $this->t('Operator'),
      '#title_display' => 'attribute',
      '#default_value' => $this->configuration['comparison_operator'],
    ];

    $form['wrapper']['hostname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hostname'),
      '#title_display' => 'attribute',
      '#description' => $this->t('Hostnames are not case-sensitive.<br />Do not include https:// or the path.'),
      '#default_value' => $this->configuration['hostname'],
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'hostname',
        ],
        'placeholder' => 'www.example.com',
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
      $this->configuration['hostname'],
      $this->hostname,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $params = [
      '@hostname' => $this->configuration['hostname'],
    ];
    if ($this->isNegated()) {
      return $this->t('Hostname is not @hostname.', $params);
    }
    return $this->t('Hostname is equal to @hostname.', $params);
  }

}
