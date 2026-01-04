<script setup>
  import { inject, ref, onBeforeMount } from 'vue'; 
  import ProgressBar from './ProgressBar.vue';

  const props = defineProps({ 
    currentStep: Number, 
    totalSteps: Number, 
    icon: {
      type: String,
      default: null
    }
  });

  const imageSrc = ref(props.icon);

  const steps = inject('steps');

  onBeforeMount(() => {
    const images = JSON.parse(localStorage.getItem('images'));

    if (images?.logo2) {
      imageSrc.value = images.logo2;
    }
  });
</script>

<template>
  <header class="global-header">
    <div class="header-content">
      <div class="logo">
        <div class="shield-icon">
          <img class="logo-image" :src="imageSrc" alt="logo"/>
        </div>
        <span class="logo-text"> Marina 
          <span class="logo-subtext">Blue Fox</span>
        </span>
      </div>
      
      <div class="step-info">
        <h1 class="step-title">Etapa {{ currentStep }} de {{ totalSteps }}</h1>
        <ProgressBar :currentStep="currentStep" :totalSteps="totalSteps" />
      </div>
    </div>
  </header>
</template>

<style scoped>
  .global-header {
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 1rem 2rem;
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
  }

  .logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .logo-image {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: 8px;
  }

  .logo-text {
    font-size: 1.3rem;
    font-weight: bold;
    color: #045781;
    white-space: nowrap;
  }

  .logo-subtext {
    color: #0b72ae;
  }

  .step-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
    min-width: 200px;
  }

  .step-title {
    font-size: 0.95rem;
    color: #333;
    font-weight: 600;
    white-space: nowrap;
  }

  @media (max-width: 768px) {
    .global-header {
      padding: 0.875rem 1rem;
    }

    .header-content {
      gap: 1rem;
    }

    .logo-image {
      width: 40px;
      height: 40px;
    }

    .logo-text {
      font-size: 1.1rem;
    }

    .step-info {
      min-width: 150px;
    }

    .step-title {
      font-size: 0.85rem;
    }
  }

  @media (max-width: 480px) {
    .global-header {
      padding: 0.75rem 0.875rem;
    }

    .header-content {
      gap: 0.75rem;
    }

    .logo-image {
      width: 36px;
      height: 36px;
    }

    .logo-text {
      font-size: 1rem;
    }

    .logo-subtext {
      font-size: 0.95rem;
    }

    .step-info {
      min-width: 130px;
      gap: 0.35rem;
    }

    .step-title {
      font-size: 0.8rem;
    }
  }
</style>
