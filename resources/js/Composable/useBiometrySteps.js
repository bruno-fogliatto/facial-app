import { computed, onMounted, onUnmounted } from 'vue';
import { useBiometryStore } from '@/Stores/biometry';
import { stopFaceCamera } from '@/Composable/useFaceCamera';

export function useBiometrySteps() {
    const store = useBiometryStore();

    const currentStep = computed(() => store.currentStep);
    const isLocked = computed(() => store.locked);
    const canGoBack = computed(() => store.canGoBack(currentStep - 1));
    const canGoForward = computed(() => store.canGoForward(currentStep + 1));

    async function next() {
        const success = await store.next();
        if (!success) {
            console.warn('Não foi possível avançar para o próximo step');
        }
        return success;
    };

    async function back() {
        const success = await store.back();
        if (!success) {
            console.warn('Não foi possível voltar para o step anterior');
        }
        return success;
    };

    async function goTo(step) {
        return store.goToStep(step);
    };

    function handleVisibilityChange() {
        if (document.hidden && currentStep.value === 3) {
            stopFaceCamera();
        }
    };

    onMounted(() => {
        document.addEventListener('visibilitychange', handleVisibilityChange);
    });

    onUnmounted(() => {
        document.removeEventListener('visibilitychange', handleVisibilityChange);
    });

    return {
        currentStep,
        isLocked,
        canGoBack,
        canGoForward,

        next,
        back,
        goTo,

        store
    }
}