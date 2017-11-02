<?php

namespace Drupal\Tests\blazy\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\blazy\Traits\BlazyUnitTestTrait;
use Drupal\Tests\blazy\Traits\BlazyCreationTestTrait;

/**
 * Tests the Blazy JavaScript using PhantomJS.
 *
 * @group blazy
 */
class BlazyJavaScriptTest extends JavascriptTestBase {

  use BlazyUnitTestTrait;
  use BlazyCreationTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'filter',
    'image',
    'node',
    'text',
    'blazy',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpVariables();

    $this->entityManager          = $this->container->get('entity.manager');
    $this->entityFieldManager     = $this->container->get('entity_field.manager');
    $this->formatterPluginManager = $this->container->get('plugin.manager.field.formatter');
    $this->blazyAdmin             = $this->container->get('blazy.admin');
    $this->blazyManager           = $this->container->get('blazy.manager');
    $bundle                       = $this->bundle;
    $data['settings']['ratio']    = '16:9';

    $this->setUpContentTypeTest($bundle);
    $this->setUpFormatterDisplay($bundle, $data);
  }

  /**
   * Test the Blazy element from loading to loaded states.
   */
  public function testFormatterDisplay() {
    $session    = $this->getSession();
    $image_path = $this->getImagePath(TRUE);
    $bundle     = $this->bundle;

    $this->setUpContentWithItems($bundle);
    $this->drupalGet('node/' . $this->entity->id());

    // Capture the loading moment.
    $this->createScreenshot($image_path . '/1_blazy_loading.png');

    // Wait a moment.
    $session->wait(1000);

    // Trigger Blazy to load images by scrolling down window.
    $session->executeScript('window.scrollTo(0, document.body.scrollHeight);');

    // Wait for the loaded images, at least one will do dependent on viewport.
    $loaded = $this->assertSession()->waitForElement('css', '.b-loaded');
    $this->assertNotEmpty($loaded, 'Blazy image is loaded, one or more.');

    // Wait a moment.
    $session->wait(10000);

    // Capture the loaded moment, only images within viewport are loaded here.
    $this->createScreenshot($image_path . '/2_blazy_loaded.png');
  }

}
