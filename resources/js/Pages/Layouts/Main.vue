<script setup>
    import { Head } from '@inertiajs/vue3';
    import { ref, provide, onMounted } from 'vue';
    import { useBiometrySteps } from '@/Composable/useBiometrySteps';
    import { useBiometryStore } from '@/Stores/biometry'; 

    import GlobalHeader from "@/Components/GlobalHeader.vue";
    import GlobalFooter from "@/Components/GlobalFooter.vue";

    import Wellcome from '@/Pages/Biometry/Wellcome.vue';
    import Instructions from "@/Pages/Biometry/Instructions.vue";
    import PhotoCapture from "@/Pages/Biometry/PhotoCapture.vue";
    import Result from '@/Pages/Biometry/Result.vue';
    import Finalization from '@/Pages/Biometry/Finalization.vue';

    const props = defineProps({
        serverStep: {
            type: Number,
            default: 1
        },
        lockedAfterCapture: {
            type: Boolean,
            default: false
        },
        access_token: {
            type: String,
            default: null
        },
        guest: {
            type: Number,
            default: null
        }
    });

    const state = ref({
        faceImage: null,
        documentData: null,
        token: null
    });

    provide('homeState', state);
    
    const biometryStore = useBiometryStore();
    const steps = useBiometrySteps();

    onMounted(() => {
        biometryStore.initialize({
            serverStep: props.serverStep,
            lockedAfterCapture: props.lockedAfterCapture
        });

        if (props.access_token) {
            sessionStorage.setItem('access_token', props.access_token);
        }

        if (props.guest) {
            localStorage.setItem('guest', props.guest);
        }
    });

    provide('steps', steps);
</script>

<template>
    <Head title='Marina Blue Fox | Biometria Facial'></Head>

    <GlobalHeader 
        :currentStep="steps.currentStep.value"
        :totalSteps="5"
    />

    <Suspense>
        <main class="main-content">
            <Wellcome v-if="steps.currentStep.value === 1" />
            <Instructions v-if="steps.currentStep.value === 2" />
            <PhotoCapture v-else-if="steps.currentStep.value === 3" />
            <Result v-else-if="steps.currentStep.value === 4" />
            <Finalization v-else-if="steps.currentStep.value === 5" />
        </main>
    </Suspense>

    <GlobalFooter />
</template>

<style scoped>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #ede9e9;
        color: #333;
        line-height: 1.6;
    }

    #app {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background-color: #ede9e9;
    }

    .main-content {
        flex: 1;
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
        background-color: #ede9e9;
        background-clip: padding-box;
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 1rem 0.5rem;
        }
    }
</style>