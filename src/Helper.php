<?php

namespace Drupal\lazy_image;

/**
 * Utility class helper for the lazy_iamge module.
 */
class Helper {

  /**
   * CSS class for JavaScript to hook into.
   *
   * @var string
   */
  const CSS_CLASS = 'js-lazy-image';

  /**
   * The list of themables that can be made lazy.
   *
   * @var string[]
   */
  protected static $supportedThemables = [
    'image',
    'image_style',
    'image_formatter',
    'responsive_image',
    'responsive_image_formatter',
  ];

  /**
   * Converts an image render array to lazy.
   *
   * @param array $element
   *   An image render array.
   *
   * @return array
   *   The modified render array.
   */
  public static function lazyImageConvertPreRender(array $element) {
    $compatible_themeable = isset($element['#theme']) && in_array($element['#theme'], self::$supportedThemables);
    $already_processed = isset($element['#lazy_image_processed']);

    if ($compatible_themeable && !$already_processed) {
      $fallback = $element;

      // Prevents recursive loop.
      $fallback['#lazy_image_processed'] = TRUE;
      // Add lazy image wrapper to handle no-javascript fallback markup.
      $element['#theme_wrappers'][] = 'lazy_image_wrapper';
      $element['#no_js_fallback'] = $fallback;

      // Change to lazy image rendering hook.
      $element['#theme'] .= '__lazy';
    }

    return $element;
  }

}
