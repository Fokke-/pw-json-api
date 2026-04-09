export const sections = [
  {
    text: 'Introduction',
    items: [
      { text: 'Overview', link: '/overview' },
      { text: 'Getting started', link: '/getting-started' },
      { text: 'Application lifecycle', link: '/lifecycle', llmsOnly: true },
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
      {
        text: 'ProcessWire Page Parser',
        link: '/processwire-page-parser',
      },
    ],
  },
  {
    text: 'Recipes',
    items: [{ text: 'OpenAPI documentation', link: '/recipes/openapi' }],
  },
];

export function getSidebarSections() {
  return sections.map((section) => ({
    text: section.text,
    items: section.items.filter((item) => !item.llmsOnly),
  }));
}
