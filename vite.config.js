import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/sword.css'
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            // jQuery is loaded synchronously from /js/vendor/jquery.min.js so inline
            // Blade scripts have immediate access to $. All other modules reference the
            // same global instance at runtime via the 'jQuery' global name.
            external: ['jquery'],
            output: {
                globals: {
                    jquery: 'jQuery',
                },
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'css/[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                }
            }
        }
    }
});
