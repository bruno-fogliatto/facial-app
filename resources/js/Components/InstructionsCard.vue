<script setup>
import { computed } from 'vue';

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  status: {
    type: String,
    default: null
  }
});

const statusClass = computed(() => {
  if (!props.status) return '';

  const statusLower = props.status.toLowerCase();
  const keyWords = ['excelente', 'perfeita', 'nítida'];

  const hasSuccessWord = keyWords.some(word =>
    statusLower.includes(word)
  );

  return hasSuccessWord ? 'status-success' : '';
});
</script>

<template>
  <div class="instruction-card" :class="statusClass">
    <div class="card-icon">
      {{ icon }}
    </div>
    <div class="card-content">
      <h3 class="card-title">{{ title }}</h3>
      <p class="card-description">{{ description }}</p>
    </div>
    <div v-if="status" class="status-indicator">
      <span class="status-text">{{ status }}</span>
    </div>
  </div>
</template>

<style scoped>
  .instruction-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 2px solid transparent;
  }

  .instruction-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  }

  .instruction-card.status-success {
    border-color: #28a745;
    background: linear-gradient(135deg, #f8fff9, #ffffff);
  }

  .card-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
  }

  .card-content {
    flex: 1;
  }

  .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
  }

  .card-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.4;
  }

  .status-indicator {
    flex-shrink: 0;
  }

  .status-text {
    background: #28a745;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  @media (max-width: 768px) {
    .instruction-card {
      flex-direction: column;
      text-align: center;
      padding: 1rem;
    }
    
    .card-icon {
      font-size: 2rem;
    }
  }
</style>
