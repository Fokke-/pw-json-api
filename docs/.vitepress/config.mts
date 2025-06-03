import { defineConfig } from 'vitepress';

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: 'ProcessWire JSON API',
  description: '',
  base: '/pw-json-api/',
  lastUpdated: true,
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    search: {
      provider: 'local',
    },
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
      {
        icon: 'packagist',
        link: 'https://packagist.org/packages/fokke/pw-json-api',
      },
    ],
    outline: {
      level: [2, 3],
    },
    editLink: {
      pattern: 'https://github.com/Fokke-/pw-json-api/edit/master/docs/:path',
    },
  },
});
