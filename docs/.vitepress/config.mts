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
        text: 'The first steps',
        items: [
          { text: 'Overview', link: '/overview' },
          { text: 'Getting started', link: '/getting-started' },
        ],
      },
      {
        text: 'Digging deeper',
        items: [
          { text: 'API instance', link: '/api-instance' },
          { text: 'Services', link: '/services' },
          { text: 'Endpoints', link: '/endpoints' },
          { text: 'Responses', link: '/responses' },
          { text: 'Exceptions', link: '/exceptions' },
          { text: 'Request hooks', link: '/request-hooks' },
        ],
      },
      // {
      //   text: 'With the tools in hand',
      //   items: [{ text: 'Page parser', link: '/page-parser' }],
      // },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/Fokke-/pw-json-api' },
    ],
  },
});
