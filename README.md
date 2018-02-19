# Lazy Image module

Adds field formatters and theme hooks for rendering lazy images.

This module changes `src` and  `srcset` attributes by prefixing `data-lazy-` to
them.

This module contains:

- Responsive image field formatter
- Image field formatter
- `lazy_image_wrapper` theme hook
- `Drupal\lazy_image\Helper` class
- `lazy_placeholder_default` image style for placeholder image
- JavaScript loader to load the images

## Writing a render array

To write a render array for a lazy image, write a normal render array for a
compatible theme hook (look in `Drupal\lazy_image\Helper::supportedThemeHooks`).
Optionally add a path to an image as the value of `#lazy_placeholder` to use it
as the image to be in-place before lazy loading happens. Finally, add
`Drupal\lazy_image\Helper::lazyImageConvertPreRender()` as a value in the
`#pre_render` key.

```php
$build = [
  '#theme' => 'image',
  ...
  '#lazy_placeholder' => $placeholder_path,
  '#pre_render' => [
    ['Drupal\lazy_image\Helper', 'lazyImageConvertPreRender'],
  ],
];
```
