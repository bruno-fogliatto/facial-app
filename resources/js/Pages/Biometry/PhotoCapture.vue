<script setup>
    import { inject, computed, ref } from 'vue'; 
    import { useBiometryStore } from '@/Stores/biometry';
    import FaceCapture from '@/Components/FaceCapture.vue';
    import ValidationErrorModal from '@/Components/ValidationErrorModal.vue';
    import serviceController from '@/Controllers/serviceController';

    const steps = inject('steps');
    const biometryStore = useBiometryStore();

    const faceCaptureRef = ref(null);
    const isValidating = computed(() => biometryStore.isValidating);
    const errorMessage = computed(() => biometryStore.validationError);
    const showErrorModal = computed(() => biometryStore.showError);

    async function handlePhotoCaptured(imageData) {
        try {
            // Limpa erro anterior
            biometryStore.clearValidationError();

            // Ativa o loading
            biometryStore.setValidating(true);

            const guest = localStorage.getItem('guest') ?? null;

            // Validação com AWS Rekognition
            const valdationResponse = await serviceController.post('photo-analysis', {
                image: imageData,
                guest_id: guest
            });
            
            // Verifica se a validação passou
            if (valdationResponse.data === true) {
                //Faz upload da foto no S3
                await serviceController.post('/upload-image', { image: imageData, guest_id: guest });

                // Salva no store
                biometryStore.setCapturedImage(imageData);

                // Lock após captura
                await biometryStore.lockAfterCapture();

                await steps.next();
            } else {
                const errorMessage = typeof valdationResponse.data === 'string' ? valdationResponse.data : 'Não foi possível valida a imagem. Tente novamente.';

                biometryStore.setValidationError(errorMessage);
            }
        } catch (error) {
            console.error('Erro ao processar foto: ',  error);

            const errorMessage = error.respose?.data?.message
                || error.message
                || 'Erro ao processar foto. Verifique sua conexão e tente novamente.';

            biometryStore.setValidationError(errorMessage);
        } finally {
            biometryStore.setValidating(false);
        }
    }

    function handleRetry() {
        // Reseta o compoenente FaceCapture para reativar o lieveness
        if (faceCaptureRef.value) {
            faceCaptureRef.value.resetCapture();
        }

        biometryStore.clearValidationError();
    }

    function handleCloseModal() {
        // Reseta o compoenente FaceCapture para reativar o lieveness
        if (faceCaptureRef.value) {
            faceCaptureRef.value.resetCapture();
        }

        biometryStore.clearValidationError();
    }

</script>

<template>
    <div class="main">
        <div class="container">
            <div class="header">
                <h1 class="title">Captura da Foto</h1>
                <p class="subtitle">Posicione seu rosto dentro da moldura e capture sua foto.</p>
            </div>

            <div v-if="isValidating" class="validation-overlay">
                <div class="validation-spinner"></div>
                <p class="validation-text">Validando qualidade da imagem...</p>
            </div>

            <FaceCapture 
                v-else
                ref="faceCaptureRef"
                @photoCaptured="handlePhotoCaptured"
            />

            <ValidationErrorModal
                :show="showErrorModal"
                :errorMessage="errorMessage"
                @retry="handleRetry"
                @close="handleCloseModal"
            />
        </div>
    </div>
</template>

<style scoped>
    .main {
        padding: 1rem;
    }
    
    .container {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .header {
        text-align: center;
        margin-bottom: 1rem;
    }

    .title {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        max-width: 600px;
        margin: 0 auto;
    }

    .error-alert {
        background: linear-gradient(135deg, #fff5f5, #ffffff);
        border: 2px solid #dc3545;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .error-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .error-content {
        flex: 1;
    }

    .error-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #721c24;
        margin-bottom: 0.5rem;
    }

    .error-message {
        color: #721c24;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .retry-button:hover {
        background: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    .validation-overlay {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .validation-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .validation-text {
        font-size: 1.1rem;
        color: #6c757d;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .title {
            font-size: 2rem;
        }

        .error-alert {
            flex-direction: column;
            text-align: center;
        }
        
        .error-icon {
            font-size: 3rem;
        }
    }
</style>