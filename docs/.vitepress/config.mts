import { withMermaid } from 'vitepress-plugin-mermaid';

// https://vitepress.dev/reference/site-config
export default withMermaid({
  title: 'ProcessWire JSON API',
  description: '',
  base: '/',
  lastUpdated: true,
  sitemap: {
    hostname: 'https://pwjsonapi.fokke.fi',
    lastmodDateOnly: false,
  },
  cleanUrls: true,
  transformPageData(pageData) {
    const path = pageData.relativePath
      .replace(/index\.md$/, '')
      .replace(/\.(md|html)$/, '');

    const canonicalUrl = [
      `https://pwjsonapi.fokke.fi`,
      path ? `/${path}` : undefined,
    ]
      .filter((item) => !!item)
      .join('');

    pageData.frontmatter.head ??= [];
    pageData.frontmatter.head.push([
      'link',
      { rel: 'canonical', href: canonicalUrl },
    ]);
  },
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
        text: 'Introduction',
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
          { text: 'Requests', link: '/requests' },
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
        text: 'Plugins',
        items: [
          { text: 'Overview', link: '/plugins/plugins-overview' },
          { text: 'CSRF protection', link: '/plugins/csrf' },
          { text: 'Rate limiting', link: '/plugins/rate-limit' },
        ],
      },
      {
        text: 'Tools',
        items: [
          { text: 'ProcessWire Page Parser', link: '/processwire-page-parser' },
        ],
      },
      {
        text: 'Recipes',
        items: [{ text: 'OpenAPI documentation', link: '/recipes/openapi' }],
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
