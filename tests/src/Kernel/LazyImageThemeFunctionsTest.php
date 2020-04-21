<?php

namespace Drupal\Tests\lazy_image\Kernel;

use Drupal\Core\EventSubscriber\AjaxResponseSubscriber;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests lazy image theme functions.
 *
 * @group lazy_image
 */
class LazyImageThemeFunctionsTest extends KernelTestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'lazy_image',
    'image',
    'system',
  ];

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['image']);
    $this->renderer = $this->container->get('renderer');
  }

  /**
   * Tests 'image__lazy' theme hook.
   */
  public function testLazyImage() {
    $images = $this->getTestFiles('image');
    $image = reset($images);
    $image2 = next($images);

    $base_build = [
      '#theme' => 'image__lazy',
      '#uri' => $image->uri,
    ];

    $build = $base_build;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img.lazyload'), 'Image has "lazyload" class as JavaScript library hook.');
    $this->assertCount(1, $this->cssSelect('img[data-src]:not([src])'), 'Image "src" attribute moved to "data-src".');

    $build = $base_build;
    $build['#srcset'] = [
      [
        'uri' => $image->uri,
        'width' => 300,
      ],
      [
        'uri' => $image2->uri,
        'width' => 200,
      ],
    ];
    $build['#sizes'] = '100vw';
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img[data-srcset]:not([srcset])'), 'Image "srcset" attribute moved to "data-srcset".');

    $build = $base_build;
    $build['#lazy_placeholder_style'] = 'thumbnail';
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img[src^="data:image/png;base64"]'), 'Image has url-encoded placeholder as "src" attribute.');
  }

  /**
   * Tests 'image_style__lazy' theme hook.
   */
  public function testLazyImageStyle() {
    $images = $this->getTestFiles('image');
    $image = reset($images);

    $base_build = [
      '#theme' => 'image_style__lazy',
      '#uri' => $image->uri,
      '#style_name' => 'medium',
    ];

    $build = $base_build;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img.lazyload'), 'Image has "lazyload" class as JavaScript library hook.');
    $this->assertCount(1, $this->cssSelect('img[data-src]:not([src])'), 'Image "src" attribute moved to "data-src".');

    $build = $base_build;
    $build['#lazy_placeholder_style'] = 'thumbnail';
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img[src^="data:image/png;base64"]'), 'Image has url-encoded placeholder as "src" attribute.');
  }

  /**
   * Tests 'image_formatter__lazy' theme hook.
   */
  public function testLazyImageFormatter() {
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
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.png');
    $image = File::create(['uri' => 'public://example.png']);
    $image->save();

    // Create a test entity with the image field set.
    $entity = EntityTest::create();
    $entity->image_test->target_id = $image->id();
    $entity->save();

    $base_build = [
      '#theme' => 'image_formatter__lazy',
      '#image_style' => 'medium',
      '#item' => $entity->image_test,
    ];

    $build = $base_build;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img.lazyload'), 'Image has "lazyload" class as JavaScript library hook.');
    $this->assertCount(1, $this->cssSelect('img[data-src]:not([src])'), 'Image "src" attribute moved to "data-src".');

    $build = $base_build;
    $build['#lazy_placeholder_style'] = 'thumbnail';
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('img[src^="data:image/png;base64"]'), 'Image has url-encoded placeholder as "src" attribute.');
  }

  /**
   * Tests 'lazy_image_wrapper' theme wrapper hook.
   */
  public function testLazyImageWrapper() {
    $base_build = [
      '#theme_wrappers' => ['lazy_image_wrapper'],
      'content' => ['#markup' => '<span></span>'],
      '#no_js_fallback' => ['#markup' => '<span id="fallback"></span>'],
    ];

    $build = $base_build;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(2, $this->cssSelect('span'));
    $this->assertCount(1, $this->cssSelect('noscript > #fallback'), 'Fallback markup rendered inside noscript tags.');

    // Create a fake AJAX request.
    $request = Request::create('/', 'POST', [AjaxResponseSubscriber::AJAX_REQUEST_PARAMETER => 1]);
    $this->container->get('request_stack')->push($request);

    $build = $base_build;
    $this->setRawContent($this->renderer->renderRoot($build));
    $this->assertCount(1, $this->cssSelect('span'));
    $this->assertCount(0, $this->cssSelect('noscript'), 'No noscript tags rendered in AJAX responses.');
  }

}
