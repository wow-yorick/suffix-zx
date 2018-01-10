<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce\Context;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the percentage off calculation.
 *
 * @CommercePriceRuleCalculation(
 *   id = "percentage_off",
 *   label = @Translation("Percentage off the product price"),
 *   entity_type = "commerce_product_variation",
 * )
 */
class PercentageOff extends PriceRuleCalculationBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'amount' => 0,
    ] + parent::defaultConfiguration();
  }

  /**
   * Gets the percentage amount.
   *
   * @return string
   *   The amount.
   */
  public function getAmount() {
    return (string) $this->configuration['amount'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);

    $form['amount'] = [
      '#type' => 'commerce_number',
      '#title' => $this->t('Percentage'),
      '#default_value' => $this->configuration['amount'] * 100,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#min' => 0,
      '#max' => 100,
      '#size' => 4,
      '#field_suffix' => $this->t('%'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    if (empty($values['amount'])) {
      $form_state->setError($form, $this->t('Percentage amount cannot be empty.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['amount'] = (string) ($values['amount'] / 100);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t(
      '@amount% off the product price',
      ['@amount' => $this->getAmount() * 100]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculate(
    EntityInterface $entity,
    PriceRuleInterface $price_rule,
    $quantity,
    Context $context
  ) {
    $this->assertEntity($entity);
    $adjusted_price = $entity->getPrice()->multiply((string) (1 - $this->getAmount()));
    $adjusted_price = $this->rounder->round($adjusted_price);

    return $adjusted_price;
  }

}
