import './bootstrap';

import Alpine from 'alpinejs'

// If you want Alpine's instance to be available globally.
window.Alpine = Alpine

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
    const next = Math.min(Math.max(scroller.scrollLeft + direction * this.cardWidth, 0), maxScroll);
    scroller.scrollTo({ left: next, behavior: 'smooth' });
  },
  next() {
    this.slide(1);
  },
  prev() {
    this.slide(-1);
  },
}));

Alpine.start()
