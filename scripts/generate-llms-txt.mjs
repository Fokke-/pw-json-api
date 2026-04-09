import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const docsDir = join(__dirname, '..', 'docs');
const publicDir = join(docsDir, 'public');
const baseUrl = 'https://pwjsonapi.fokke.fi';

const sections = [
  {
    title: 'Introduction',
    pages: ['overview', 'getting-started', 'lifecycle'],
  },
  {
    title: 'Core concepts',
    pages: [
      'api-instance',
      'services',
      'endpoints',
      'requests',
      'responses',
      'error-handling',
    ],
  },
  {
    title: 'Hooks',
    pages: ['request-hooks', 'error-hooks'],
  },
  {
    title: 'Plugins',
    pages: ['plugins/plugins-overview', 'plugins/csrf', 'plugins/rate-limit'],
  },
  {
    title: 'Tools',
    pages: ['processwire-page-parser'],
  },
  {
    title: 'Recipes',
    pages: ['recipes/openapi'],
  },
];

const allPages = sections.flatMap((s) => s.pages);

const header = `# ProcessWire JSON API

> Build structured JSON APIs for ProcessWire without boilerplate.
> Convention-based, extensible, and MIT licensed.
> Requires PHP 8.2+ and ProcessWire 3.0.173+.
> Install: composer require fokke/pw-json-api`;

/**
 * Parse frontmatter description and content from a markdown file.
 */
function parsePage(slug) {
  const filePath = join(docsDir, `${slug}.md`);
  const raw = readFileSync(filePath, 'utf-8');

  const frontmatterMatch = raw.match(/^---\n([\s\S]*?)\n---\n/);
  let description = '';
  let content = raw;

  if (frontmatterMatch) {
    const yaml = frontmatterMatch[1];
    const descMatch = yaml.match(/description:\s*['"]?(.*?)['"]?\s*$/m);
    if (descMatch) {
      description = descMatch[1];
    }
    content = raw.slice(frontmatterMatch[0].length);
  }

  // Convert relative markdown links to absolute URLs
  // Matches [text](/path) and [text](./path) but not [text](http...) or [text](#anchor)
  content = content.replace(
    /\]\((?!https?:\/\/|#|mailto:)(\.?\/?)([\w/.@-]+?)(\.html)?(#[\w-]*)?\)/g,
    (match, prefix, path, _ext, anchor) => {
      return `](${baseUrl}/${path}${anchor || ''})`;
    },
  );

  // Get the first heading as the title
  const titleMatch = content.match(/^#\s+(.+)$/m);
  const title = titleMatch
    ? titleMatch[1].replace(/<[^>]+>/g, '').trim()
    : slug;

  return { slug, description, content: content.trim(), title };
}

/**
 * Warn about docs that are not included in the page list.
 */
function warnUnlisted() {
  const findMdFiles = (dir, prefix = '') => {
    const results = [];
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
      if (entry.isDirectory()) {
        results.push(
          ...findMdFiles(join(dir, entry.name), `${prefix}${entry.name}/`),
        );
      } else if (entry.name.endsWith('.md')) {
        results.push(`${prefix}${entry.name.replace(/\.md$/, '')}`);
      }
    }
    return results;
  };

  const allMdFiles = findMdFiles(docsDir).filter(
    (f) =>
      f !== 'index' && !f.startsWith('.vitepress/') && !f.startsWith('public/'),
  );

  for (const file of allMdFiles) {
    if (!allPages.includes(file)) {
      console.warn(`Warning: docs/${file}.md is not included in llms.txt`);
    }
  }
}

// Build llms.txt
function buildLlmsTxt(pages) {
  const lines = [header, ''];

  for (const section of sections) {
    lines.push(`## ${section.title}`, '');
    for (const slug of section.pages) {
      const page = pages.get(slug);
      const url = `${baseUrl}/${slug}`;
      lines.push(`- [${page.title}](${url}): ${page.description}`);
    }
    lines.push('');
  }

  return lines.join('\n').trimEnd() + '\n';
}

// Build llms-full.txt
function buildLlmsFullTxt(pages) {
  const parts = [header, ''];

  for (const section of sections) {
    for (const slug of section.pages) {
      const page = pages.get(slug);
      parts.push(page.content, '\n---\n');
    }
  }

  // Remove trailing separator
  parts.pop();

  return parts.join('\n').trimEnd() + '\n';
}

// Main
warnUnlisted();

const pages = new Map();
for (const slug of allPages) {
  pages.set(slug, parsePage(slug));
}

const llmsTxt = buildLlmsTxt(pages);
const llmsFullTxt = buildLlmsFullTxt(pages);

writeFileSync(join(publicDir, 'llms.txt'), llmsTxt);
writeFileSync(join(publicDir, 'llms-full.txt'), llmsFullTxt);

console.log(
  `Generated llms.txt (${llmsTxt.length} bytes) and llms-full.txt (${llmsFullTxt.length} bytes)`,
);
