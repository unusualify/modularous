import { defineConfig } from "vitepress";


export const shared = defineConfig({
    title: "Modularous",
    srcDir: 'pages',
    outDir: '../build',
    cleanUrls: true,
    lastUpdated: true,
    vite: {
      server: {
          host: '0.0.0.0',
          port: parseInt(process.env.DOCS_PORT || '8080', 10),
          strictPort: false,
          headers: { 'Access-Control-Allow-Origin': '*' },
          watch: {
            usePolling: true
          },

      }
    },

})
