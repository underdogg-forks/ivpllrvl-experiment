import { defineConfig } from 'vite';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Vite Configuration for InvoicePlane
 * 
 * Modern build tooling with Tailwind CSS support
 * 
 * Features:
 * - Tailwind CSS compilation with PostCSS
 * - JavaScript bundling and minification
 * - Asset copying (fonts, locales)
 * - Hot module replacement for development
 */

export default defineConfig({
  // Base public path
  base: '/assets/',
  
  // Build configuration
  build: {
    // Output to public/assets
    outDir: 'public/assets',
    emptyOutDir: false, // Changed to false to preserve other assets
    
    // Generate sourcemaps for debugging
    sourcemap: process.env.NODE_ENV === 'development',
    
    // Minification
    minify: process.env.NODE_ENV === 'production' ? 'terser' : false,
    
    rollupOptions: {
      input: {
        // Main Tailwind CSS styles
        'core/css/style-tailwind': 'resources/assets/core/css/style-tailwind.css',
        
        // Existing custom CSS files
        'core/css/custom-pdf': 'resources/assets/core/css/custom-pdf.css',
        'core/css/paypal': 'resources/assets/core/css/paypal.css',
        
        // Main application script (if it exists)
        ...((() => {
          try {
            require.resolve('./resources/assets/core/js/scripts.js');
            return { 'core/js/scripts': 'resources/assets/core/js/scripts.js' };
          } catch {
            return {};
          }
        })()),
      },
      output: {
        // Output naming patterns
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          // Keep CSS in the same structure
          if (assetInfo.name?.endsWith('.css')) {
            return '[name][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
  
  // Development server
  server: {
    port: 5173,
    strictPort: false,
    
    // Proxy to backend (if needed)
    proxy: {
      '^(?!/assets/).*': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
    
    // Watch for changes
    watch: {
      include: ['resources/assets/**'],
    },
  },
  
  // CSS configuration with Tailwind CSS
  css: {
    postcss: './postcss.config.js',
  },
  
  // Resolve configuration
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/assets'),
    },
  },
  
  // Plugin configuration
  plugins: [],
});
