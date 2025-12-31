<script setup>
  import { computed } from 'vue';

  const props = defineProps({
    label: {
      type: String,
      required: null
    },
    action: {
      type: String,
      default: 'primary'
    },
    icon: {
      type: String,
      default: null
    },
    image: {
      type: String,
      default: null
    },
    disabled: {
      type: Boolean,
      default: false
    },
    option: {
      type: String,
      default: 'button'
    },
    visible: {
      type: Boolean,
      default: true
    }
  });

  const emits = defineEmits(['click']);

  const buttonClass = computed(() => {
    return {
      'btn-primary': props.action === 'primary',
      'btn-secondary': props.action === 'secondary',
      'btn-danger': props.action === 'danger'
    };
  });

  function handleClick() {
    if (!props.disabled) {
      emits('click');
    }
  };
</script>

<template>
  <button
    v-if="visible"
    :type="option"
    class="custom-button" 
    :class="buttonClass"
    @click="handleClick"
    :disabled="disabled"
  >
    <span v-if="icon" class="button-icon">{{ icon }}</span>
    <span v-if="image" class="button-icon">
      <img :src="image" alt="icon" class="icon-image" />
    </span>
    {{ label }}
  </button>
</template>

<style scoped>
  .custom-button {
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    min-width: 120px;
  }

  .custom-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .btn-primary {
    background: linear-gradient(135deg, #0b72ae, #045781);
    color: white;
  }

  .btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #0b72ae, #045781);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  }

  .btn-secondary {
    background: #6c757d;
    color: white;
  }

  .btn-secondary:hover:not(:disabled) {
    background: #545b62;
    transform: translateY(-1px);
  }

  .btn-danger {
    background: #dc3545;
    color: white;
  }

  .btn-danger:hover:not(:disabled) {
    background: #c82333;
    transform: translateY(-1px);
  }

  .button-icon {
    font-size: 1.1rem;
  }

  @media (max-width: 768px) {
    .custom-button {
      width: 100%;
      padding: 1rem;
    }
  }
</style>
