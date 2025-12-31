<script setup>
    import { inject } from 'vue'; 
    import FaceCapture from '@/Components/FaceCapture.vue';
    import serviceController from '@/Controllers/serviceController';

    const steps = inject('steps');

    async function handlePhotoCaptured(imageData) {
        const guest = localStorage.getItem('guest') ?? null;
        const validImage = await serviceController.post('/photo-analysis', { imageData: imageData });
        if (validImage) {
            await serviceController.post('/upload-image', { image: imageData, guest_id: guest });
        }

        steps.next();
    }

</script>

<template>
    <div class="container">
        <div class="header">
            <h1 class="title">Captura da Foto</h1>
            <p class="subtitle">Posicione seu rosto dentro da moldura e capture sua foto.</p>
        </div>

        <FaceCapture @photoCaptured="handlePhotoCaptured" />
    </div>
</template>

<style scoped>
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

    @media (max-width: 768px) {
    .title {
        font-size: 2rem;
    }
    }
</style>