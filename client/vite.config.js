import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],
  server: {
    proxy: {
      '/server': {
        target: 'http://localhost', // Change this if your PHP server runs elsewhere
        changeOrigin: true,
      }
    }
  }
})
