<template>
  <Layout>
    <template #sidebar-nav-after>
      <iframe
        src="https://github.com/sponsors/Fokke-/button"
        title="Sponsor Fokke-"
        height="32"
        width="114"
        style="border: 0; border-radius: 6px"
      ></iframe>
    </template>
  </Layout>
</template>

<script setup lang="ts">
import DefaultTheme from 'vitepress/theme';

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
