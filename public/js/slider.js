import EmblaCarousel from 'embla-carousel';
import EmblaCarouselAutoplay from 'embla-carousel-autoplay';
import EmblaCarouselAutoScroll from 'embla-carousel-auto-scroll';
import { setupTweenParallax } from './parallax'


document.addEventListener('DOMContentLoaded', () => {
  if (!EmblaCarousel) {
    console.error('EmblaCarousel is not loaded. Please ensure it is included in your project.');
    return;
  }

  // Select all Embla slider instances
  const emblaNodes = document.querySelectorAll('.os-slider');
  if (!emblaNodes.length) {
    console.warn('No Embla sliders found with the class `.os-slider`.');
    return;
  }

  // Array to store Embla instances
  const emblaInstances = [];

  /**
   * Generic functions to pause and resume plugins
   */
  const pausePlugins = (plugins) => {
    plugins.forEach((plugin) => {
      if (plugin.stop && typeof plugin.stop === 'function' && plugin.isPlaying && plugin.isPlaying()) {
        plugin.stop();
        // console.log(`Paused plugin: ${plugin.name}`);
      } else if (plugin.pause && typeof plugin.pause === 'function') {
        plugin.pause();
        // console.log(`Paused plugin: ${plugin.name}`);
      }
    });
  };

  const resumePlugins = (plugins) => {
    plugins.forEach((plugin) => {
      if (plugin.play && typeof plugin.play === 'function' && plugin.isPlaying && !plugin.isPlaying()) {
        plugin.play();
        // console.log(`Resumed plugin: ${plugin.name}`);
      } else if (plugin.resume && typeof plugin.resume === 'function') {
        plugin.resume();
        // console.log(`Resumed plugin: ${plugin.name}`);
      }
    });
  };

  /**
   * Function to initialize a single Embla slider
   */
  const initializeEmbla = (emblaNode, index) => {
    // Prevent multiple initializations
    if (emblaNode.dataset.emblaInitialized === 'true') {
      return null;
    }

    // eslint-disable-next-line no-param-reassign
    emblaNode.dataset.emblaInitialized = 'true';

    // Set the options for Embla Carousel using dataset attributes
    const OPTIONS = {
      loop: emblaNode.dataset.loop !== 'false', // Default to true unless explicitly set to false
      speed: parseInt(emblaNode.dataset.duration, 10) || 10,
      // Add other options as needed
      breakpoints: {
        // Example:
        // '(max-width: 768px)': { dragFree: true }
      },
    };

    const viewportNode = emblaNode.querySelector('.os-slider__viewport');
    const prevBtn = emblaNode.querySelector('.os-slider__button--prev');
    const nextBtn = emblaNode.querySelector('.os-slider__button--next');

    if (!viewportNode) {
      console.warn(`Embla slider instance ${index + 1} is missing the viewport element.`);
      return null;
    }

    // Prepare plugins based on data attributes
    const plugins = [];

    // Track which plugins are initialized for this slider
    const initializedPlugins = [];

    // Initialize Autoplay if data-autoplay is set to 'yes'
    if (emblaNode.dataset.autoplay === 'yes') {
      if (EmblaCarouselAutoplay) {
        const autoplayPlugin = EmblaCarouselAutoplay({
          playOnInit: true,
          stopOnInteraction: false,
          delay: parseInt(emblaNode.dataset.delay, 10) || 2500,
        });
        plugins.push(autoplayPlugin);
        initializedPlugins.push(autoplayPlugin);
        // console.log(`Initialized Autoplay plugin for slider instance ${index + 1}.`);
      } else {
        console.warn(`Autoplay plugin is not loaded for slider instance ${index + 1}.`);
      }
    }

    // Initialize AutoScroll if data-autoscroll is set to 'yes'
    if (emblaNode.dataset.autoscroll === 'yes') {
      console.warn(emblaNode.dataset.duration);
      if (EmblaCarouselAutoScroll) {
        const autoScrollPlugin = EmblaCarouselAutoScroll({
          speed: parseInt(emblaNode.dataset.duration, 10) || 5,
        });
        plugins.push(autoScrollPlugin);
        initializedPlugins.push(autoScrollPlugin);
        // console.log(`Initialized AutoScroll plugin for slider instance ${index + 1}.`);
      } else {
        console.warn(`AutoScroll plugin is not loaded for slider instance ${index + 1}.`);
      }
    }

    // Initialize Embla Carousel with the selected plugins
    const emblaApi = EmblaCarousel(viewportNode, OPTIONS, plugins);

    // Store the instance for later reference
    emblaInstances.push({
      api: emblaApi,
      node: emblaNode,
      plugins: initializedPlugins,
      index: index + 1,
    });

    /**
     * Event Listeners for Autoplay/AutoScroll Control
     * Using generic functions to handle any number of plugins
     */
    const handleMouseEnter = () => pausePlugins(initializedPlugins);
    const handleMouseLeave = () => resumePlugins(initializedPlugins);
    const handleTouchStart = () => pausePlugins(initializedPlugins);
    const handleTouchEnd = () => resumePlugins(initializedPlugins);

    // Only add event listeners if there are initialized plugins
    if (initializedPlugins.length > 0) {
      emblaNode.addEventListener('mouseenter', handleMouseEnter, false);
      emblaNode.addEventListener('mouseleave', handleMouseLeave, false);
      emblaNode.addEventListener('touchstart', handleTouchStart, false);
      emblaNode.addEventListener('touchend', handleTouchEnd, false);
    }

    // Clean up event listeners on destroy
    emblaApi.on('destroy', () => {
      if (initializedPlugins.length > 0) {
        emblaNode.removeEventListener('mouseenter', handleMouseEnter, false);
        emblaNode.removeEventListener('mouseleave', handleMouseLeave, false);
        emblaNode.removeEventListener('touchstart', handleTouchStart, false);
        emblaNode.removeEventListener('touchend', handleTouchEnd, false);
        // console.log(`Removed event listeners for slider instance ${index + 1}.`);
      }
    });

    // Optional: Initialize additional functionalities like Parallax
    if (typeof setupTweenParallax === 'function' && emblaNode.dataset.autoplay === 'yes') {
      const removeTweenParallax = setupTweenParallax(emblaApi);
      emblaApi.on('destroy', removeTweenParallax);
      // console.log(`Initialized Parallax for slider instance ${index + 1}.`);
    } else {
      console.warn(`setupTweenParallax function is not defined for slider instance ${index + 1}.`);
    }

    // Add click handlers for previous and next buttons if they exist
    if (prevBtn && nextBtn) {
      prevBtn.addEventListener('click', () => emblaApi.scrollPrev(), false);
      nextBtn.addEventListener('click', () => emblaApi.scrollNext(), false);
      // console.log(`Added navigation button listeners for slider instance ${index + 1}.`);
    } else {
      console.warn(`Navigation buttons missing for slider instance ${index + 1}.`);
    }

    return emblaApi;
  };

  /**
   * Intersection Observer Callback
   */
  const onIntersection = (entries) => {
    entries.forEach((entry) => {
      const emblaNode = entry.target;
      const index = Array.from(emblaNodes).indexOf(emblaNode) + 1;

      if (entry.isIntersecting) {
        // Initialize Embla Carousel for this node if not already initialized
        if (emblaNode.dataset.emblaInitialized !== 'true') {
          initializeEmbla(emblaNode, index);
          // console.log(`Initialized slider instance ${index} on intersection.`);
        } else {
          // If already initialized, resume plugins
          const instance = emblaInstances.find((inst) => inst.node === emblaNode);
          if (instance) {
            resumePlugins(instance.plugins);
            // console.log(`Resumed plugins for slider instance ${index} on intersection.`);
          }
        }
      } else if (emblaNode.dataset.emblaInitialized === 'true') {
        // Pause autoplay/autoscroll if initialized
        const instance = emblaInstances.find((inst) => inst.node === emblaNode);
        if (instance) {
          pausePlugins(instance.plugins);
          // console.log(`Paused plugins for slider instance ${index} on exit.`);
        }
      }
    });
  };

  /**
   * Create Intersection Observer
   * Options:
   * - root: null (viewport)
   * - rootMargin: '0px'
   * - threshold: 0.5 (50% visibility)
   */
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.03,
  };

  const observer = new IntersectionObserver(onIntersection, observerOptions);

  // Observe each Embla slider
  emblaNodes.forEach((emblaNode, index) => {
    observer.observe(emblaNode);
    // console.log(`Started observing slider instance ${index + 1}.`);
  });
});

/**
 * Compatibility for WordPress (Ensuring script works properly in WordPress environment)
 */
if (typeof window.wp !== 'undefined' && typeof window.jQuery !== 'undefined') {
  jQuery(document).ready(() => {
    if (typeof EmblaCarousel === 'undefined') {
      console.error('EmblaCarousel script is not loaded. Make sure EmblaCarousel JS is properly enqueued.');
    }
    if (typeof EmblaCarouselAutoplay === 'undefined' && typeof window.EmblaCarouselAutoScroll === 'undefined') {
      console.warn('No autoplay plugins (Autoplay or AutoScroll) are loaded. Autoplay/AutoScroll functionality will not be available.');
    }
  });
}