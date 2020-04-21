<?php

namespace Drupal\lazy_image;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\image\ImageStyleInterface;

/**
 * Utility class helper for the lazy_image module.
 */
class Helper implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'lazyImageConvertPreRender',
    ];
  }

  /**
   * CSS class for JavaScript to hook into.
   *
   * @var string
   */
  const CSS_CLASS = 'lazyload';

  /**
   * The list of theme hooks that can be made lazy.
   *
   * @var string[]
   */
  const SUPPORTED_THEME_HOOKS = [
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
    // Skip conversion for media embed preview — frontend assets may not get
    // added to the parent frame/document to facilitate lazy loading.
    if (\Drupal::routeMatch()->getRouteName() == 'media.filter.preview') {
      return $element;
    }

    $compatible_themeable = isset($element['#theme']) && in_array($element['#theme'], self::SUPPORTED_THEME_HOOKS);
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

  /**
   * Creates a base64-encoded image as a string.
   *
   * @param string $uri
   *   The image file URI.
   * @param \Drupal\image\ImageStyleInterface $image_style
   *   The image style object to use.
   *
   * @return string
   *   The base64-encoded image.
   */
  public static function encodeImage($uri, ImageStyleInterface $image_style) {
    $derivative_uri = $image_style->buildUri($uri);
    $cache_key = "lazy_image:base64_encode:$derivative_uri";

    $cache_bin = \Drupal::cache('render');
    $cache = $cache_bin->get($cache_key);
    $data = '';

    if ($cache) {
      $data = $cache->data;
    }
    else {
      if (!file_exists($derivative_uri)) {
        $image_style->createDerivative($uri, $derivative_uri);
      }

      $data = [
        'data' => base64_encode(file_get_contents($derivative_uri)),
        'type' => pathinfo($derivative_uri, PATHINFO_EXTENSION),
      ];

      $cache_bin->set(
        $cache_key,
        $data,
        $image_style->getCacheMaxAge(),
        $image_style->getCacheTags()
      );
    }

    return "data:image/$data[type];base64,$data[data]";
  }

}
