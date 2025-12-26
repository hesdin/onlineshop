import './bootstrap';
import '../css/app.css';

import Alpine from 'alpinejs';
import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

window.Alpine = Alpine;

Alpine.data('collectionSlider', () => ({
  cardWidth: 0,
  gap: 0,
  init() {
    this.$nextTick(() => {
      const scroller = this.$refs.scroller;
      if (!scroller) return;
      const firstCard = scroller.querySelector('.product-card');
      if (!firstCard) return;
      const style = getComputedStyle(scroller);
      const gapValue = parseFloat(style.columnGap || style.gap || 0);
      this.gap = Number.isNaN(gapValue) ? 0 : gapValue;
      this.cardWidth = firstCard.getBoundingClientRect().width + this.gap;
    });
  },
  slide(direction = 1) {
    if (!this.$refs.scroller || !this.cardWidth) return;
    const scroller = this.$refs.scroller;
    const maxScroll = scroller.scrollWidth - scroller.clientWidth;
    const next = Math.min(
      Math.max(scroller.scrollLeft + direction * this.cardWidth, 0),
      maxScroll,
    );
    scroller.scrollTo({ left: next, behavior: 'smooth' });
  },
  next() {
    this.slide(1);
  },
  prev() {
    this.slide(-1);
  },
}));

Alpine.start();

const appName = import.meta.env.VITE_APP_NAME ?? 'Laravel';

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
  progress: {
    color: '#0ea5e9',
  },
});
