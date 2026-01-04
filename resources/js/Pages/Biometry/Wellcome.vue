<script setup>
    import { inject, ref, onBeforeMount } from 'vue';
    import Button from '@/Components/Button.vue';

    const props = defineProps({
        icon: {
            type: String,
            default: null
        }
    });

    const imageSrc = ref(props.icon);

    const steps = inject('steps');

    onBeforeMount(() => {
        const images = JSON.parse(localStorage.getItem('images'));
        if (images?.wellcome) {
            imageSrc.value = images.wellcome;
        }
    });
</script>

<template>
    <div class="main">
        <div class="container">
            <div class="image">
                <img :src="imageSrc" alt="wellcome" />
            </div>

            <div class="header">
                <h1 class="title">Bem-vindo.</h1>
                <br>
                <ul class="subtitle access-features">
                    <li>
                        <span class="icone">📸</span>
                        <span>Captura da sua biometria facial.</span>
                    </li>
                    <li>
                        <span class="icone">⚡</span>
                        <span>Procedimento rápido.</span>
                    </li>
                    <li>
                        <span class="icone">🔒</span>
                        <span>Processo seguro.</span>
                    </li>
                    <li>
                        <span class="icone">🛡️</span>
                        <span>Controle de acesso e proteção de todos.</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="section">
            <Button 
                label="Continuar"
                action="primary"
                icon="→"
                @click="steps.next()"
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

    .image {
        display: flex;
        justify-content: center;
    }

    .image img {
        width: 50%;          /* ocupa toda a largura do container */
        max-width: 500px;     /* limita o tamanho máximo */
        height: auto;         /* mantém proporção da imagem */
        border-radius: 100%;  /* bordas arredondadas opcionais */
        object-fit: cover;    /* cobre o container mantendo proporção */
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); /* sombra suave */
    }

    /* Para telas pequenas */
    @media (max-width: 768px) {
        .image img {
            max-width: 90%;   /* reduz o tamanho em mobile */
        }
    }

    .header {
        text-align: center;
        align-items: center;
        justify-items: center;
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

    .section {
        display: flex;
        justify-content: center;
    }

    .access-features {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .access-features li {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        font-size: 1rem;
    }

    .icone {
        font-size: 1.4rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
