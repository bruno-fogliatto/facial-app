<script setup>
    import { inject, ref, computed, onMounted } from 'vue';
    import Button from '@/Components/Button.vue';

    const steps = inject('steps');

    const isSuccess = ref(true);

    const resultClass = computed(() => ({
        'success': isSuccess.value,
        'error': isSuccess.value
    }));

    const resultIcon = computed(() => isSuccess.value ? '✅' : '❌');

    const resultTitle = computed(() => isSuccess.value ? 'Procedimento realizado com sucesso!' : 'Falha ao realizar procedimento!');

    const resultMessage = computed(() => isSuccess.value ? 'Sua biometria facial foi cadastrada e validada com sucesso.' : 'Sua biometria facial não foi cadastrara e validada, tente novamente.');

    const buttonLabel = computed(() => isSuccess.value ? 'Finalizar' : 'Refazer');

    const buttonAction = computed(() => isSuccess.value ? 'primary' : 'danger');

    const handleAction = () => {
        if (isSuccess.value) {
            alert('REDIRECT');
        } else {
            steps.back();
        }
    };

    onMounted(() => {
        isSuccess.value = Math.random() > 0.5;
    });
</script>

<template>
  <div class="container">
    <div class="result-content" :class="resultClass">
      <div class="result-icon">
        {{ resultIcon }}
      </div>
      
      <h1 class="result-title">{{ resultTitle }}</h1>
      <p class="result-message">{{ resultMessage }}</p>
      
      <div class="action-section">
        <Button
          :label="buttonLabel"
          :action="buttonAction"
          @click="handleAction"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
  .container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
  }

  .result-content {
    text-align: center;
    padding: 3rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
  }

  .result-success {
    background: linear-gradient(135deg, #d4edda, #ffffff);
    border: 2px solid #28a745;
  }

  .result-error {
    background: linear-gradient(135deg, #f8d7da, #ffffff);
    border: 2px solid #dc3545;
  }

  .result-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
  }

  .result-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .result-success .result-title {
    color: #155724;
  }

  .result-error .result-title {
    color: #721c24;
  }

  .result-message {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
  }

  .result-success .result-message {
    color: #155724;
  }

  .result-error .result-message {
    color: #721c24;
  }

  .action-section {
    display: flex;
    justify-content: center;
  }

  @media (max-width: 768px) {
    .result-content {
      padding: 2rem 1rem;
    }
    
    .result-title {
      font-size: 1.5rem;
    }
    
    .result-icon {
      font-size: 3rem;
    }
  }
</style>