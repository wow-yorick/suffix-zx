<?php

namespace Drupal\physical;

/**
 * Allows parsing and formatting numbers using language-specific rules.
 *
 * For example, if the current language is 'fr', then commas will be used
 * as decimal separators instead of the usual dots.
 */
interface NumberFormatterInterface {

  /**
   * Formats the given number for the current language.
   *
   * @param string $number
   *   The number.
   *
   * @return string
   *   The formatted number.
   */
  public function format($number);

  /**
   * Parses the given number.
   *
   * Replaces language-specific characters with the standard ones.
   *
   * @param string $number
   *   The number, formatted according to the current language.
   *
   * @return string|false
   *   The parsed number, or FALSE on error.
   */
  public function parse($number);

}
