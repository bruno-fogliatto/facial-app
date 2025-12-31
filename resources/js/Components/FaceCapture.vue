<script setup>
  import { ref, onMounted, onBeforeMount, onBeforeUnmount, watch } from 'vue';
  import { startFaceCamera, stopFaceCamera } from '@/Composable/useFaceCamera';

  import {
    state,
    canCapture,
    faceAligned,
    statesText,
    timeoutExpired,
    processFace,
    resetLiveness,
    deactivateLiveness,
    stabilityScore
  } from '@/Composable/useFaceLiveness';

  import Button from './Button.vue';
  import shutterMP3 from '@/Assets/camera-shutter.mp3';

  const emits = defineEmits(['photoCaptured']);
  const props = defineProps({
    icon: {
      type: String,
      default: null
    }
  });

  const manualCapture = ref(false);
  const videoElement = ref(null);
  const canvasElement = ref(null);
  const capturedImage = ref(null);
  const countdown = ref(null);
  const isCounting = ref(false);
  const flash = ref(false);
  const stableFrames = ref(0);

  const imageSrc = ref(props.icon);
  

  const REQUIRED_STABLE_FRAMES = 8; // ~260ms
  const IOS_READY_DELAY = 400;
  const CAPTURE_DELAY = 180;

  let shutterSound = null;
  let countdownTimer = null;
  let stabilityInterval = null;
  let captureLocked = false;

  const capturePhoto = () => {
    const video = videoElement.value;
    const canvas = canvasElement.value;
    const ctx = canvas.getContext('2d');

    const vw = video.videoWidth;
    const vh = video.videoHeight;

    canvas.width = vw * 0.6;
    canvas.height = vh * 0.7;

    ctx.drawImage(
      video,
      vw * 0.2, vh * 0.15,
      vw * 0.6, vh * 0.7,
      0, 0,
      canvas.width, canvas.height
    )

    const image = canvas.toDataURL('image/jpeg', 0.9);
    emits('photoCaptured', image);
  };

  watch(timeoutExpired, (v) => {
    if (v) {
      setTimeout(() => {
        timeoutExpired.value = false;
        resetLiveness();
      }, 20000);
    }
  });

  watch(state, (newState) => {
    if (newState === 'READY') {
      startStabilityCheck();
    } else {
      clearAllTimers();
    }
  });

  function clearAllTimers() {
    if (stabilityInterval) {
      clearInterval(stabilityInterval);
      stabilityInterval = null;
    }

    if (countdownTimer) {
      clearInterval(countdownTimer);
      countdownTimer = null;
    }

    stableFrames.value = 0;
    isCounting.value = 0;
    countdown.value = null;
  };

  function startStabilityCheck() {
    if (stabilityInterval || isCounting.value) return;
    stableFrames.value = 0;

    stabilityInterval = setInterval(() => {
      if (!faceAligned.value || state.value !== 'READY') {
        stableFrames.value = 0;
        return;
      }

      stableFrames.value++;

      if (stableFrames.value >= REQUIRED_STABLE_FRAMES) {
        clearInterval(stabilityInterval);
        stabilityInterval = null;

        setTimeout(() => {
          if (state.value === 'READY' && faceAligned.value) {
            startCountdown();
          }
        }, IOS_READY_DELAY);
      }
    }, 33);
  }

  function startCountdown() {
    if (isCounting.value) return;
    isCounting.value = true;
    countdown.value = 3;

    countdownTimer = setInterval(() => {
      if (!faceAligned.value || state.value !== 'READY') {
        clearAllTimers();
        return;
      }

      countdown.value--;

      if (countdown.value <= 0) {
        clearAllTimers();
        triggerCapture();
      }
    }, 1000);
  };

  function triggerCapture() {
    if (captureLocked) return;
    captureLocked = true;

    flash.value = true;

    shutterSound.currentTime = 0;
    shutterSound.play().catch(() => {});

    setTimeout(() => {
      flash.value = false;
      capturePhoto();

      setTimeout(() => {
        captureLocked = false;
      }, 1000);
    }, CAPTURE_DELAY);
  };

  onBeforeMount(() => {
    const images = JSON.parse(localStorage.getItem('images'));
    if (images?.camera) {
      imageSrc.value = images.camera;
    }
  });

  onMounted(async () => {
    try {
      shutterSound = new Audio(shutterMP3);
      shutterSound.volume = 0.7;
      await startFaceCamera(videoElement, processFace);
    } catch (err) {
      alert('Não foi possível acessar a câmera. Verifique as permissões.');
    }
  });

  onBeforeUnmount(async () => {
    deactivateLiveness();
    stopFaceCamera(videoElement);
  });
