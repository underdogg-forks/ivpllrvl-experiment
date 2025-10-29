import { defineConfig } from 'vite';
import { glob } from 'glob';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Vite Configuration for InvoicePlane
 * 
 * Replaces Gruntfile.js with modern build tooling
 * 
 * Features:
 * - SCSS/SASS compilation with autoprefixer
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
    emptyOutDir: true,
    
    // Generate sourcemaps for debugging
    sourcemap: process.env.NODE_ENV === 'development',
    
    // Minification
    minify: process.env.NODE_ENV === 'production' ? 'terser' : false,
    
    rollupOptions: {
      input: {
        // Main application scripts
        'core/js/scripts': 'resources/assets/core/js/scripts.js',
        'core/js/dependencies': 'resources/assets/core/js/dependencies-entry.js',
        'core/js/legacy': 'resources/assets/core/js/legacy-entry.js',
        
        // Styles - find all SCSS files
        ...Object.fromEntries(
          glob.sync('resources/assets/**/sass/*.scss').map(file => {
            const name = file
              .replace('resources/assets/', '')
              .replace('/sass/', '/css/')
              .replace('.scss', '');
            return [name, file];
          })
        ),
      },
      output: {
        // Output naming patterns
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          // Keep CSS in the same structure
          if (assetInfo.name?.endsWith('.css')) {
            return assetInfo.name.replace('resources/assets/', '');
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
    
    // Proxy to CodeIgniter backend
    proxy: {
      // Proxy all non-asset requests to the PHP backend
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
  
  // CSS configuration
  css: {
    preprocessorOptions: {
      scss: {
        // Add any global SCSS variables/mixins here if needed
      },
    },
    postcss: {
      plugins: [
        require('autoprefixer'),
      ],
    },
  },
  
  // Resolve configuration
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/assets'),
      '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap-sass'),
    },
  },
  
  // Plugin configuration
  plugins: [
    // Custom plugin to copy assets
    {
      name: 'copy-assets',
      async buildEnd() {
        const fs = await import('fs-extra');
        
        // Copy datepicker locales
        await fs.copy(
          'node_modules/bootstrap-datepicker/js/locales',
          'public/assets/core/js/locales',
          { overwrite: true }
        );
        
        // Copy select2 locales
        await fs.copy(
          'node_modules/select2/dist/js/i18n',
          'public/assets/core/js/locales/select2',
          { overwrite: true }
        );
        
        // Copy font-awesome fonts
        await fs.copy(
          'node_modules/font-awesome/fonts',
          'public/assets/core/fonts',
          { overwrite: true }
        );
      },
    },
  ],
});
