<?php

namespace Drupal\Tests\lazy_image\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests lazy responsive image theme functions.
 *
 * @group lazy_image
 */
class LazyResponsiveImageThemeFunctionsTest extends KernelTestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'breakpoint',
    'file',
    'lazy_image',
    'image',
    'responsive_image',
    'responsive_image_test_module',
    'system',
  ];

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Responsive image style entity to test, with an img tag.
   *
   * @var \Drupal\responsive_image\Entity\ResponsiveImageStyle
   */
  protected $responsiveImgStyleImgTag;

  /**
   * Responsive image style entity to test, with sources.
   *
   * @var \Drupal\responsive_image\Entity\ResponsiveImageStyle
   */
  protected $responsiveImgStyleSources;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['image']);

    $this->responsiveImgStyleImgTag = ResponsiveImageStyle::create([
      'id' => 'img_tag',
      'label' => 'Img tag',
      'breakpoint_group' => 'responsive_image',
      'fallback_image_style' => 'medium',
    ])
      ->addImageStyleMapping('responsive_image.viewport_sizing', '1x', [
        'image_mapping_type' => 'sizes',
        'image_mapping' => [
          'sizes' => '(min-width: 700px) 700px, 100vw',
          'sizes_image_styles' => [
            'large',
            'medium',
            'thumbnail',
          ],
        ],
      ])
      ->save();

    $this->responsiveImgStyleSources = ResponsiveImageStyle::create([
      'id' => 'sources',
      'label' => 'Sources',
      'breakpoint_group' => 'responsive_image_test_module',
      'fallback_image_style' => 'medium',
    ])
      ->addImageStyleMapping('responsive_image_test_module.mobile', '1x', [
        'image_mapping_type' => 'image_style',
        'image_mapping' => 'thumbnail',
      ])
      ->addImageStyleMapping('responsive_image_test_module.narrow', '1x', [
        'image_mapping_type' => 'sizes',
        'image_mapping' => [
          'sizes' => '(min-width: 700px) 700px, 100vw',
          'sizes_image_styles' => [
            'large',
            'medium',
          ],
        ],
      ])
      ->addImageStyleMapping('responsive_image_test_module.wide', '1x', [
        'image_mapping_type' => 'image_style',
        'image_mapping' => 'large',
      ])
      ->save();

    $this->renderer = $this->container->get('renderer');
  }

  /**
   * Tests 'responsive_image__lazy' theme hook.
   */
  public function testLazyResponsiveImage() {
    $images = $this->getTestFiles('image');
    $image = reset($images);

    $base_build = [
      '#theme' => 'responsive_image__lazy',
      '#uri' => $image->uri,
      '#responsive_image_style_id' => 'img_tag',
    ];

    $build = $base_build;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img.lazyload'), 'Image has "lazyload" class as JavaScript library hook.');
    $this->assertCount(1, $this->cssSelect('img[data-src]:not([src])'), 'Image "src" attribute moved to "data-src".');

    $build = $base_build;
    $build['#lazy_placeholder_style'] = 'thumbnail';
    $build['#width'] = 360;
    $build['#height'] = 240;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img[src^="data:image/png;base64"]'), 'Image has url-encoded placeholder as "src" attribute.');
    $this->assertCount(1, $this->cssSelect('img[width="220"]'), 'Image has width attribute of fallback derivative.');
    $this->assertCount(1, $this->cssSelect('img[height="147"]'), 'Image has height attribute of fallback derivative.');

    $build = $base_build;
    $build['#responsive_image_style_id'] = 'sources';
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(3, $this->cssSelect('source[data-srcset]:not([srcset])'), 'Sources have "srcset" attribute moved to "data-srcset".');
  }

  /**
   * Tests 'responsive_image_formatter__lazy' theme hook.
   */
  public function testLazyResponsiveImageFormatter() {
    $this->enableModules(['entity_test', 'field', 'user']);
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('user');

    // Create image field.
    FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'image_test',
      'type' => 'image',
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'image_test',
      'bundle' => 'entity_test',
    ])->save();

    // Create file entity.
    $images = $this->getTestFiles('image');
    $image_file = reset($images);
    \Drupal::service('file_system')->copy($image_file->uri, 'public://example.png');
    $image = File::create(['uri' => 'public://example.png']);
    $image->save();

    // Create a test entity with the image field set.
    $entity = EntityTest::create();
    $entity->image_test->target_id = $image->id();
    $entity->image_test->width = 360;
    $entity->image_test->height = 240;
    $entity->save();

    $base_build = [
      '#theme' => 'responsive_image_formatter__lazy',
      '#item' => $entity->image_test,
      '#responsive_image_style_id' => 'img_tag',
    ];

    $build = $base_build;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img.lazyload'), 'Image has "lazyload" class as JavaScript library hook.');
    $this->assertCount(1, $this->cssSelect('img[data-src]:not([src])'), 'Image "src" attribute moved to "data-src".');

    $build = $base_build;
    $build['#lazy_placeholder_style'] = 'thumbnail';
    $build['#width'] = 360;
    $build['#height'] = 240;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img[src^="data:image/png;base64"]'), 'Image has url-encoded placeholder as "src" attribute.');
    $this->assertCount(1, $this->cssSelect('img[width="220"]'), 'Image has width attribute of fallback derivative.');
    $this->assertCount(1, $this->cssSelect('img[height="147"]'), 'Image has height attribute of fallback derivative.');

    $build = $base_build;
    $build['#responsive_image_style_id'] = 'sources';
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(3, $this->cssSelect('source[data-srcset]:not([srcset])'), 'Sources have "srcset" attribute moved to "data-srcset".');
  }

}
