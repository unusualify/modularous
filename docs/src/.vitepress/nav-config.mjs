import { defineConfig } from "vitepress";

export const navConfig = defineConfig({
    nav: [
        { text: 'Home', link: '/' },
        { text: 'Get Started', link: 'get-started/what-is-modularity' },
        { text: 'Custom Auth Pages', link: 'guide/custom-auth-pages' },
        {
          text : 'Version' ,
          items : [
              {text: '1.0.0', link: ''}
          ]}
      ],
})
