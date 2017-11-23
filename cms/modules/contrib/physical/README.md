Physical Fields
===============
Provides an API for storing and manipulating physical measurements.

Supported measurement types:
- Area
- Length
- Volume
- Weight

Provided field types:
- Physical measurement: Stores a single measurement and its unit.
- Physical dimensions: Stores the length/width/height measurements and their unit.

Other features:
- Unit conversions
- Language-specific number input and formatting
- Value objects with support for bcmath-based arithmetic.

```php
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;

$weight = new Weight('100', WeightUnit::KILOGRAM);
$other_weight = new Weight('120', WeightUnit::KILOGRAM);
// Add the two weights together then express them in pounds.
$new_weight = $weight->add($other_weight)->convert(WeightUnit::POUND);
```