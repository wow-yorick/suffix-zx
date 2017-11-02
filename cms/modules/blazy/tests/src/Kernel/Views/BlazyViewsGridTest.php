<?php

namespace Drupal\Tests\blazy\Kernel\Views;

use Drupal\Core\Form\FormState;
use Drupal\views\Views;

/**
 * Test Blazy Views Grid integration.
 *
 * @coversDefaultClass \Drupal\blazy\Plugin\views\style\BlazyViews
 * @group blazy
 */
class BlazyViewsGridTest extends BlazyViewsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $testViews = ['test_blazy_file'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp($import_test_views);

    $bundle = $this->bundle;
    $this->setUpContentTypeTest($bundle);

    $data['settings'] = $this->getFormatterSettings();
    $this->display = $this->setUpFormatterDisplay($bundle, $data);

    $this->setUpContentWithItems($bundle);
  }

  /**
   * Make sure that the HTML list style markup is correct.
   */
  public function testBlazyViews() {
    $view = Views::getView('test_blazy_file');
    $this->executeView($view);
    $view->setDisplay('default');

    $style_plugin = $view->style_plugin;

    $style_plugin->options['grid']        = 4;
    $style_plugin->options['grid_medium'] = 3;
    $style_plugin->options['grid_small']  = 1;

    $this->assertInstanceOf('\Drupal\blazy\BlazyManagerInterface', $style_plugin->blazyManager(), 'BlazyManager implements interface.');
    $this->assertInstanceOf('\Drupal\blazy\Form\BlazyAdminInterface', $style_plugin->admin(), 'BlazyAdmin implements interface.');

    $settings = $style_plugin->options;

    $form = [];
    $form_state = new FormState();
    $style_plugin->buildOptionsForm($form, $form_state);
    $this->assertArrayHasKey('closing', $form);

    $style_plugin->submitOptionsForm($form, $form_state);
    $view->destroy();

    // @todo: Fields.
    $view = Views::getView('test_blazy_file');
    $this->executeView($view);
    $view->setDisplay('default');

    // Render.
    $render = $view->getStyle()->render();
    $this->assertArrayHasKey('data-blazy', $render['#attributes']);

    $output = $view->preview();
    $output = $this->blazyManager->getRenderer()->renderRoot($output);
    $this->assertTrue(strpos($output, 'data-blazy') !== FALSE, 'Blazy attribute is added to DIV.');
    $view->destroy();
  }

}
