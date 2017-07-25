<?php

namespace Drupal\lazy_image\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
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

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'classes' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['classes'] = [
      '#title' => t('Classes'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('classes'),
      '#description' => $this->t('HTML classes to add to the &lt;img> element.')
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($classes = $this->getSetting('classes')) {
      $summary[] = $this->t('Classes: @classes', ['@classes' => $classes]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach($elements as $delta => $element) {
      $element['#item_attributes']['class'] = explode(' ', $this->getSetting('classes'));
      $elements[$delta] = [
        '#type' => ['lazy_image'],
        'image' => $element,
      ];
    }

    return $elements;
  }
}
