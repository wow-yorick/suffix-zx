<?php

namespace Drupal\apply\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a 'Apply' Block.
 *
 * @Block(
 *   id = "apply_block",
 *   admin_label = @Translation("Apply block"),
 *   category = @Translation("user apply"),
 * )
 */
class ApplyBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $name = $config['apply.name'];
    return array(
      '#markup' => $this->t('Hello @name!', array(
        '@name' => $name,
      )),
    );
  }

   /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['apply_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Who'),
      '#description' => $this->t('报名人?'),
      '#default_value' => isset($config['apply_name']) ? $config['apply_name'] : '',
    );

    return $form;
  }

   /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['apply_name'] = $values['apply_name'];
  }

   /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('apply.settings');
    return array(
      'apply_name' => $default_config->get('apply.name'),
    );
  }

}
