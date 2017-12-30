Commerce Quantity Increments
============================
Commerce Quantity Increments is a small module on top of
[Drupal Commerce](http://drupal.org/project/commerce), that provides possibility
to set and validate quantity increments on a per product variation level.

## Requirements

Commerce Quantity Increments depends on Drupal Commerce of course, given a strict
dependency on commerce_product sub module and a soft dependency on commerce_cart.

## Maturity

As Drupal Commerce 2.x is not feature complete and in alpha state at the time
of writing, possible pre-beta schema changes could also affect this module -
however chances are rather low, as we are only altering forms and add some
custom validation to them.

Also, a possible refactoring of this module could also lead to an internal
schema change. Update scripts won't happen before a beta release. However,
this module is quite small in general and currently only adds base field to
product variation entities.

## Credits

Commerce Quantity Increments module was originally developed and is currently
maintained by [Mag. Andreas Mayr](https://www.drupal.org/u/agoradesign).

All initial development was sponsored by [agoraDesign KG](http://www.agoradesign.at/).