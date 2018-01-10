<?php

namespace Drupal\Tests\dc_ajax_add_cart\Kernel;

use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\dc_ajax_add_cart\RefreshPageElementsHelper;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Tests RefreshPageElementsHelper methods.
 *
 * @coversDefaultClass \Drupal\dc_ajax_add_cart\RefreshPageElementsHelper
 *
 * @ingroup dc_ajax_add_cart
 *
 * @group dc_ajax_add_cart
 */
class RefreshPageElementsHelperTest extends CommerceKernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'commerce_order',
    'dc_ajax_add_cart',
    'entity_reference_revisions',
    'profile',
    'state_machine',
  ];

  /**
   * The block storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $controller;

  /**
   * Active theme.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $activeTheme;

  /**
   * Status messages block id.
   *
   * @var string
   */
  protected $statusMessagesBlockId;

  /**
   * RefreshPageElementsHelper service.
   *
   * @var \Drupal\dc_ajax_add_cart\RefreshPageElementsHelper
   */
  protected $refreshPageElementsHelper;

  /**
   * Ajax command names expected to be present in status update ajax response.
   *
   * @var array
   */
  protected $expectedAjaxCommandNamesStatusMessagesUpdate = [
    'remove',
    'insert',
  ];

  /**
   * Ajax command names expected to be present in update cart ajax response.
   *
   * @var array
   */
  protected $expectedAjaxCommandNamesCartBlockUpdate = [
    'insert',
  ];

  /**
   * Ajax command names expected to be present in update form build id response.
   *
   * @var array
   */
  protected $expectedAjaxCommandNamesFormBuildIdUpdate = [
    'update_build_id',
  ];

  /**
   * Ajax command names expected to be present when page elements updated.
   *
   * @var array
   */
  protected $expectedAjaxCommandNamesUpdatePageElements = [
    'remove',
    'insert',
    'update_build_id',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->controller = $this->container->get('entity_type.manager')->getStorage('block');
    $this->activeTheme = $this->container->get('theme.manager')->getActiveTheme();
    $this->refreshPageElementsHelper = $this->container->get('dc_ajax_add_cart.refresh_page_elements_helper');
    $this->statusMessagesBlockId = $this->randomMachineName();
  }

  /**
   * Asserts whether response is an ajax response.
   *
   * @param object $response
   *   The response to be checked.
   */
  protected function assertAjaxResponse($response) {
    $this->assertTrue($response instanceof AjaxResponse, 'Ajax response is not returned.');
  }

  /**
   * Asserts whether the object is an instance of RefreshPageElementsHelper.
   *
   * @param object $object
   *   The object to be checked.
   */
  protected function assertInstanceOfRefreshPageElementsHelper($object) {
    $this->assertTrue($object instanceof RefreshPageElementsHelper, 'Not an instance of RefreshPageElementsHelper.');
  }

  /**
   * Places status messages block.
   */
  protected function placeStatusMessagesBlock() {
    $entity = $this->controller->create([
      'id' => $this->statusMessagesBlockId,
      'theme' => $this->activeTheme->getName(),
      'region' => 'content',
      'plugin' => 'system_messages_block',
    ]);
    $entity->save();
  }

  /**
   * Tests getStatusMessagesBlockId().
   *
   * @covers ::getStatusMessagesBlockId
   */
  public function testStatusMessageBlockId() {
    $this->placeStatusMessagesBlock();

    $this->assertEquals($this->refreshPageElementsHelper->getStatusMessagesBlockId(), $this->statusMessagesBlockId, 'Status messages block is not present.');
  }

  /**
   * Negative test getStatusMessagesBlockId().
   *
   * @covers ::getStatusMessagesBlockId
   */
  public function testNoStatusMessageBlockId() {
    $this->assertNull($this->refreshPageElementsHelper->getStatusMessagesBlockId(), 'Status messages block is present.');
  }

  /**
   * Tests ajax response when status messages block is placed.
   *
   * @covers ::getResponse
   * @covers ::updateStatusMessages
   */
  public function testAjaxResponseStatusMessagesBlock() {
    $this->placeStatusMessagesBlock();

    $refreshPageElements = $this->refreshPageElementsHelper
      ->updateStatusMessages();
    $this->assertInstanceOfRefreshPageElementsHelper($refreshPageElements);

    $response = $refreshPageElements->getResponse();
    $this->assertAjaxResponse($response);

    // Check if the returned response has the expected ajax commands.
    $ajax_commands = $response->getCommands();
    $actual_ajax_command_names = array_map(function ($i) {
      return $i['command'];
    }, $ajax_commands);

    foreach ($this->expectedAjaxCommandNamesStatusMessagesUpdate as $ajax_command_name) {
      $this->assertTrue(in_array($ajax_command_name, $actual_ajax_command_names), "$ajax_command_name is not present");
    }
  }

  /**
   * Tests ajax response when status messages block is not placed.
   *
   * @covers ::getResponse
   * @covers ::updateStatusMessages
   */
  public function testAjaxResponseNoStatusMessagesBlock() {
    $refreshPageElements = $this->refreshPageElementsHelper
      ->updateStatusMessages();
    $this->assertInstanceOfRefreshPageElementsHelper($refreshPageElements);

    $response = $refreshPageElements->getResponse();
    $this->assertAjaxResponse($response);

    // The returned response should not have the expected ajax commands.
    $ajax_commands = $response->getCommands();
    $actual_ajax_command_names = array_map(function ($i) {
      return $i['command'];
    }, $ajax_commands);

    foreach ($this->expectedAjaxCommandNamesStatusMessagesUpdate as $ajax_command_name) {
      $this->assertFalse(in_array($ajax_command_name, $actual_ajax_command_names), "$ajax_command_name is present");
    }
  }

  /**
   * Tests ajax response when cart block is updated.
   *
   * @covers ::getCartBlock
   * @covers ::updateCart
   * @covers ::getResponse
   */
  public function testAjaxResponseCartBlock() {
    $refreshPageElements = $this->refreshPageElementsHelper
      ->updateCart();
    $this->assertInstanceOfRefreshPageElementsHelper($refreshPageElements);

    $response = $refreshPageElements->getResponse();
    $this->assertAjaxResponse($response);

    // Check if the returned response has the expected ajax commands.
    $ajax_commands = $response->getCommands();
    $actual_ajax_command_names = array_map(function ($i) {
      return $i['command'];
    }, $ajax_commands);

    foreach ($this->expectedAjaxCommandNamesCartBlockUpdate as $ajax_command_name) {
      $this->assertTrue(in_array($ajax_command_name, $actual_ajax_command_names), "$ajax_command_name is not present");
    }
  }

  /**
   * Tests ajax response when form build id is updated.
   *
   * @covers ::updateFormBuildId
   */
  public function testAjaxResponseFormBuildId() {
    $form_build_id_old = $this->randomMachineName();
    $form_build_id = $this->randomMachineName();

    $refreshPageElements = $this->refreshPageElementsHelper
      ->updateFormBuildId([
        '#build_id_old' => $form_build_id_old,
        '#build_id' => $form_build_id,
      ]);
    $this->assertInstanceOfRefreshPageElementsHelper($refreshPageElements);

    $response = $refreshPageElements->getResponse();
    $this->assertAjaxResponse($response);

    // Check if the returned response has the expected ajax commands.
    $ajax_commands = $response->getCommands();
    $actual_ajax_command_names = array_map(function ($i) {
      return $i['command'];
    }, $ajax_commands);

    foreach ($this->expectedAjaxCommandNamesFormBuildIdUpdate as $ajax_command_name) {
      $this->assertTrue(in_array($ajax_command_name, $actual_ajax_command_names), "$ajax_command_name is not present");
    }
  }

  /**
   * Negative tests ajax response when form build id is updated.
   *
   * @covers ::updateFormBuildId
   */
  public function testNoAjaxResponseFormBuildId() {
    $form_build_id_old = $form_build_id = $this->randomMachineName();

    $refreshPageElements = $this->refreshPageElementsHelper
      ->updateFormBuildId([
        '#build_id_old' => $form_build_id_old,
        '#build_id' => $form_build_id,
      ]);
    $this->assertInstanceOfRefreshPageElementsHelper($refreshPageElements);

    $response = $refreshPageElements->getResponse();
    $this->assertAjaxResponse($response);

    // Check if the returned response has the expected ajax commands.
    $ajax_commands = $response->getCommands();
    $actual_ajax_command_names = array_map(function ($i) {
      return $i['command'];
    }, $ajax_commands);

    foreach ($this->expectedAjaxCommandNamesFormBuildIdUpdate as $ajax_command_name) {
      $this->assertFalse(in_array($ajax_command_name, $actual_ajax_command_names), "$ajax_command_name is present");
    }
  }

  /**
   * Tests updatePageElements().
   *
   * @covers ::updateStatusMessages
   * @covers ::getCartBlock
   * @covers ::updateCart
   * @covers ::updateFormBuildId
   * @covers ::updatePageElements
   * @covers ::getResponse
   */
  public function testAjaxResponseUpdatePageElements() {
    $this->placeStatusMessagesBlock();
    $form_build_id_old = $this->randomMachineName();
    $form_build_id = $this->randomMachineName();

    $refreshPageElements = $this->refreshPageElementsHelper
      ->updatePageElements([
        '#build_id_old' => $form_build_id_old,
        '#build_id' => $form_build_id,
      ]);
    $this->assertInstanceOfRefreshPageElementsHelper($refreshPageElements);

    $response = $refreshPageElements->getResponse();
    $this->assertAjaxResponse($response);

    // Check if the returned response has the expected ajax commands.
    $ajax_commands = $response->getCommands();
    $actual_ajax_command_names = array_map(function ($i) {
      return $i['command'];
    }, $ajax_commands);

    foreach ($this->expectedAjaxCommandNamesUpdatePageElements as $ajax_command_name) {
      $this->assertTrue(in_array($ajax_command_name, $actual_ajax_command_names), "$ajax_command_name is not present");
    }
  }

}
