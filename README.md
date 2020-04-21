# Lazy Image module

Adds field formatters and theme hooks for rendering lazy images.

This module changes `src` and  `srcset` attributes by prefixing `data-` to
them.

This module contains:

- Responsive image field formatter
- Image field formatter
- `lazy_image_wrapper` theme hook
- `Drupal\lazy_image\Helper` class
- `lazy_placeholder_default` image style for placeholder image
- Third-party JavaScript loader [lazysizes](https://github.com/aFarkas/lazysizes)
to load the images

## Writing a render array

To write a render array for a lazy image, write a normal render array for a
compatible theme hook (look in `Drupal\lazy_image\Helper::supportedThemeHooks`).
Optionally add an image style name to generate a placeholder image (the image
shown before the image is actually lazy-loaded) using the
`#lazy_placeholder_style` render key. Finally, add
`Drupal\lazy_image\Helper::lazyImageConvertPreRender()` as a value in the
`#pre_render` key.

```php
$build = [
  '#theme' => 'image',
  ...
  '#lazy_placeholder_style' => 'lazy_image_style',
  '#pre_render' => [
    ['Drupal\lazy_image\Helper', 'lazyImageConvertPreRender'],
  ],
];
```
