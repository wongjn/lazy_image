<?php

namespace Drupal\lazy_image\Plugin\Field\FieldFormatter;

use Drupal\responsive_image\Plugin\Field\FieldFormatter\ResponsiveImageFormatter;

/**
 * Plugin implementation of the 'lazy_responsive_image' formatter.
 *
 * @FieldFormatter(
 *   id = "lazy_responsive_image",
 *   label = @Translation("Lazy Responsive Image"),
 *   field_types = {
 *     "image"
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class LazyResponsiveImageFormatter extends ResponsiveImageFormatter {

  use LazyImageFormatterTrait;

}
