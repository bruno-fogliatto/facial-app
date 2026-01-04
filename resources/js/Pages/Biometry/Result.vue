<script setup>
    import { inject, ref, computed, onBeforeMount, onMounted } from 'vue';
    import serviceController from '@/Controllers/serviceController';
    import Button from '@/Components/Button.vue';

    const steps = inject('steps');

    const image = ref(null);
    const loading  = ref(true);
    const showContent = ref(false);
    const rekognitionData = ref(null);
    const thresholds = ref(null);
    const dataLoaded = ref(false);

    const configs = await serviceController.get('/configs?h=rekognition');
    if (configs?.data) {
        thresholds.value = configs.data;
    }

    const getIlluminationStatus = computed(() => {
        if (!rekognitionData.value) return { label: 'Aguardando...', status: 'neutral', icon: '💡' };

        const brightness = rekognitionData.value.brightness;
        if (brightness >= thresholds.value.brightness_min && brightness <= thresholds.value.brightness_max) {
            if (brightness >= 80 && brightness <= 90) {
                return { label: 'Excelente', status: 'excellent', icon: '💡' };
            } else {
                return { label: 'Bom', status: 'good', icon: '💡'};
            }
        } else if (brightness >= thresholds.value.brightness_min - 5 && brightness <= thresholds.value.brightness_max + 5) {
            return { label: 'Neutro', status: 'neutral', icon: '💡' };
        } else {
            return { label: 'Insuficiente', status: 'bad', icon: '💡' };
        }
    });

    const getPositionStatus = computed(() => {
        if (!rekognitionData.value) return { label: 'Aguardando...', status: 'neutral', icon: '📍' };

        const nose = rekognitionData.value.nose;
        if (!nose) return { label: "Não detectado", status: 'neutral', icon: '📍' };

        const isWellCentered = nose.x >= 0.48 && nose.x <= 0.58 && nose.y >= 0.60 && nose.y <= 0.70;
        const isAcceptableCentered = nose.x >= 0.45 && nose.x <= 0.60 && nose.y >= 0.55 && nose.y <= 0.75;

        if (isWellCentered) {
            return { label: 'Perfeita', status: 'excellent', icon: '📍' };
        } else if (isAcceptableCentered) {
            return { label: 'Neutro', status: 'neutral', icon: '📍' };
        } else {
            return { label: 'Desalinhado', status: 'bad', icon: '📍' };
        }
    });

    const getClarityStatus = computed(() => {
        if (!rekognitionData.value) return { label: 'Aguardando...', status: 'neutral', icon: '📷' };
       
        const sharpness = rekognitionData.value.sharpness;

        if (sharpness >= thresholds.value.sharpness_min * 3) {
            return { label: 'Nítida', status: 'excellent', icon: '📷' };
        } else if (sharpness >= thresholds.value.sharpness_min * 1.5) {
            return { label: 'Boa', status: 'good', icon: '📷' };
        } else if (sharpness >= thresholds.value.sharpness_min) {
            return { label: 'Neutro', status: 'neutral', icon: '📷' };
        } else {
            return { label: 'Desfocada', status: 'bad', icon: '📷' };
        }
    });

    const validations = computed(() => [
        {
            ...getIlluminationStatus.value,
            type: 'illumination'
        },
        {
            ...getPositionStatus.value,
            type: 'position'
        },
        {
            ...getClarityStatus.value,
            type: 'clarity'
        }
    ]);

    const hasFailedValidation = computed(() => {
        if (!dataLoaded.value) return false;
        return validations.value.some(v => v.status === 'bad');
    });

    const isApproved = computed(() => {
        if (!dataLoaded.value) return false;
        return !hasFailedValidation.value && rekognitionData.value !== null;
    });

    const loadImageFromAws = async () => {
        try {
            loading.value = true;
            const guest = localStorage.getItem('guest');

            const response = await serviceController.get('/search-photo', { guest_id: guest });
            image.value = response.data;
        } catch (error) {
            console.error('Erro ao carregar imagem: ', error);
        } finally {
            loading.value = false;
        }
    };

    const loadRekognitionData = async () => {
        try {
            const guest = localStorage.getItem('guest');
            const response = await serviceController.get('/analysis-data', { guest_id: guest });
            if (response.data) {
               rekognitionData.value =  response.data;
            }
        } catch (error) {
            console.error('Erro ao carregar dados Rekognition:', error)
        }
    };

    const handleFinalize = async () => {
        if (isApproved) {
            await steps.next();
        }
    };

    const handleRetry = async () => {
        await steps.goTo(3);
    };

    onBeforeMount(async () => {
        dataLoaded.value = false;

        await Promise.all([
            await loadImageFromAws(),
            await loadRekognitionData()
        ]);

        dataLoaded.value = true;
    });

    onMounted(() => {
        setTimeout(() => {
            showContent.value = true;
        }, 100);
    });
