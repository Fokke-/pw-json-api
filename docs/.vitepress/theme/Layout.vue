<template>
  <Layout>
    <template #layout-bottom>
      <ReloadPrompt />
    </template>
    <template #sidebar-nav-after>
      <a
        href="https://deepwiki.com/Fokke-/pw-json-api"
        target="_blank"
        rel="noopener noreferrer"
      >
        <img
          src="https://deepwiki.com/badge.svg"
          alt="Ask DeepWiki"
          loading="lazy"
          style="margin-bottom: 1.1rem"
        />
      </a>
      <iframe
        src="https://github.com/sponsors/Fokke-/button"
        title="Sponsor Fokke-"
        height="32"
        width="114"
        loading="lazy"
        style="border: 0; border-radius: 6px"
      ></iframe>
    </template>
  </Layout>
</template>

<script setup lang="ts">
import DefaultTheme from 'vitepress/theme';
import ReloadPrompt from './components/ReloadPrompt.vue';

const { Layout } = DefaultTheme;
</script>

<style lang="scss">
.VPHomeHero {
  .main {
    text-align: center;
  }

  .name,
  .tagline {
    width: 100%;
    max-width: unset !important;
  }

  .actions {
    justify-content: center;
  }
}
</style>
