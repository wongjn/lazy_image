<?php

namespace Drupal\Tests\lazy_image\Kernel;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\lazy_image\Plugin\Field\FieldFormatter\LazyImageFormatterTrait;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;

/**
 * Tests the lazy image formatter trait.
 *
 * @group lazy_image
 */
class LazyImageFormatterTraitTest extends FieldKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'image',
    'lazy_image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['image']);
  }

  /**
   * Tests default settings.
   */
  public function testDefaultSettings() {
    $defaults = LazyImageFormatterTraitTestClass::defaultSettings();
    $expected = [
      'classes' => '',
      'lazy_placeholder_style' => 'lazy_placeholder_default',
    ];
    $this->assertArraySubset($expected, $defaults, FALSE, 'Default settings from LazyImageFormatterTrait are present.');
  }

  /**
   * Tests settings summary output.
   */
  public function testSettingsSummary() {
    $cases = [
      [
        '',
        t('Lazy placeholder style: @style', ['@style' => t('Disabled')]),
        'Show as disabled for empty setting value.',
      ],
      [
        'medium',
        t('Lazy placeholder style: @style', ['@style' => 'Medium (220×220)']),
        'Show image style label for valid setting value.',
      ],
      [
        'foobar',
        t('Lazy placeholder style: @style', ['@style' => t('Disabled')]),
        'Show as disabled for missing image style.',
      ],
    ];

    foreach ($cases as $case) {
      list($value, $expected, $message) = $case;
      $formatter = $this->createFormatter(['lazy_placeholder_style' => $value]);
      $this->assertContains((string) $expected, array_map('strval', $formatter->settingsSummary()), $message);
    }
  }

  /**
   * Tests field item viewing.
   */
  public function testViewElements() {
    $items = $this->createMock('\Drupal\Core\Field\FieldItemList');
    $items->method('getIterator')->willReturn(new \ArrayIterator([[]]));

    $settings = [
      'classes' => $this->randomMachineName() . ' ' . $this->randomMachineName(),
      'lazy_placeholder_style' => mb_strtolower($this->randomMachineName()),
    ];
    $build = $this->createFormatter($settings)->viewElements($items, 'und')[0];

    $expected_classes = explode(' ', $settings['classes']);
    $this->assertEquals($expected_classes[0], $build['#item_attributes']['class'][0], 'Class setting added to image attributes.');
    $this->assertEquals($expected_classes[1], $build['#item_attributes']['class'][1], 'Class setting added to image attributes.');

    $this->assertEquals(
      ['Drupal\lazy_image\Helper', 'lazyImageConvertPreRender'],
      $build['#pre_render'][0],
      '#pre_render helper added.'
    );

    $this->assertEquals($settings['lazy_placeholder_style'], $build['#lazy_placeholder_style'], 'Placeholder image style setting added as render property.');
  }

  /**
   * Tests calculation of dependencies.
   */
  public function testCalculateDependencies() {
    $formatter = $this->createFormatter(['lazy_placeholder_style' => 'medium']);
    $this->assertEquals(['config' => ['image.style.medium']], $formatter->calculateDependencies());
  }

  /**
   * Tests dependency removal.
   */
  public function testOnDependencyRemoval() {
    /** @var \Drupal\image\ImageStyleStorageInterface $image_style_storage */
    $image_style_storage = \Drupal::entityTypeManager()->getStorage('image_style');
    $image_style_storage->setReplacementId('medium', 'large');

    $formatter = $this->createFormatter(['lazy_placeholder_style' => 'medium'])
      ->setImageStyleStorage($image_style_storage);
    $changed = $formatter->onDependencyRemoval(['config' => ['image.style.medium']]);

    $this->assertTrue($changed, 'Plugin configuration marked as changed.');
    $this->assertEquals('large', $formatter->getSetting('lazy_placeholder_style'), 'Formatter uses replacement image style.');
  }

  /**
   * Create a formatter.
   */
  protected function createFormatter(array $settings = []) {
    $field = $this->createMock('Drupal\Core\Field\FieldDefinitionInterface');
    return new LazyImageFormatterTraitTestClass('test', [], $field, $settings, $this->randomMachineName(), 'full', []);
  }

}

/**
 * Test class that extends from FormatterBase.
 */
class LazyImageFormatterTraitTestClassBase extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    return iterator_to_array($items);
  }

}

/**
 * Test class that uses LazyImageFormatterTrait.
 */
class LazyImageFormatterTraitTestClass extends LazyImageFormatterTraitTestClassBase {

  use LazyImageFormatterTrait;

}