</script>

<template>
  <div class="face-capture">
    <div class="camera-container">
      <video 
        ref="videoBlur" 
        class="camera-video video-blur" 
        autoplay 
        muted 
        playsinline
      ></video>

      <video
        ref="videoElement"
        class="camera-video video-clear"
        autoplay
        muted
        playsinline
      ></video>

      <canvas 
        ref="canvasElement" 
        class="capture-canvas" 
        style="display: none;"
      ></canvas>

      <div class="mask-layer"></div>

      <div class="face-overlay">
        <div class="stability-bar-wrapper">
          <div
            class="stability-bar"
            :style="{ width: `${stabilityScore * 100}%` }"
          ></div>
        </div>

        <div class="face-frame">
          <div v-if="countdown" class="countdown">
            {{ countdown }}
          </div>
        </div>
      </div>
    </div>

    <div class="flash-overlay" v-if="flash"></div>

    <div class="capture-instructions">
      <p class="instruction-text" :class="{ error: timeoutExpired, success: state === 'READY' }">
        {{ timeoutExpired ? statesText.TIMEOUT : statesText[state] }}
      </p>
    </div>
    
    <div class="capture-controls">
      <Button 
        :visible="manualCapture"
        :label="capturedImage"
        action="primary"
        :image="imageSrc"
        :disabled="!canCapture"
        @click="capturePhoto"
      />
    </div>
  </div>
</template>

<style scoped>
  .face-capture {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: clamp(1rem, 3vh, 2rem);
    width: 100%;
  }

  .camera-container {
    position: relative;
    width: min(92vw, 380px);
    aspect-ratio: 3 / 4;
    border-radius: clamp(14px, 4vw, 20px);
    overflow: hidden;
    background: #000;
  }

  .camera-video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .capture-canvas {
    position: absolute;
    top: 0;
    left: 0;
  }

  .face-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
  }

  .face-frame {
    /* position: absolute; */
    width: 72%;
    height: 76%;
    border: 3px solid rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    transition: border-color 0.3s, box-shadow 0.3s;
    z-index: 4;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .capture-instructions {
    text-align: center;
  }

  .instruction-text {
    font-size: clamp(0.95rem, 2.8vw, 1.1rem);
    color: #6c757d;
    margin: 0;
  }

  .instruction-text.success {
    color: #28a745;
    font-weight: 500;
  }

  .capture-controls {
    display: flex;
    gap: 1rem;
  }

  .video-blur {
    filter: blur(14px);
    transform: scale(1.15);
    z-index: 1;
  }

  .video-clear {
    z-index: 2;
  }

  .mask-layer {
    position: absolute;
    inset: 0;
    z-index: 3;
    background: rgba(0, 0, 0, 0.65);

    mask-image: radial-gradient(
      ellipse 36% 38% at center,
      transparent 99%,
      black 100%
    );

    -webkit-mask-image: radial-gradient(
      ellipse 36% 38% at center,
      transparent 99%,
      black 100%
    );
  }

  .flash-overlay {
    position: absolute;
    inset: 0;
    background: white;
    opacity: 0.9;
    z-index: 10;
    animation: flash 150ms ease-out;
  }

  @keyframes flash {
    from { opacity: 0.9; }
    to { opacity: 0; }
  }

  .countdown {
    font-size: clamp(3.5rem, 12vw, 6.5rem);
    font-weight: 800;
    color: white;
    text-shadow: 0 0 20px rgba(0, 0, 0, 0.9);
    user-select: none;
    pointer-events: none;
  }

  .stability-bar-wrapper {
    position: absolute;
    bottom: clamp(10px, 3vh, 16px);
    left: 50%;
    transform: translateX(-50%);
    width: 72%;
    height: 6px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 999px;
    overflow: hidden;
    z-index: 6;
  }

  .stability-bar {
    height: 100%;
    background: linear-gradient(
      90deg,
      #28a745,
      #6bff95
    );
    border-radius: 999px;
    transition: width 120ms linear;
  }

  @media (max-width: 768px) {
    .camera-container {
      width: 300px;
      height: 250px;
    }
    
    .face-frame {
      width: 150px;
      height: 190px;
    }

    .mask-layer {
      mask-image: radial-gradient(
        ellipse 26% 39% at center,
        transparent 99%,
        black 100%
      );

      -webkit-mask-image: radial-gradient(
        ellipse 26% 39% at center,  
        transparent 99%, 
        black 100%
      );
    }
  }

  @media (max-height: 640px) {
    .camera-container {
      width: min(88vw, 320px);
    }

    .face-frame {
      width: 70%;
      height: 72%;
    }
  }

</style>
