<script setup>
import { computed } from 'vue';

const props = defineProps({ 
  currentStep: {
    type: Number,
    required: true
  }, 
  totalSteps: {
    type: Number,
    required: true    
  }
});

const progressPercentage = computed(() => {
  return (props.currentStep / props.totalSteps) * 100;
});

</script>

<template>
  <div class="progress-container">
    <div class="progress-bar">
      <div 
        class="progress-fill" 
        :style="{ width: progressPercentage + '%' }"
      ></div>
    </div>
  </div>
</template>

<style scoped>
.progress-container {
  width: 200px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background-color: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #007bff, #0056b3);
  border-radius: 4px;
  transition: width 0.3s ease;
}

@media (max-width: 768px) {
  .progress-container {
    width: 150px;
  }
}
</style>
