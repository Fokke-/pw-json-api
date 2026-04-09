import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { sections } from '../docs/.vitepress/pages.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const docsDir = join(rootDir, 'docs');
const baseUrl = 'https://pwjsonapi.fokke.fi';

const allPages = sections.flatMap((s) =>
  s.items.map((item) => item.link.slice(1)),
);

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

/**
 * Convert a heading text to a markdown anchor slug.
 */
function slugify(text) {
  return text
    .replace(/<[^>]+>/g, '')
    .trim()
    .toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .replace(/\s+/g, '-');
}

/**
 * Convert relative markdown links to internal anchor links.
 * Builds a slug→anchor map from page titles, then replaces links:
 * - [text](/path) → [text](#page-heading-anchor)
 * - [text](/path#section) → [text](#section)
 */
function convertToAnchorLinks(content, pages) {
  const slugToAnchor = new Map();
  for (const [slug, page] of pages) {
    slugToAnchor.set(slug, slugify(page.title));
  }

  return content.replace(
    /\]\((?!https?:\/\/|#|mailto:)(\.?\/?)([\w/.@-]+?)(\.html)?(#[\w-]*)?\)/g,
    (_match, _prefix, path, _ext, anchor) => {
      if (anchor) {
        return `](${anchor})`;
      }
      const pageAnchor = slugToAnchor.get(path);
      return pageAnchor ? `](#${pageAnchor})` : `](${baseUrl}/${path})`;
    },
  );
}

// Build llms.txt
function buildLlmsTxt(pages) {
  const lines = [header, ''];

  for (const section of sections) {
    lines.push(`## ${section.text}`, '');
    for (const item of section.items) {
      const slug = item.link.slice(1);
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
    for (const item of section.items) {
      const slug = item.link.slice(1);
      const page = pages.get(slug);
      parts.push(convertToAnchorLinks(page.content, pages), '\n---\n');
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

/**
 * Validate that every anchor link in llms-full.txt points to an
 * existing heading.
 */
function validateAnchorLinks(content) {
  const headings = new Set();
  for (const [, heading] of content.matchAll(/^#{1,6}\s+(.+)$/gm)) {
    headings.add(slugify(heading));
  }

  let ok = true;
  for (const [, anchor] of content.matchAll(/\]\(#([\w-]+)\)/g)) {
    if (!headings.has(anchor)) {
      console.warn(`Warning: broken anchor link #${anchor} in llms-full.txt`);
      ok = false;
    }
  }
  return ok;
}

writeFileSync(join(rootDir, 'llms.txt'), llmsTxt);
writeFileSync(join(rootDir, 'llms-full.txt'), llmsFullTxt);

const valid = validateAnchorLinks(llmsFullTxt);

console.log(
  `Generated llms.txt (${llmsTxt.length} bytes) and llms-full.txt (${llmsFullTxt.length} bytes)`,
);

if (!valid) {
  process.exitCode = 1;
}
