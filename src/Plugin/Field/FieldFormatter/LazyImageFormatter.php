<?php

namespace Drupal\lazy_image\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the 'lazy_image' formatter.
 *
 * @FieldFormatter(
 *   id = "lazy_image",
 *   label = @Translation("Lazy Image"),
 *   field_types = {
 *     "image"
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class LazyImageFormatter extends ImageFormatter {

  use LazyImageFormatterTrait;

}
