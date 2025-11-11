import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => ({
	plugins: [
		laravel({
			input: ['resources/css/app.css', 'resources/js/app.js'],
			refresh: true,
		}),
		tailwindcss(),
	],
	build: {
		// Code splitting for better caching
		rollupOptions: {
			output: {
				// Add hash to filenames for cache busting
				entryFileNames: 'assets/[name].[hash].js',
				chunkFileNames: 'assets/[name].[hash].js',
				assetFileNames: 'assets/[name].[hash].[ext]',
				manualChunks: {
					'vendor': ['axios'],
					'chart': ['chart.js'],
				},
			},
		},
		// Optimize chunk size
		chunkSizeWarningLimit: 1000,
		// Enable minification
		minify: 'terser',
		terserOptions: {
			compress: {
				// Keep console logs in development for realtime debugging; drop in production
				drop_console: mode === 'production',
			},
		},
	},
	// Optimize dependencies
	optimizeDeps: {
		include: ['axios', 'chart.js'],
	},
	// Server configuration for development
	server: {
		hmr: {
			host: 'localhost',
		},
	},
}));