</script>

<template>
    <div class="container">
        <div class="hero-section" :class="{ 'show': showContent }">
            <!-- Badge de Sucesso/Falha -->
            <div v-if="dataLoaded" class="success-badge" :class="{ 'failed': hasFailedValidation }">
                <span class="badge-icon">{{ hasFailedValidation ? '⚠' : '✓' }}</span>
                <span class="badge-text">
                    {{ hasFailedValidation ? 'Atenção: Qualidade insuficiente!' : 'Procedimento realizado com sucesso!' }}
                </span>
            </div>

            <div v-else class="success-badge loading-badge">
                <div class="badge-spinner"></div>
                <div class="badge-text">Analisando resultados...</div>
            </div>

            <!-- Foto em Destaque -->
            <div class="photo-showcase">
                <div v-if="loading" class="loading">
                    <div class="spinner"></div>
                </div>
                
                <img 
                    v-else-if="image" 
                    :src="image" 
                    alt="Foto capturada" 
                    class="photo-circle"
                />
                
                <div v-else class="no-photo">
                    <span>📸</span>
                </div>
            </div>

            <!-- Título e Descrição -->
            <h2 class="main-title">
                {{ hasFailedValidation ? 'Revisão Necessária' : 'Biometria Cadastrada' }}
            </h2>
            <p class="main-description">
                {{ hasFailedValidation 
                    ? 'A qualidade da foto não atendeu todos os requisitos. Por favor, tire uma nova foto.' 
                    : 'Sua biometria facial foi cadastrada e validada com sucesso.' }}
            </p>

            <!-- Grid de Validações com Status Reais -->
            <div class="info-grid">
                <div 
                    v-for="(validation, index) in validations" 
                    :key="index"
                    class="info-card"
                    :class="`status-${validation.status}`"
                    :style="{ animationDelay: `${0.1 + index * 0.1}s` }"
                >
                    <div class="info-icon">{{ validation.icon }}</div>
                    <div class="info-label">
                        {{ validation.type === 'illumination' ? 'Iluminação' : 
                           validation.type === 'position' ? 'Posição' : 'Clareza' }}
                    </div>
                    <div class="info-status">{{ validation.label }}</div>
                </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="details-section" :class="{ 'failed': hasFailedValidation }">
                <div class="detail-row">
                    <span class="detail-label">Status do Cadastro</span>
                    <span class="detail-value" :class="{ 'success': isApproved, 'failed': hasFailedValidation }">
                        {{ isApproved ? '✓ Aprovado' : '⚠ Requer Revisão' }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Data de Cadastro</span>
                    <span class="detail-value">{{ new Date().toLocaleDateString('pt-BR') }}</span>
                </div>
                <div class="detail-row" v-if="rekognitionData">
                    <span class="detail-label">Confiança da Análise</span>
                    <span class="detail-value">{{ Math.round(rekognitionData.confidence) }}%</span>
                </div>
            </div>

            <!-- Botão de Ação - Condicional -->
            <div v-if="hasFailedValidation" class="action-buttons">
                <Button 
                    label="🔄 Refazer Biometria"
                    action="danger"
                    @click="handleRetry"
                />
                <p class="warning-text">
                    É necessário tirar uma nova foto com melhor qualidade.
                </p>
            </div>
            <div v-else>
                <Button 
                    label="Finalizar"
                    action="primary"
                    @click="handleFinalize"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
    .container {
        max-width: 650px;
        margin: 0 auto;
        padding: 1rem;
    }

    .hero-section {
        background: white;
        padding: 3rem 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        text-align: center;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease-out;
    }

    .hero-section.show {
        opacity: 1;
        transform: translateY(0);
    }

    /* Badge de Sucesso */
    .success-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
        padding: 0.875rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        margin-bottom: 2.5rem;
        font-size: 1.1rem;
        box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
        animation: slideDown 0.5s ease-out 0.2s backwards;
    }

    .loading-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .badge-spinner {
        width: 1.3rem;
        height: 1.3rem;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top: 3px solid white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    /* Badge de Falha */
    .success-badge.failed {
        background: linear-gradient(135deg, #f56565, #e53e3e);
        box-shadow: 0 4px 15px rgba(245, 101, 101, 0.3);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge-icon {
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
    }

    /* Foto em Destaque */
    .photo-showcase {
        width: 200px;
        height: 200px;
        margin: 0 auto 2.5rem;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        animation: scaleIn 0.6s ease-out 0.3s backwards;
        position: relative;
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .loading {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7fafc;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e2e8f0;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .photo-circle {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .no-photo {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f7fafc, #edf2f7);
        font-size: 4rem;
    }

    /* Título e Descrição */
    .main-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1rem;
        animation: fadeIn 0.5s ease-out 0.4s backwards;
    }

    .main-description {
        font-size: 1.1rem;
        color: #718096;
        line-height: 1.6;
        margin-bottom: 2.5rem;
        animation: fadeIn 0.5s ease-out 0.5s backwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Grid de Validações */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2.5rem;
    }

    .info-card {
        padding: 1.25rem 1rem;
        border-radius: 12px;
        border: 2px solid;
        animation: fadeInUp 0.5s ease-out backwards;
        transition: all 0.3s;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
    }

    .info-card:hover {
        transform: translateY(-2px);
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

    /* Status Colors */
    .status-excellent {
        background: linear-gradient(135deg, #f0fff4, #ffffff);
        border-color: #48bb78;
    }

    .status-excellent .info-status {
        color: #48bb78;
    }

    .status-good {
        background: linear-gradient(135deg, #fefcbf, #ffffff);
        border-color: #ecc94b;
    }

    .status-good .info-status {
        color: #d69e2e;
    }

    .status-neutral {
        background: linear-gradient(135deg, #e6fffa, #ffffff);
        border-color: #81e6d9;
    }

    .status-neutral .info-status {
        color: #319795;
    }

    .status-bad {
        background: linear-gradient(135deg, #fff5f5, #ffffff);
        border-color: #fc8181;
    }

    .status-bad .info-status {
        color: #e53e3e;
    }

    .info-icon {
        font-size: 2rem;
        margin-bottom: 0.75rem;
    }

    .info-label {
        font-size: 0.85rem;
        color: #718096;
        margin-bottom: 0.5rem;
        display: block;
        font-weight: 500;
    }

    .info-status {
        font-weight: 700;
        font-size: 0.95rem;
    }

    /* Detalhes */
    .details-section {
        background: #f7fafc;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        animation: fadeIn 0.5s ease-out 0.9s backwards;
    }

    .details-section.failed {
        background: linear-gradient(135deg, #fff5f5, #ffffff);
        border: 2px solid #fc8181;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.875rem 0;
    }

    .detail-row:not(:last-child) {
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-label {
        color: #718096;
        font-weight: 500;
    }

    .detail-value {
        color: #2d3748;
        font-weight: 600;
    }

    .detail-value.success {
        color: #48bb78;
    }

    .detail-value.failed {
        color: #e53e3e;
    }

    /* Ações */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .warning-text {
        color: #e53e3e;
        font-weight: 500;
        font-size: 0.9rem;
        text-align: center;
        margin: 0;
    }

    /* Responsivo */
    @media (max-width: 640px) {
        .hero-section {
            padding: 2rem 1.5rem;
        }

        .success-badge {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }

        .photo-showcase {
            width: 160px;
            height: 160px;
        }

        .main-title {
            font-size: 1.75rem;
        }

        .main-description {
            font-size: 1rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .info-card {
            padding: 1rem;
        }
    }
</style>