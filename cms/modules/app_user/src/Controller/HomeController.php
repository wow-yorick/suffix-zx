<?php
/**
 * @file
 * Contains \Drupal\app_user\Controller\UserController.
 */
namespace Drupal\app_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\examples\Utility\DescriptionTemplateTrait;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends ControllerBase {

    /**
     * {@inheritdoc}
     */
    protected function getModuleName() {
        return 'app_user';
    }

    public function center(Request $request) {
        return array(
            '#markup'=>'test',
        );
    }

}