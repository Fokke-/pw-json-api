import { defineConfig } from 'vitepress';

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'ProcessWire JSON API',
  description: '',
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Guide', link: '/overview' },
    ],

    sidebar: [
      {
        text: 'Overview',
        items: [
          { text: 'Overview', link: '/overview' },
          { text: 'Getting started', link: '/getting-started' },
        ],
      },
      {
        text: 'Core concepts',
        items: [
          { text: 'API instance', link: '/api-instance' },
          { text: 'Services and endpoints', link: '/services-and-endpoints' },
          { text: 'Hooks', link: '/hooks' },
        ],
      },
      {
        text: 'Tools',
        items: [{ text: 'Page parser', link: '/page-parser' }],
      },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/Fokke-/pw-json-api' },
    ],
  },
});
