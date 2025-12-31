import { ref } from 'vue'
import serviceController from './serviceController';
import { stopFaceCamera } from '@/Composable/useFaceCamera';

export function useSteps({ 
    initialStep, 
    totalSteps, 
    routes, 
    lockedAfterCapture, 
    onLeaveStep = {} 
}) {
    const currentStep = ref(initialStep);
    const locked = ref(lockedAfterCapture);
    const syncEndpoint = '/validate-step';

    function updateUrl(step) {
        const url = routes[step];
        if (url) history.pushState({ step }, '', url);
    }

    function canGoTo(step) {
        if (!lockedAfterCapture) return true;

        if (currentStep.value > 3 && step <= 3) {
            return false;
        }

        return true;
    }

    async function handleLeave(step) {
        if (typeof onLeaveStep[step] === 'function') {
            onLeaveStep[step]();
        }
    }

    async function sync(step) {
        await serviceController.post(syncEndpoint, { step });
    }

    async function goTo(step) {
        if (step < 1 || step > totalSteps) return;
        if (step === currentStep.value) return;
        if (!canGoTo(step)) return;

        await handleLeave(currentStep.value);
        await sync(step);

        currentStep.value = step;
        updateUrl(step);
    }

    async function next() {
        return goTo(currentStep.value + 1);
    }

    async function back() {
        return goTo(currentStep.value - 1);
    }

    window.addEventListener('popstate', e => {
        const step = e.state?.step;

        if (!step) return;

        if (locked.value && step < currentStep.value) return;
        
        if (onLeaveStep[currentStep.value]) {
            onLeaveStep[currentStep.value]();
        }

        currentStep.value = step;
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden && currentStep.value === 3) {
            stopFaceCamera();
        }
    });

    return {
        currentStep,
        next,
        back,
        goTo,
        lockedAfterCapture
    }
}
