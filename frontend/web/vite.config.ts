import path from "path";
import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [react()],
  
  // Build настройки
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    chunkSizeWarningLimit: 5000,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom', 'react-router'],
        }
      }
    }
  },
  
  // Dev server настройки
  server: {
    allowedHosts: true,
    port: 5173,
    // Proxy для dev режима (опционально)
    proxy: {
      '/api': {
        target: 'http://cityquest.test',
        changeOrigin: true,
        secure: false,
      }
    }
  },
  
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
});
