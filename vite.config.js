import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';
import { existsSync, statSync } from 'node:fs';
import { resolve, dirname } from 'node:path';

// Custom plugin to resolve directory imports to index.ts
function resolveIndex() {
  const jsDir = fileURLToPath(new URL('./resources/js', import.meta.url));

  return {
    name: 'resolve-index',
    resolveId(source, importer) {
      // Handle @/ alias imports
      if (source.startsWith('@/')) {
        const relativePath = source.slice(2);
        const fullPath = resolve(jsDir, relativePath);

        // Check if it's a directory with index file
        try {
          if (existsSync(fullPath) && statSync(fullPath).isDirectory()) {
            // Try different index extensions
            for (const ext of ['/index.ts', '/index.js', '/index.vue']) {
              const indexPath = fullPath + ext;
              if (existsSync(indexPath)) {
                return indexPath;
              }
            }
          }
        } catch (e) {
          // Ignore errors, let other resolvers handle it
        }
      }
      return null;
    }
  };
}

export default defineConfig({
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
    },
    extensions: ['.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json', '.vue'],
  },
  plugins: [
    resolveIndex(),
    vue(),
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    tailwindcss(),
  ],
});
