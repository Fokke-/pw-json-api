import { defineConfig } from 'vitepress';
import { withMermaid } from 'vitepress-plugin-mermaid';

// https://vitepress.dev/reference/site-config
export default withMermaid({
  title: 'ProcessWire JSON API',
  description: '',
  base: '/pw-json-api/',
  head: [
    [
      'meta',
      {
        'http-equiv': 'Cache-Control',
        content: 'no-cache, no-store, must-revalidate',
      },
    ],
    [
      'meta',
      {
        'http-equiv': 'Pragma',
        content: 'no-cache',
      },
    ],
    [
      'meta',
      {
        'http-equiv': 'Expires',
        content: '0',
      },
    ],
  ],
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
        text: 'Core concepts',
        items: [
          { text: 'API instance', link: '/api-instance' },
          { text: 'Services', link: '/services' },
          { text: 'Endpoints', link: '/endpoints' },
          { text: 'Responses', link: '/responses' },
          { text: 'Error handling', link: '/error-handling' },
        ],
      },
      {
        text: 'Hooks',
        items: [
          { text: 'Request hooks', link: '/request-hooks' },
          { text: 'Error hooks', link: '/error-hooks' },
        ],
      },
      {
        text: 'Tools',
        items: [
          { text: 'ProcessWire Page Parser', link: '/processwire-page-parser' },
        ],
      },
    ],
    socialLinks: [
      { icon: 'github', link: 'https://github.com/Fokke-/pw-json-api' },
      {
        icon: 'packagist',
        link: 'https://packagist.org/packages/fokke/pw-json-api',
      },
      {
        icon: 'githubsponsors',
        link: 'https://github.com/sponsors/Fokke-',
      },
    ],
    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2025-present Ville Fokke Saarivaara',
    },
    outline: {
      level: [2, 3],
    },
    editLink: {
      pattern: 'https://github.com/Fokke-/pw-json-api/edit/master/docs/:path',
    },
  },
});
