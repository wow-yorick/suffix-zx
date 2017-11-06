<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "hello_test_block",
 *   admin_label = @Translation("Hello test block"),
 *   category = @Translation("Hello test World"),
 * )
 */
class HelloTestBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (!empty($config['hello_block_name'])) {
      $name = $config['hello_block_name'];
    }
    elseif (!empty($config['hello_block_abc'])) {
      $name = $config['hello_block_abc'];
    }
    else {
      $name = $this->t('to no one');
    }
    return array(
      '#markup' => $this->t('Hello @name! @test', array(
        '@name' => $name,
        '@test' => $config['hello_block_abc'],
      )),
    );
  }

   /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['hello_block_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Who'),
      '#description' => $this->t('Who do you want to say hello to?'),
      '#default_value' => isset($config['hello_block_name']) ? $config['hello_block_name'] : '',
    );
    $form['hello_block_abc'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Who me'),
      '#description' => $this->t('Who do you want to say hello to?'),
      '#default_value' => isset($config['hello_block_abc']) ? $config['hello_block_abc'] : '',
    );

    return $form;
  }

   /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['hello_block_abc'] = $values['hello_block_abc'];
    $this->configuration['hello_block_name'] = $values['hello_block_name'];
  }

   /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('hello_world.settings');
    return array(
      'hello_block_name' => $default_config->get('hello.name'),
      'hello_block_abc' => $default_config->get('hello.wow'),
    );
  }

}
