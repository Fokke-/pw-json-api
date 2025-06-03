<template>
  <Layout />
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
