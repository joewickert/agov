<?php

namespace Drupal\text_icon\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'text_icon' formatter.
 *
 * @FieldFormatter(
 *   id = "text_icon",
 *   label = @Translation("Text icon"),
 *   field_types = {
 *     "string",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class TextIconFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'icon_sprite' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['icon_sprite'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon sprite location'),
      '#default_value' => $this->getSetting('icon_sprite'),
      '#description' => $this->t('Enter the location to your icon sprite.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    if (!empty($settings['icon_sprite'])) {
      $summary[] = $this->t('Sprite location: @icon_sprite', ['@icon_sprite' => $settings['icon_sprite']]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => new FormattableMarkup('<svg role="presentation"><use xlink:href=":path#:icon" /></svg>', [
          ':path' => $this->getSetting('icon_sprite'),
          ':icon' => $item->value,
        ]),
      ];
    }

    return $element;
  }

}
