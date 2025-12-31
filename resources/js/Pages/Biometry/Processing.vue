<script setup>
    import { inject, ref, onMounted } from 'vue';
    import ProcessingStatus from '@/Components/ProcessingStatus.vue';

    const steps = inject('steps');

    const processingSteps = ref([
        { text: 'Verificação da qualidade da imagem', status: 'pending'},
        { text: 'Processamento dos dados', status: 'pending'},
        { text: 'Verificação de segurança', status: 'pending'}
    ]);

    const rekognition = () => {
        
    };

    const simulateProcessing = () => {
        setTimeout(() => {
            processingSteps.value[0].status = 'processing'
        }, 500);

        setTimeout(() => {
            processingSteps.value[0].status = 'completed'
            processingSteps.value[1].status = 'processing'
        }, 2000);

        setTimeout(() => {
            processingSteps.value[1].status = 'completed'
            processingSteps.value[2].status = 'processing'
        }, 3500);

        setTimeout(() => {
            processingSteps.value[2].status = 'completed'
        }, 5000)

        setTimeout(() => {
            steps.next();
        }, 6000);
    };

    onMounted(() => {
        simulateProcessing();
    });

</script>

<template>
    <div class="container">
        <div class="header">
            <h1 class="title">Processando sua foto, aguarde...</h1>
            <p class="subtitle">Estamos analisando sua biometria facial!</p>
        </div>

        <ProcessingStatus :steps="processingSteps" />
    </div>
</template>

<style scoped>
    .container {
        display: flex;
        flex-direction: column;
        gap: 3rem;
        max-width: 600px;
        margin: 0 auto;
        text-align: center;
    }

    .header {
        margin-bottom: 1rem;
    }

    .title {
        font-size: 2.2rem;
        color: #333;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .subtitle {
        font-size: 1.1rem;
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .title {
            font-size: 1.8rem;
        }
    }
</style>