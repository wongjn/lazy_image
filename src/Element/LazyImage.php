<?php

namespace Drupal\lazy_image\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a lazy (responsive) image element.
 *
 * This does not provide any JavaScript loading on the frontend - it is up to
 * the developer to implement the actualy lazy-loading.
 *
 * Usage example:
 * @code
 * $build = [
 *   '#type' => 'lazy_image',
 *   'image' => [
 *      '#theme' => 'image',
 *      '#uri' => 'public://image.jpg',
 *   ],
 * ];
 * @endcode
 *
 * @RenderElement("lazy_image")
 */
class LazyImage extends RenderElement {

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
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#pre_render' => [
        [$class, 'preRenderImage'],
      ],
    ];
  }

  /**
   * Makes images as lazy.
   *
   * @param array $elements
   *   The render array.
   */
  public static function preRenderImage($elements) {
    foreach (Element::getVisibleChildren($elements) as $key) {
      $image_build = $elements[$key];

      if (isset($image_build['#theme']) && in_array($image_build['#theme'], self::$supportedThemables)) {
        $elements[$key]['#theme'] .= '__lazy';

        // No js fallback.
        $elements["${key}_fallback"] = [
          '#type' => 'inline_template',
          '#template' => '<noscript>{{ image }}</noscript>',
          '#context' => ['image' => $image_build],
        ];
      }
    }

    return $elements;
  }

}
