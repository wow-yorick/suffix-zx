<?php

namespace Drupal\physical\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the dimensions constraint.
 */
class DimensionsConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $properties = [
      'length' => t('Length'),
      'width' => t('Width'),
      'height' => t('Height'),
    ];
    // Drupal runs the validator only if !$value->isEmpty(), which means that
    // we can count on $value->unit and at least one number not being empty.
    foreach ($properties as $property => $label) {
      if (is_null($value->{$property}) || $value->{$property} === '') {
        $this->context->buildViolation($constraint->emptyMessage)
          ->atPath($property)
          ->setParameter('@name', $label)
          ->addViolation();
      }
      elseif (!is_numeric($value->{$property})) {
        $this->context->buildViolation($constraint->invalidMessage)
          ->atPath($property)
          ->setParameter('@name', $label)
          ->addViolation();
      }
    }
  }

}
