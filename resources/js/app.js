import './bootstrap';
import '../css/app.css';

import Alpine from 'alpinejs';
import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize Pusher (required by Echo)
window.Pusher = Pusher;

// Initialize Laravel Echo with Reverb configuration
window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
  authEndpoint: '/broadcasting/auth',
});

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

// Heartbeat for online status tracking
// Sends ping every 3 minutes to keep user marked as online
(function initHeartbeat() {
  const HEARTBEAT_INTERVAL = 3 * 60 * 1000; // 3 minutes

  const sendHeartbeat = async () => {
    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (!csrfToken) return;

      await fetch('/heartbeat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      });
    } catch (e) {
      // Silently fail - user might be logged out
    }
  };

  // Only start heartbeat if there's a CSRF token (meaning we're in Laravel app)
  if (document.querySelector('meta[name="csrf-token"]')) {
    // Send initial heartbeat
    sendHeartbeat();
    // Then send every 3 minutes
    setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
  }
})();
