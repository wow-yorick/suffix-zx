<?php

namespace Drupal\slick_devel;

use Drupal\Core\Url;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Slick admin settings form.
 */
class SlickDevelSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'slick_devel_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['slick_devel.settings'];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('slick_devel.settings');

    $form['slick_devel'] = array(
      '#type' => 'details',
      '#title' => 'Slick development',
      '#description' => $this->t("Unless you are helping to develop the Slick module, all these are not needed to run Slick. Requires slick > 1.6.0"),
      '#open' => TRUE,
      '#collapsible' => FALSE,
    );

    $form['slick_devel']['unminified'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable development mode'),
      '#description' => $this->t('Load the development version of the Slick library. Only useful to test new features of the library. Leave it unchecked at production.'),
      '#default_value' => $config->get('unminified'),
    );

    $form['slick_devel']['debug'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use non-minified slick.load.js'),
      '#description' => t('Replace slick.load.min.js with slick.load.js. Only useful to debug it.'),
      '#default_value' => $config->get('debug'),
    );

    $form['slick_devel']['disable'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Disable module slick.load.js'),
      '#description' => $this->t('Slick will not run unless you initiliaze it yourself.'),
      '#default_value' => $config->get('disable'),
      '#states' => array(
        'invisible' => array(
          array(':input[name="debug"]' => array('checked' => TRUE)),
        ),
      ),
    );

    $form['slick_devel']['replace'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Replace the slick.load.js with development version: slick.load.devel.js'),
      '#description' => $this->t('Use slick.load.devel.js to debug the Slick without modifying slick.load.min.js.'),
      '#default_value' => $config->get('replace'),
      '#states' => array(
        'invisible' => array(
          array(':input[name="disable"]' => array('checked' => TRUE)),
        ),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->configFactory->getEditable('slick_devel.settings')
      ->set('unminified', $form_state->getValue('unminified'))
      ->set('debug', $form_state->getValue('debug'))
      ->set('replace', $form_state->getValue('replace'))
      ->set('disable', $form_state->getValue('disable'))
      ->save();

    // Invalidate the library discovery cache to update the responsive image.
    \Drupal::service('library.discovery')->clearCachedDefinitions();

    drupal_set_message($this->t('Be sure to <a href=":clear_cache">clear the cache</a> if trouble to see the updated settings', [':clear_cache' => Url::fromRoute('system.performance_settings')->toString()]));

    parent::submitForm($form, $form_state);
  }

}
