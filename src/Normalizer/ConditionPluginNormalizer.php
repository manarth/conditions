<?php

namespace Drupal\conditions\Normalizer;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionManager;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Replace the plugin object with the information needed to recreate it.
 */
class ConditionPluginNormalizer extends NormalizerBase implements DenormalizerInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Condition\ConditionManager $manager
   *   A manager for condition plugins.
   */
  public function __construct(protected ConditionManager $manager) {
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {
    assert($object instanceof ConditionInterface);
    return [
      'id' => $object->getPluginId(),
      'configuration' => $object->getConfiguration(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []): mixed {
    assert(is_array($data));
    assert(array_key_exists('id', $data) && is_string($data['id']));
    assert(array_key_exists('configuration', $data) && is_array($data['configuration']));

    return $this
      ->manager
      ->createInstance($data['id'], $data['configuration']);
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedTypes(?string $format): array {
    return [
      ConditionInterface::class => TRUE,
    ];
  }

}
