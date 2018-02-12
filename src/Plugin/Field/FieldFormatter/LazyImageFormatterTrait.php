<?php

namespace Drupal\lazy_image\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Common method implementations for lazy image formatters.
 *
 * If the class is capable of injecting services from the container, it should
 * inject the 'image_style' entity type storage from the 'entity_type.manager'
 * service and assign it to $this->imageStyleStorage.
 */
trait LazyImageFormatterTrait {

  /**
   * Gets the image style entity storage.
   *
   * @return \Drupal\image\ImageStyleStorageInterface
   *   The image style entity storage.
   */
  protected function getImageStyleStorage() {
    if (!$this->imageStyleStorage) {
      $this->imageStyleStorage = \Drupal::service('entity_type.manager')->getStorage('image_style');
    }

    return $this->imageStyleStorage;
  }

  /**
   * Defines the default settings for this plugin.
   *
   * @return array
   *   A list of default settings, keyed by the setting name.
   */
  public static function defaultSettings() {
    return [
      'classes' => '',
      'lazy_placeholder_style' => 'lazy_placeholder_default',
    ] + parent::defaultSettings();
  }

  /**
   * Returns a form to configure settings for the formatter.
   *
   * Invoked from \Drupal\field_ui\Form\EntityDisplayFormBase to allow
   * administrators to configure the formatter. The field_ui module takes care
   * of handling submitted form values.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form elements for the formatter settings.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['lazy_placeholder_style'] = [
      '#title' => $this->t('Placeholder image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('lazy_placeholder_style'),
      '#empty_option' => t('Disable'),
      '#options' => image_style_options(FALSE),
      '#description' => $this->t('The image style for the initial image placeholder before loading the full-sized image.'),
    ];

    $elements['classes'] = [
      '#title' => $this->t('Classes'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('classes'),
      '#description' => $this->t('HTML classes to add to the &lt;img> element.')
    ];

    return $elements;
  }

  /**
   * Returns a short summary for the current formatter settings.
   *
   * If an empty result is returned, a UI can still be provided to display
   * a settings form in case the formatter has configurable settings.
   *
   * @return string[]
   *   A short summary of the formatter settings.
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($classes = $this->getSetting('classes')) {
      $summary[] = $this->t('Classes: @classes', ['@classes' => $classes]);
    }

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);

    $placeholder_setting = $this->getSetting('lazy_placeholder_style');
    $style = isset($image_styles[$placeholder_setting]) ? $image_styles[$placeholder_setting] : $this->t('Disabled');

    $summary[] = $this->t('Lazy placeholder style: @style', ['@style' => $style]);

    return $summary;
  }

  /**
   * Builds a renderable array for a field value.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field values to be rendered.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array for $items, as an array of child elements keyed by
   *   consecutive numeric indexes starting from 0.
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    $placeholder_style = $this->getImageStyleStorage()->load($this->getSetting('lazy_placeholder_style'));

    foreach ($elements as $delta => $element) {
      $elements[$delta]['#item_attributes']['class'] = explode(' ', $this->getSetting('classes'));
      $elements[$delta]['#pre_render'][] = ['Drupal\lazy_image\Helper', 'lazyImageConvertPreRender'];

      if ($placeholder_style) {
        $file_uri = $element['#item']->entity->getFileUri();
        $elements[$delta]['#lazy_placeholder'] = $placeholder_style->buildUrl($file_uri);
      }
    }

    return $elements;
  }

}
