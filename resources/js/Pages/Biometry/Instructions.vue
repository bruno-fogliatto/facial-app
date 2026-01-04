<script setup>
    import { inject } from 'vue'; 
    import InstructionsCard from '@/Components/InstructionsCard.vue';
    import Button from '@/Components/Button.vue';

    const steps = inject('steps');
</script>

<template>
    <div class="main">
        <div class="container">
            <div class="header">
                <h1 class="title">Biometria Facial</h1>
                <p class="subtitle">Siga as intruções abaixo para uma captura perfeita.</p>
            </div>
        </div>

        <div class="instructions-grid">
            <InstructionsCard 
                icon="💡"
                title="Iluminação Adequada"
                description="Certifique-se de estar em um ambiente bem iluminado, evitando sombras no rosto"
            />
            <InstructionsCard 
                icon="😐"
                title="Expressão Neutra"
                description="Mantenha uma expressão facial neutra, sem sorrir ou fazer caretas"
            />
            <InstructionsCard 
                icon="👓"
                title="Sem Acessórios"
                description="Remova óculos, chapéus ou qualquer acessório que possa cobrir o rosto"
            />
            <InstructionsCard 
                icon="📱"
                title="Posicionamento"
                description="Posicione o dispositivo na altura dos olhos e mantenha-se centralizado"
            />
        </div>

        <div class="section">
            <Button 
                label="Iniciar Captura"
                action="primary"
                icon="▶️"
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

    .instructions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .section {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .title {
            font-size: 2rem;
        }
        
        .instructions-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>