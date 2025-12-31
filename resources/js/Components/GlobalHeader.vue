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
    console.log(images);
    if (images?.logo2) {
      imageSrc.value = images.logo2;
    }
  });

  // function goBack() {
  //   if (!steps) return;
  //   steps.back();
  // }
</script>

<template>
  <header class="global-header">
    <div class="header-content">
      <!-- <button 
        v-if="currentStep > 1" 
        class="back-button" 
        @click="goBack()"
      >
        ← Voltar
      </button> -->
      
      <div class="logo-section">
        <div class="logo">
          <div class="shield-icon">
           <img class="logo-image" :src="imageSrc" alt="logo"/>
          </div>
          <span class="logo-text"> Marina 
            <span class="logo-subtext">Blue Fox</span>
          </span>
        </div>
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
    overflow: hidden;
  }

  .header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
  }

  .back-button {
    background: none;
    border: none;
    color: #045781;
    font-size: 1rem;
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: background-color 0.2s;
  }

  .back-button:hover {
    background-color: #f8f9fa;
  }

  .logo-section {
    flex: 1;
    display: flex;
    justify-content: center;
  }

  .logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .shield-icon {
    font-size: 2rem;
    color: #045781;
  }

  .logo-image {
    width: 64px;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
    vertical-align: center;
  }

  .logo-text {
    font-size: 1.5rem;
    font-weight: bold;
    color: #045781;
  }

  .logo-subtext {
    color: #0b72ae;
  }

  .step-info {
    flex: 1;
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
  }

  .step-title {
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 0.5rem;
  }

  @media (max-width: 768px) {
    .global-header {
      padding: 1rem;
    }
    
    .header-content {
      flex-direction: column;
      gap: 1rem;
    }
    
    .logo-section,
    .step-info {
      flex: none;
    }
    
    .step-info {
      text-align: center;
      width: 100%;
    }
  }

  @media (max-width: 480px) {

    .global-header {
      padding: 0.5rem 0.75rem;
    }

    .header-content {
      flex-direction: column;
      align-items: stretch;
      gap: 0.4rem;
    }

    /* Botão voltar menor */
    .back-button {
      font-size: 0.85rem;
      padding: 0.25rem 0.5rem;
    }

    .logo-section {
      justify-content: center;
    }

    /* Logo menor */
    .logo {
      gap: 0.35rem;
    }

    .logo-image {
      width: 36px;
      border-radius: 6px;
    }

    .logo-text {
      font-size: 1rem;
      line-height: 1.1;
    }

    .logo-subtext {
      font-size: 0.95rem;
    }

    /* Step info mais compacto */
    .step-info {
      gap: 0.2rem;
      align-items: flex-end;
    }

    .step-title {
      font-size: 0.8rem;
      margin-bottom: 0.2rem;
    }
  }

</style>
