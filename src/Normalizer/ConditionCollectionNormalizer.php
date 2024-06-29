<?php

namespace Drupal\conditions\Normalizer;

use Drupal\conditions\ConditionCollection;
use Drupal\conditions\ConditionCollectionInterface;
use Drupal\conditions\LogicalOperator;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Serializes condition collections for config storage.
 *
 * @internal
 */
class ConditionCollectionNormalizer extends NormalizerBase implements DenormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface {

  /**
   * The normalizer service.
   *
   * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected NormalizerInterface $normalizer;

  /**
   * The denormalizer service.
   *
   * @var \Symfony\Component\Serializer\Normalizer\DenormalizerInterface
   */
  protected DenormalizerInterface $denormalizer;

  /**
   * {@inheritdoc}
   */
  public function setDenormalizer(DenormalizerInterface $denormalizer) {
    $this->denormalizer = $denormalizer;
  }

  /**
   * {@inheritdoc}
   */
  public function setNormalizer(NormalizerInterface $normalizer) {
    $this->normalizer = $normalizer;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {
    assert($object instanceof ConditionCollectionInterface);

    $result = [];
    foreach ($object as $entry) {
      $result[] = $this->normalizer->normalize($entry);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []): mixed {
    $conditionCollection = new ConditionCollection();
    if (empty($data)) {
      return $conditionCollection;
    }

    if (!is_array($data)) {
      throw new \UnexpectedValueException('Unsupported format.');
    }

    foreach ($data as $element) {
      $targetClass = $this->identifyChild($element);
      $result = $this->denormalizer->denormalize($element, $targetClass, $format, $context);
      $conditionCollection->append($result);
    }
    return $conditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedTypes(?string $format): array {
    return [
      ConditionCollectionInterface::class => TRUE,
      ConditionCollection::class => TRUE,
    ];
  }

  /**
   * Identify if an element is a condition collection, plugin, or operator.
   *
   * @param string|array $element
   *   The element to identify.
   *
   * @return string
   *   The class to denormalize to.
   */
  protected function identifyChild($element) : string {
    switch (gettype($element)) {
      case 'string':
        return LogicalOperator::class;

      case 'array':
        return match(TRUE) {
        (array_key_exists('id', $element) && array_key_exists('configuration', $element))=> ConditionInterface::class,
          (is_array(reset($element))) => ConditionCollectionInterface::class,
        };
    }
  }

}
