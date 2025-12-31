<script setup>
    import { inject, ref, onBeforeMount } from 'vue';
    import serviceController from '@/Controllers/serviceController';
    import InstructionsCard from '@/Components/InstructionsCard.vue';
    import Button from '@/Components/Button.vue';

    const steps = inject('steps');

    const image = ref(null);

    const loadImageFromAws = async () => {
        const guest = localStorage.getItem('guest');
        let data = {};
        if (guest) {
            data = {
                guest_id: guest
            }
        };
        image = await serviceController.get('/s3/search', { params: data });
    };

    const retakePhoto = () => {

    };

    const continueProcess = () => {
        steps.next();
    };

    onBeforeMount(() => {
        loadImageFromAws();
    });
</script>

<template>
  <div class="container">
    <div class="header">
      <h1 class="title">Revisão da Captura</h1>
      <p class="subtitle">Verifique a qualidade da sua foto capturada</p>
    </div>
    
    <div class="review-content">
      <div class="photo-preview">
        <img 
          v-if="image" 
          :src="image" 
          alt="Foto capturada" 
          class="preview-image"
        />
        <div v-else class="no-photo">
          <p>Nenhuma foto encontrada</p>
        </div>
      </div>
      
      <div class="validation-cards">
        <InstructionsCard
          icon="💡"
          title="Iluminação"
          description="Qualidade da iluminação na foto"
          status="Excelente"
        />
        
        <InstructionsCard
          icon="📍"
          title="Posição do Rosto"
          description="Centralização e enquadramento"
          status="Perfeita"
        />
        
        <InstructionsCard
          icon="🔍"
          title="Clareza da Imagem"
          description="Nitidez e qualidade da imagem"
          status="Nítida"
        />
      </div>
    </div>
    
    <div class="action-section">
      <Button
        label="Refazer"
        action="secondary"
        @click="retakePhoto"
      />
      <Button
        label="Continuar"
        action="primary"
        icon="→"
        @click="continueProcess"
      />
    </div>
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
  }

  .review-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
  }

  .photo-preview {
    display: flex;
    justify-content: center;
    align-items: center;
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .preview-image {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .no-photo {
    text-align: center;
    color: #6c757d;
    padding: 2rem;
  }

  .validation-cards {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .action-section {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
  }

  @media (max-width: 768px) {
    .title {
      font-size: 2rem;
    }
    
    .review-content {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }
    
    .action-section {
      flex-direction: column;
      align-items: center;
    }
  }
</style>