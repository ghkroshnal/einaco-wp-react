import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig(({ command }) => {
  if (command === 'build') {
    return {
      base: "/wp-headless/server/wp-content/reactpress/apps/contacts/dist/",
      plugins: [react()],
    }
  } else {
    return {
      plugins: [react()],
    }
  }
})
