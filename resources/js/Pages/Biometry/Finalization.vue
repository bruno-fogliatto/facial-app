<script setup>
import { ref, onMounted, computed } from 'vue';
import Button from '@/Components/Button.vue';

const showContent = ref(false);
const animateCheckmark = ref(false);
const showConfetti = ref(false);

const registrationDate = ref(new Date().toLocaleDateString('pt-BR', { 
    day: '2-digit', 
    month: 'long', 
    year: 'numeric' 
}));
const registrationTime = ref(new Date().toLocaleTimeString('pt-BR', { 
    hour: '2-digit', 
    minute: '2-digit' 
}));

const guest = localStorage.getItem('guest');

const handleFinish = () => {
    // Redireciona ou fecha o processo
    window.location.href = 'https://www.bluefoxmarina.com.br/';
};

onMounted(() => {
    setTimeout(() => {
        showContent.value = true;
    }, 100);

    setTimeout(() => {
        animateCheckmark.value = true;
    }, 400);

    setTimeout(() => {
        showConfetti.value = true;
    }, 600);

    // Remove confetti após animação
    setTimeout(() => {
        showConfetti.value = false;
    }, 3600);
});
</script>

<template>
    <div class="finalization-container">
        <!-- Confetti Animation -->
        <div v-if="showConfetti" class="confetti-container">
            <div v-for="i in 50" :key="i" class="confetti" :style="{
                left: `${Math.random() * 100}%`,
                animationDelay: `${Math.random() * 0.5}s`,
                backgroundColor: ['#48bb78', '#667eea', '#f56565', '#ecc94b', '#38b2ac'][Math.floor(Math.random() * 5)]
            }"></div>
        </div>

        <div class="content-wrapper" :class="{ 'show': showContent }">
            <!-- Success Icon -->
            <div class="success-icon-wrapper">
                <div class="success-circle" :class="{ 'animate': animateCheckmark }">
                    <svg class="checkmark" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
            </div>

            <!-- Main Title -->
            <h1 class="main-title">Cadastro Concluído!</h1>
            <p class="main-subtitle">
                Sua biometria facial foi registrada com sucesso
            </p>

            <!-- User Info Card -->
            <div class="info-card">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-icon">📅</span>
                        <div class="info-content">
                            <span class="info-label">Data de Registro</span>
                            <span class="info-value">{{ registrationDate }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="info-icon">🕐</span>
                        <div class="info-content">
                            <span class="info-label">Horário</span>
                            <span class="info-value">{{ registrationTime }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="info-icon">🔒</span>
                        <div class="info-content">
                            <span class="info-label">Segurança</span>
                            <span class="info-value">Criptografado</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="info-icon">✅</span>
                        <div class="info-content">
                            <span class="info-label">Validação</span>
                            <span class="info-value">Aprovado</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="benefits-section">
                <h3 class="benefits-title">O que você pode fazer agora:</h3>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <span class="benefit-icon">🚀</span>
                        <div class="benefit-text">
                            <strong>Acesso Rápido</strong>
                            <p>Use reconhecimento facial para acesso instantâneo.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <span class="benefit-icon">🔐</span>
                        <div class="benefit-text">
                            <strong>Segurança Avançada</strong>
                            <p>Seus dados biométricos estão protegidos.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <Button 
                    label="Concluir"
                    action="primary"
                    @click="handleFinish"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
    .finalization-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        /* padding: 1rem 1rem; */
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        position: relative;
        overflow: hidden;
    }

    /* Confetti Animation */
    .confetti-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        top: -10px;
        animation: confetti-fall 3s linear forwards;
    }

    @keyframes confetti-fall {
        to {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }

    /* Main Content */
    .content-wrapper {
        max-width: 600px;
        width: 100%;
        background: white;
        border-radius: 24px;
        padding: 3rem 2rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        opacity: 0;
        transform: scale(0.9);
        transition: all 0.5s ease-out;
        position: relative;
        z-index: 2;
    }

    .content-wrapper.show {
        opacity: 1;
        transform: scale(1);
    }

    /* Success Icon */
    .success-icon-wrapper {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }

    .success-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #48bb78, #38a169);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);
        transform: scale(0);
        transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .success-circle.animate {
        transform: scale(1);
    }

    .checkmark {
        width: 60px;
        height: 60px;
    }

    .checkmark-circle {
        stroke: rgba(255, 255, 255, 0.3);
        stroke-width: 2;
    }

    .checkmark-check {
        stroke: white;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: draw-check 0.6s 0.4s ease-out forwards;
    }

    @keyframes draw-check {
        to {
            stroke-dashoffset: 0;
        }
    }

    /* Titles */
    .main-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #2d3748;
        text-align: center;
        margin-bottom: 0.5rem;
        animation: fadeInUp 0.5s ease-out 0.2s backwards;
    }

    .main-subtitle {
        font-size: 1.1rem;
        color: #718096;
        text-align: center;
        margin-bottom: 2.5rem;
        animation: fadeInUp 0.5s ease-out 0.3s backwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Info Card */
    .info-card {
        background: linear-gradient(135deg, #f7fafc, #edf2f7);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        animation: fadeInUp 0.5s ease-out 0.4s backwards;
    }

    .info-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .user-details {
        flex: 1;
    }

    .user-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0 0 0.25rem 0;
    }

    .user-status {
        color: #48bb78;
        font-weight: 600;
        margin: 0;
        font-size: 0.95rem;
    }

    .info-divider {
        height: 1px;
        background: #cbd5e0;
        margin-bottom: 1.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .info-item {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .info-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .info-content {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.85rem;
        color: #718096;
        font-weight: 500;
    }

    .info-value {
        font-size: 0.95rem;
        color: #2d3748;
        font-weight: 600;
        margin-top: 0.15rem;
    }

    /* Benefits Section */
    .benefits-section {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.75rem;
        margin-bottom: 2rem;
        animation: fadeInUp 0.5s ease-out 0.5s backwards;
    }

    .benefits-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0 0 1.25rem 0;
    }

    .benefits-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .benefit-item {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .benefit-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .benefit-text strong {
        display: block;
        color: #2d3748;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .benefit-text p {
        color: #718096;
        font-size: 0.9rem;
        margin: 0;
        line-height: 1.5;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
        animation: fadeInUp 0.5s ease-out 0.6s backwards;
    }

    .secondary-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.875rem 1.5rem;
        background: white;
        border: 2px solid #667eea;
        color: #667eea;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .secondary-button:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .button-icon {
        font-size: 1.2rem;
    }

    /* Footer Note */
    .footer-note {
        text-align: center;
        color: #718096;
        font-size: 0.9rem;
        line-height: 1.6;
        margin: 0;
        animation: fadeIn 0.5s ease-out 0.7s backwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 640px) {
        .content-wrapper {
            padding: 2rem 1.5rem;
        }

        .main-title {
            font-size: 2rem;
        }

        .main-subtitle {
            font-size: 1rem;
        }

        .info-card {
            padding: 1.5rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .user-name {
            font-size: 1.25rem;
        }

        .benefits-section {
            padding: 1.5rem;
        }

        .action-buttons {
            gap: 0.75rem;
        }
    }
</style>