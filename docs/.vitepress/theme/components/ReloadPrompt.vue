<template>
  <div v-if="needRefresh" class="reload-prompt" role="alert">
    <span>New content available!</span>
    <div class="reload-prompt__buttons">
      <button class="reload-prompt-reload" @click="updateServiceWorker()">
        Reload
      </button>
      <button class="reload-prompt-dismiss" @click="needRefresh = false">
        Dismiss
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';

const needRefresh = ref(false);
let updateServiceWorker: () => Promise<void> = async () => {};

onMounted(async () => {
  const { registerSW } = await import('virtual:pwa-register');
  updateServiceWorker = registerSW({
    onNeedRefresh() {
      needRefresh.value = true;
    },
  });
});
</script>

<style scoped lang="scss">
.reload-prompt {
  position: fixed;
  left: 50%;
  bottom: 1.5rem;
  z-index: 100;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: 1px solid var(--vp-c-border);
  border-radius: 8px;
  background: var(--vp-c-bg-elv);
  box-shadow: var(--vp-shadow-3);
  font-size: 0.875rem;
  transform: translateX(-50%);

  @media (min-width: 600px) {
    align-items: center;
    flex-direction: row;
  }

  &__buttons {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 0.75rem;
  }

  button {
    cursor: pointer;
    border: none;
    border-radius: 4px;
    padding: 0.25rem 0.75rem;
    font-size: 0.8125rem;
    font-weight: 500;
  }

  &-reload {
    background: var(--vp-c-brand-1);
    color: var(--vp-c-white);
  }

  &-dismiss {
    background: var(--vp-c-default-soft);
    color: var(--vp-c-text-1);
  }
}
</style>
