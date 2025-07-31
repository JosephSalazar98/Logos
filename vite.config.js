import { defineConfig } from 'vite';
import leaf from '@leafphp/vite-plugin';

export default defineConfig({
  plugins: [
    leaf({
      input: ['css/app.css'],
      refresh: true,
    }),
  ],
});
