import { withPwa } from '@vite-pwa/vitepress';
import { defineConfig } from 'vitepress';
import { getSidebarSections } from './pages.mjs';

// https://vitepress.dev/reference/site-config
export default withPwa(
  defineConfig({
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
    transformHtml(code) {
      if (!code.includes('<main') && code.includes('id="VPContent"')) {
        return code.replace('id="VPContent"', 'id="VPContent" role="main"');
      }
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
      sidebar: getSidebarSections(),
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
    vite: {
      css: {
        preprocessorOptions: {
          scss: {
            api: 'modern-compiler',
          },
        },
      },
    },
    pwa: {
      registerType: 'prompt',
      manifest: false,
      workbox: {
        globPatterns: ['**/*.{js,css,html,woff2}'],
        cleanupOutdatedCaches: true,
        navigateFallbackDenylist: [/\.xml$/, /\.txt$/],
      },
    },
  }),
);
