# Lazy Image module

Adds field formatters and a render element for rendering lazy images. This
module is not responsible for lazy-loading the images on the front-end, only
rendering the HTML to enable such practice.

This module changes `src` and  `srcset` attributes by prefixing `lazy-` to them.

This module contains:

- Responsive image field formatter
- Image field formatter
- `lazy_image` render element
