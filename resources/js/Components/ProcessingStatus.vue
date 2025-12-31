<script setup>
  const props = defineProps({
    steps: {
      type: Array,
      required: true,
      default: () => []
    }
  });

  function getStepClass(status) {
    return {
      'step-completed': status === 'completed',
      'step-processing': status === 'processing',
      'step-error': status === 'error',
      'step-pending': status === 'pending'
    } 
  };
</script>

<template>
  <div class="processing-status">
    <div class="processing-steps">
      <div 
        v-for="(step, index) in steps" 
        :key="index"
        class="processing-step"
        :class="getStepClass(step.status)"
      >
        <div class="step-icon">
          <span v-if="step.status === 'completed'">✓</span>
          <span v-else-if="step.status === 'error'">✗</span>
          <div v-else class="loading-spinner"></div>
        </div>
        <span class="step-text">{{ step.text }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
  .processing-status {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
  }

  .processing-steps {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .processing-step {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
  }

  .step-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
  }

  .step-text {
    font-size: 1rem;
    font-weight: 500;
  }

  .step-completed {
    border-left: 4px solid #28a745;
  }

  .step-completed .step-icon {
    background: #28a745;
    color: white;
  }

  .step-completed .step-text {
    color: #28a745;
  }

  .step-processing {
    border-left: 4px solid #ffc107;
  }

  .step-processing .step-icon {
    background: #ffc107;
  }

  .step-processing .step-text {
    color: #856404;
  }

  .step-error {
    border-left: 4px solid #dc3545;
  }

  .step-error .step-icon {
    background: #dc3545;
    color: white;
  }

  .step-error .step-text {
    color: #dc3545;
  }

  .step-pending {
    border-left: 4px solid #e9ecef;
  }

  .step-pending .step-icon {
    background: #e9ecef;
    color: #6c757d;
  }

  .step-pending .step-text {
    color: #6c757d;
  }

  .loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #333;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  @media (max-width: 768px) {
    .processing-step {
      padding: 0.75rem;
    }
    
    .step-icon {
      width: 28px;
      height: 28px;
      font-size: 1rem;
    }
    
    .step-text {
      font-size: 0.9rem;
    }
  }
</style>
