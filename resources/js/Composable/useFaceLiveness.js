import { ref } from 'vue';

export const state = ref('INIT');
export const canCapture = ref(false);
export const faceAligned = ref(false);
export const timeoutExpired = ref(false);
export const stabilityScore = ref(0);

export const statesText = {
    INIT: 'Encaixe seu rosto na moldura',
    CENTER: 'Centralize seu rosto na moldura',
    TOO_FAR: 'Aproxime-se da câmera',
    TOO_CLOSE: 'Afaste-se da câmera',
    EYES_OPEN: 'Mantenha os olhos abertos',
    RECENTER: 'Centralize novamente o rosto',
    NEUTRAL: 'Mantenha expressão neutra',
    READY: 'Pronto para capturar',
    TIMEOUT: 'Tempo esgotado. Tente novamente.'
};

let active = true;
let timeoutHandle = null;
let stabilityFrames = 0;
const REQUIRED_STABLE_FRAMES = 12;

function isFaceCentered(lm)
{
    const nose = lm[1];
    return (
        nose.x > 0.48 && nose.x < 0.58 &&
        nose.y > 0.60 && nose.y < 0.70
    );
}

function checkFaceDistance(lm)
{
    const leftEye = lm[33];
    const rightEye = lm[263];
    const topForehead = lm[10];
    const chin = lm[152];

    const dx = Math.abs(leftEye.x - rightEye.x);
    const dy = Math.abs(chin.y - topForehead.y);

    if (dx < 0.20 || dy < 0.48) return 'TOO_FAR';
    
    if (dx > 0.25 || dy > 0.58) return 'TOO_CLOSE';

    return "OK";
}

function eyesOpen(lm)
{
    const left = Math.abs(lm[159].y - lm[145].y);
    const right = Math.abs(lm[386].y - lm[374].y);
    return left > 0.015 && right > 0.015;
}

function isNeutral(lm)
{
    const mouthOpen = Math.abs(lm[13].y - lm[14].y);
    const mouthWidth = Math.abs(lm[61].x - lm[291].x);
    return mouthOpen < 0.015 && mouthWidth < 0.32;
}

function updateStability()
{
    if (faceAligned.value &&
        state.value === 'READY'
    ) {
        stabilityFrames++;
    } else {
        stabilityFrames = 0;
    }

    stabilityScore.value = Math.min(1, stabilityFrames / REQUIRED_STABLE_FRAMES);
}

function startTimeout()
{
    if (timeoutHandle) clearTimeout(timeoutHandle);
    timeoutHandle = setTimeout(() => {
    timeoutExpired.value = true;
    resetLiveness();
    }, 20000);
}


export function processFace(results)
{
    if (!active) return;

    if (!results.multiFaceLandmarks?.length) {
        resetLiveness();
        return;
    }

    startTimeout();

    const lm = results.multiFaceLandmarks[0];

    const distanceStatus = checkFaceDistance(lm);

    if (state.value === 'INIT' || state.value === 'TOO_FAR' || state.value === 'TOO_CLOSE') {        
        if (distanceStatus !== 'OK') {
            state.value = distanceStatus;
            return;
        }

        state.value = 'CENTER';
        return;
    }

    if (state.value === 'CENTER') {
        if (distanceStatus !== 'OK') {
            state.value = distanceStatus;
            return;
        }

        if (!isFaceCentered(lm)) {
            faceAligned.value = false;
            return;
        }

        faceAligned.value = true;
        state.value = 'EYES_OPEN';
        return;
    }

    if (state.value === 'EYES_OPEN') {
        if (!eyesOpen(lm)) return;

        state.value = 'RECENTER';
        return;
    }

    if (state.value === 'RECENTER') {
        if (distanceStatus !== 'OK') {
            state.value = distanceStatus;
            return;
        }

        if (!isFaceCentered(lm)) return;

        state.value = 'NEUTRAL';
        return;
    }

    if (state.value === 'NEUTRAL') {
        if (distanceStatus !== 'OK') {
            state.value = distanceStatus;
            return;
        }

        if (!isNeutral(lm)) return;

        stabilityFrames = 0;
        stabilityScore.value = 0;

        state.value = 'READY';
        canCapture.value = true;

        clearTimeout(timeoutHandle);
        return;
    }

    if (state.value === 'READY') {
        updateStability();
        return;
    }
}

export function resetLiveness()
{
    stabilityFrames = 0;
    stabilityScore.value = 0;

    state.value = 'CENTER';
    canCapture.value = false;
    faceAligned.value = false;

    if (timeoutHandle) {
        clearTimeout(timeoutHandle);
        timeoutHandle = null;
    }
}

export function deactivateLiveness()
{
    if (timeoutHandle) {
        clearTimeout(timeoutHandle);
        timeoutHandle = null;
    }
}
