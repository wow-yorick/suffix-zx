CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Alternate Installation

INTRODUCTION
------------

This module enables you to use Xdebug breakpoints with Twig templates.

To use it, insert {{ breakpoint() }} into your template. When the processor
reaches that point, it makes a call to a Twig extension where xdebug_break() is
implemented.

The breakpoint will open in a file (BreakpointExtension.php) outside your Twig
template, but you'll be able to inspect any variables available at the
breakpoint in the template. The key values you'll see at the breakpoint are:

 - $context         These are the variables available to use in the template.
 - $environment     This is information about the Twig environment, including
                    available functions.
 - $arguments       If you supply an argument to breakpoint (e.g
                    {{ breakpoint(fields) }}), it'll be viewable here.

REQUIREMENTS
------------

This module requires the Composer package ajgl/breakpoint-twig-extension:

https://packagist.org/packages/ajgl/breakpoint-twig-extension

INSTALLATION
------------

On Drupal 8.1.x, install the module using CLI:

 1. Add the Drupal Packagist repository (if you haven't already):

  $ composer config repositories.drupal composer https://packages.drupal.org/8

 2. Require and install the module and its dependencies:

  $ composer require drupal/twig_xdebug

ALTERNATE INSTALLATION
----------------------

On Drupal 8.1.x, install the module manually:

 1. Add the git repository to your composer.json:

    "repositories": {
         ... other repos (if any) ...
         "drupal": {
             "type": "composer",
             "url": "https://packages.drupal.org/8"
         }
     }

 2. Require the module in your composer.json:

     "require": {
         ... other packages ...
         "drupal/twig_xdebug": "8.*"
     },

 3. Install the module and its dependencies:

  $ composer update drupal/twig_xdebug


On Drupal 8.0.x, install the module using composer_manager:

 1. Download twig_xdebug

  $ drush dl twig_xdebug

 2. Download composer_manager

  $ drush dl composer_manager

  Note: You don't have to enable composer_manager to use it.

 3. Run the composer_manager init script

  $ php modules/contrib/composer_manager/scripts/init.php

 4. Rebuild your composer.json file

  $ composer drupal-rebuild

 5. Update to install dependencies

  $ composer update drupal/twig_xdebug

Learn more about using composer_manager here:

https://www.drupal.org/node/2405811

