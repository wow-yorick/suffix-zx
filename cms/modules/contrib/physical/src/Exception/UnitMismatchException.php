<?php

namespace Drupal\physical\Exception;

/**
 * Thrown when trying to operate on measurements with different units.
 */
class UnitMismatchException extends \InvalidArgumentException {}
