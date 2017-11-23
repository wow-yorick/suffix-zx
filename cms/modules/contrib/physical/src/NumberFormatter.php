<?php

namespace Drupal\physical;

use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Default number formatter.
 *
 * Uses the intl NumberFormatter class, if the intl PHP extension is enabled.
 * Otherwise, returns the numbers as given.
 *
 * Commerce swaps out this class in order to use its own NumberFormatter which
 * does not depend on the intl extension.
 */
class NumberFormatter implements NumberFormatterInterface {

  /**
   * The intl number formatter.
   *
   * @var \NumberFormatter
   */
  protected $numberFormatter;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new NumberFormatter object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    if (extension_loaded('intl')) {
      $language = $language_manager->getConfigOverrideLanguage() ?: $language_manager->getCurrentLanguage();
      $this->numberFormatter = new \NumberFormatter($language->getId(), \NumberFormatter::DECIMAL);
      // Skip rounding for the first 12 decimals.
      $this->numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 12);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function format($number) {
    if ($this->numberFormatter) {
      $number = $this->numberFormatter->format($number);
    }
    return $number;
  }

  /**
   * {@inheritdoc}
   */
  public function parse($number) {
    if ($this->numberFormatter) {
      $number = $this->numberFormatter->parse($number);
      // The returned number should be a string.
      if (is_numeric($number)) {
        $number = (string) $number;
      }
    }
    elseif (!is_numeric($number)) {
      // The intl extension is missing, validate the number at least.
      $number = FALSE;
    }
    return $number;
  }

}
