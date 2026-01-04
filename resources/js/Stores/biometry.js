import { defineStore } from 'pinia';
import { router } from '@inertiajs/vue3';

export const useBiometryStore = defineStore('biometry', {
    state: () => ({
        currentStep: 1,
        locked: false,
        uuid: null,
        capturedImage: null,
        showError: false,
        validationError: null,
        isValidating: false
    }),

    getters: {
        canGoBack: (state) => {
            return (targetStep) => {
                // Se locked, não pode voltar para steps <= 3
                if (state.locked && state.currentStep > 3 && targetStep <= 3) {
                    return false;
                }
                return targetStep < state.currentStep && targetStep >= 1;
            };
        },

        canGoForward: (state) => {
            return (targetStep) => {
                return targetStep > state.currentStep && targetStep <= 5;
            };
        },
    },

    actions: {
        /**
         * Inicializa a store com dados do servidor
         */
        initialize(serverData) {
            if (serverData) {
                this.currentStep = serverData.serverStep || 1;
                this.locked = serverData.lockedAfterCapture || false;
                
                // Extrai UUID da URL atual
                const urlParts = window.location.pathname.split('/');
                this.uuid = urlParts[urlParts.length - 1];
            }
        },

        /**
         * Navega para um step específico
         */
        async goToStep(step) {
            if (step < 1 || step > 5) return false;
            if (step === this.currentStep) return false;

            // Validações
            if (step < this.currentStep && !this.canGoBack(step)) {
                console.warn('Não é possível voltar após capturar a imagem');
                return false;
            }

            if (step > this.currentStep && !this.canGoForward(step)) {
                console.warn('Não é possível pular steps');
                return false;
            }

            // Navega usando Inertia com query param
            return new Promise((resolve) => {
                router.get(
                    `/biometry/${this.uuid}`,
                    { step },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        only: ['serverStep', 'lockedAfterCapture'],
                        onSuccess: (page) => {
                            this.currentStep = step;
                            // Atualiza locked se vier do servidor
                            if (page.props.lockedAfterCapture !== undefined) {
                                this.locked = page.props.lockedAfterCapture;
                            }
                            resolve(true);
                        },
                        onError: (errors) => {
                            console.error('Erro ao mudar step:', errors);
                            resolve(false);
                        },
                    }
                );
            });
        },

        /**
         * Avança para o próximo step
         */
        async next() {
            return this.goToStep(this.currentStep + 1);
        },

        /**
         * Volta para o step anterior
         */
        async back() {
            return this.goToStep(this.currentStep - 1);
        },

        /**
         * Marca como locked após captura (step 3)
         */
        async lockAfterCapture() {
            this.locked = true;
        },

        /**
         * Salva imagem capturada
         */
        setCapturedImage(imageData) {
            this.capturedImage = imageData;
        },

        /**
         * Reseta o fluxo
         */
        reset() {
            this.$reset();
        },

        /**
         * Define erro de validação
         */
        setValidationError(error) {
            this.validationError = error;
            this.showError = true;
        },

        /**
         * Limpa erro de validação
         */
        clearValidationError() {
            this.validationError = null;
            this.showError = false;
        },

        /**
         * Define estado de validação (loading)
         */
        setValidating(value) {
            this.isValidating = value;
        }
    },

    // Persiste apenas dados essenciais (não a imagem)
    persist: {
        storage: sessionStorage,
        paths: ['currentStep', 'locked', 'uuid'],
    },
});