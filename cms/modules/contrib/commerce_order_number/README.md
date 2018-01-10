Commerce order number
=====================
Commerce order number is a module for
[Drupal Commerce](https://drupal.org/project/commerce), that provides an
extensible framework for generating ID independent order numbers.

By default, Commerce sets the ID of the order entity as order number as well,
which leads to gaps (unfinished carts) and not always ascending order numbers.
This can cause problems, if orders are exported to external accounting systems,
when sequential order numbers are presumed.

The number generators are implemented as plugins, so custom ones can easily be
added. The module ships with three default implementations, that cover all the
invoice number generations from the 7.x version of
[Commerce Billy](https://www.drupal.org/project/commerce_billy). These are
infinite, yearly and monthly increments.

Number formatting options include prefixes and suffixes, as well as number
padding to a fixed string length.

## Requirements

Commerce order number depends on Drupal Commerce, including commerce_order sub
module.

**Until [this issue](https://www.drupal.org/node/2842356) is resolved, the patch
posted in that issue must be applied (otherwise, Commerce will already set an
order number to carts in draft state). Alternatively, turning on the
"Force override" option will also work.**

## Credits

Commerce order number module was originally developed and is currently
maintained by [Mag. Andreas Mayr](https://www.drupal.org/u/agoradesign).

All initial development was sponsored by [agoraDesign KG](https://www.agoradesign.at).
