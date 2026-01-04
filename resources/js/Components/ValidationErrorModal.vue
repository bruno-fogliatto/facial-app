<script setup>
    import { ref, watch, onBeforeMount, onMounted } from 'vue';
    import Button from '@/Components/Button.vue';
    
    const props = defineProps({
        show: {
            type: Boolean,
            required: true
        },
        errorMessage: {
            type: String,
            default: 'Ocorreu um erro ao processar a foto.'
        }
    });

    const emits = defineEmits(['close', 'retry']);

    const isVisible = ref(false);
    
    watch(() => props.show, (newValue) => {
        if (newValue) {
            setTimeout(() => {
                isVisible.value = true;
            }, 10);

            document.body.style.overflow = 'hidden';
        } else {
            isVisible.value = false;
            document.body.style.overflow = '';
        } 
    });

    function handleClose() {
        isVisible.value = false;
        document.body.style.overflow = '';
        setTimeout(() => {
            emits('close');
        }, 300);
    };

    function handleRetry() {
        isVisible.value = false;
        document.body.style.overflow = '';
        setTimeout(() => {
            emits('retry');
        }, 300);
    };

    function handleKeydown(event) {
        if (event.key === 'Espace' && props.show) {
            handleClose();
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handleKeydown);
    });

    onBeforeMount(() => {
        window.removeEventListener('keydown', handleKeydown);
        document.body.style.overflow = '';
    });
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="modal-overlay" @click="handleClose">
                <div 
                    class="modal-container" 
                    :class="{ 'modal-enter': isVisible }"
                    @click.stop
                >
                    <!-- Ícone de Aviso -->
                    <div class="modal-icon">
                        <div class="icon-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                                <circle cx="12" cy="13" r="3"/>
                                <line x1="18" y1="6" x2="6" y2="18" stroke-width="3"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Conteúdo -->
                    <div class="modal-content">
                        <h2 class="modal-title">Foto não aprovada!</h2>
                        <p class="modal-message">{{ errorMessage }}</p>
                    </div>

                    <!-- Ações -->
                    <div class="modal-actions">
                        <Button 
                            label="Tentar Novamente"
                            icon="🔄"
                            @click="handleRetry"
                        />
                        <Button 
                            label="Fechar"
                            action="secondary"
                            @click="handleClose"
                        />
                    </div>

                    <!-- Botão X no canto -->
                    <button class="close-x" @click="handleClose">
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" 
                            viewBox="0 0 24 24" 
                            fill="none" 
                            stroke="currentColor" 
                            stroke-width="2"
                        >
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
    /* Overlay */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 1rem;
    }

    /* Container da Modal */
    .modal-container {
        background: white;
        border-radius: 16px;
        max-width: 480px;
        width: 100%;
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
        transform: scale(0.9);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .modal-container.modal-enter {
        transform: scale(1);
        opacity: 1;
    }

    /* Ícone */
    .modal-icon {
        display: flex;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .icon-circle {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        animation: pulse 2s infinite;
    }

    .icon-circle svg {
        width: 48px;
        height: 48px;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    /* Conteúdo */
    .modal-content {
        text-align: center;
        margin-bottom: 2rem;
    }

    .modal-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1rem;
    }

    .modal-message {
        font-size: 1.1rem;
        color: #4a5568;
        line-height: 1.6;
    }

    /* Ações */
    .modal-actions {
        display: flex;
        gap: 1rem;
        flex-direction: column;
    }

    .btn-retry {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-retry:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .btn-retry:active {
        transform: translateY(0);
    }

    .btn-close {
        background: transparent;
        color: #718096;
        border: 2px solid #e2e8f0;
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-close:hover {
        background: #f7fafc;
        border-color: #cbd5e0;
        color: #4a5568;
    }

    /* Botão X */
    .close-x {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 32px;
        height: 32px;
        border: none;
        background: #f7fafc;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #718096;
        transition: all 0.2s;
    }

    .close-x:hover {
        background: #edf2f7;
        color: #2d3748;
        transform: rotate(90deg);
    }

    .close-x svg {
        width: 20px;
        height: 20px;
    }

    /* Animações de Transição */
    .modal-enter-active,
    .modal-leave-active {
        transition: opacity 0.3s;
    }

    .modal-enter-from,
    .modal-leave-to {
        opacity: 0;
    }

    /* Responsivo */
    @media (max-width: 640px) {
        .modal-container {
            padding: 1.5rem;
            margin: 1rem;
        }

        .icon-circle {
            width: 64px;
            height: 64px;
        }

        .icon-circle svg {
            width: 36px;
            height: 36px;
        }

        .modal-title {
            font-size: 1.5rem;
        }

        .modal-message {
            font-size: 1rem;
        }

        .btn-retry {
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
        }

        .btn-close {
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
        }
    }

    /* Acessibilidade */
    @media (prefers-reduced-motion: reduce) {
        .modal-container,
        .icon-circle,
        .btn-retry,
        .btn-close,
        .close-x {
            transition: none;
            animation: none;
        }
    }
</style>