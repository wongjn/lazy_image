/**
 * @file
 * Lazy loads images.
 */

import arrayFrom from 'core-js/library/fn/array/from';

((Drupal) => {
  /**
   * CSS class hook for elements to be loaded.
   *
   * @var {string}
   */
  const CSS_CLASS = 'js-lazy-image';

  /**
   * CSS class hook for elements that have been loaded.
   *
   * @var {string}
   */
  const LOADED_CLASS = 'is-loaded';

  /**
   * CSS selector for elements not yet loaded.
   *
   * @var {string}
   */
  const UNPROCESSED_SELECTOR = `.${CSS_CLASS}:not(.${LOADED_CLASS})`;

  /**
   * Loads an element.
   *
   * @param {HTMLElement} element
   *   The element to load.
   * @param {object} options
   *   Options for loading.
   * @param {bool} [options.addLoadedClass=true]
   *   Whether to add the loaded class to the given element.
   */
  function load(element, { addLoadedClass = true } = {}) {
    // If picture element, load the children
    if (element.tagName === 'PICTURE') {
      arrayFrom(element.children).forEach(child => load(child, { addLoadedClass: false }));
      element.classList.add(LOADED_CLASS);
      return;
    }

    // Copy `lazy-` prefixed attributes without the suffix to load the image.
    arrayFrom(element.attributes)
      .filter(attribute => attribute.name.indexOf('data-lazy-') === 0)
      .forEach((attribute) => {
        element.setAttribute(attribute.name.replace(/^data-lazy-/, ''), attribute.value);
      });

    if (addLoadedClass) {
      element.classList.add(LOADED_CLASS);
    }
  }

  /**
   * Reacts on intersection.
   *
   * @param {IntersectionObserverEntry[]} entries
   *   List of entries intersecting the viewport.
   * @param {IntersectionObserver} selfObserver
   *   The IntersectionObserver that observed this intersection event.
   */
  function onIntersect(entries, selfObserver) {
    entries.forEach((entry) => {
      selfObserver.unobserve(entry.target);
      load(entry.target);
    });
  }

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(onIntersect, { rootMargin: '20px', threshold: 0.2 });

    /**
     * Loads lazy images once they are near the viewport.
     *
     * @type {Drupal~behavior}
     */
    Drupal.behaviors.lazyImageLoad = {
      attach(context) {
        arrayFrom(context.querySelectorAll(UNPROCESSED_SELECTOR))
          .forEach(target => observer.observe(target));
      },
      detach(context, drupalSettings, trigger) {
        if (trigger !== 'unload') {
          return;
        }

        arrayFrom(context.querySelectorAll(UNPROCESSED_SELECTOR))
          .forEach(target => observer.unobserve(target));
      },
    };

    return;
  }

  /**
   * Loads lazy images.
   *
   * For user-agents that do not support IntersectionObserver.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.lazyImageLoad = {
    attach(context) {
      arrayFrom(context.querySelectorAll(UNPROCESSED_SELECTOR))
        .forEach(target => load(target));
    },
  };
})(Drupal);
