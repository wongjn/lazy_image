<?php

namespace Drupal\Tests\lazy_image\Kernel;

use Drupal\image\Entity\ImageStyle;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lazy_image\Helper;
use Drupal\Tests\TestFileCreationTrait;

/**
 * @coversDefaultClass \Drupal\lazy_image\Helper
 * @group lazy_image
 */
class HelperTest extends KernelTestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lazy_image',
  ];

  /**
   * @covers ::lazyImageConvertPreRender
   */
  public function testLazyImageConvertPreRender() {
    $themeables = [
      'image',
      'image_style',
      'image_formatter',
      'responsive_image',
      'responsive_image_formatter',
    ];
    foreach ($themeables as $theme_hook) {
      $original = ['#theme' => $theme_hook];
      $this->assertEquals(
        [
          '#theme' => "{$theme_hook}__lazy",
          '#theme_wrappers' => ['lazy_image_wrapper'],
          '#no_js_fallback' => [
            '#theme' => $theme_hook,
            '#lazy_image_processed' => TRUE,
          ],
        ],
        Helper::lazyImageConvertPreRender($original),
        "Expected pre_render conversion result for $theme_hook."
      );
    }
  }

  /**
   * @covers ::encodeImage
   */
  public function testEncodeImage() {
    $this->enableModules(['system', 'image']);
    $this->installConfig(['image']);

    $images = $this->getTestFiles('image');
    $image = reset($images);
    $encoded = Helper::encodeImage($image->uri, ImageStyle::load('thumbnail'));

    $this->assertStringStartsWith('data:image/png;base64,', $encoded, 'Has correct data url (prefix).');
  }

}
