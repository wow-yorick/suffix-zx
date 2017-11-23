<?php

namespace Drupal\physical\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Dimensions constraint.
 *
 * @Constraint(
 *   id = "Dimensions",
 *   label = @Translation("Dimension", context = "Validation"),
 *   type = { "physical_dimensions" }
 * )
 */
class DimensionsConstraint extends Constraint {

  public $emptyMessage = '@name field is required.';
  public $invalidMessage = '@name field must be a number.';

}
