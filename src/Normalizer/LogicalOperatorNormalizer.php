<?php

namespace Drupal\conditions\Normalizer;

use Drupal\conditions\LogicalOperator;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Replace the logical operator enum with its backed value for serialization.
 */
class LogicalOperatorNormalizer extends NormalizerBase implements DenormalizerInterface {

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {
    assert($object instanceof LogicalOperator);
    return $object->value;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []): mixed {
    assert(is_string($data));
    $operator = LogicalOperator::tryFrom($data);
    assert($operator instanceof LogicalOperator);
    return $operator;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedTypes(?string $format): array {
    return [
      LogicalOperator::class => TRUE,
    ];
  }

}
