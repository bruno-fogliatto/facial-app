<script setup>
    import { Head, usePage } from '@inertiajs/vue3';
    import { ref, provide } from 'vue';
    import { useSteps } from '@/Controllers/stepsController';
    import { stopFaceCamera } from '@/Composable/useFaceCamera';

    import GlobalHeader from "@/Components/GlobalHeader.vue";
    import GlobalFooter from "@/Components/GlobalFooter.vue";

    import Instructions from "../Biometry/Instructions.vue";
    import PhotoCapture from "../Biometry/PhotoCapture.vue";
    import Processing from "../Biometry/Processing.vue";
    import Review from "../Biometry/Review.vue";
    import Result from "../Biometry/Result.vue";
    import Wellcome from '../Biometry/Wellcome.vue';

    const state = ref({
        faceImage: null,
        documentData: null,
        token: null,
    });

    provide('homeState', state);

    const page = usePage();
    const serverStep = page.props.serverStep;
    const lockedAfterCapture = page.props.lockedAfterCapture;

    if (page.props.access_token) {
        sessionStorage.setItem('access_token', page.props.access_token);
    }

    if (page.props.guest) {
        localStorage.setItem('guest', page.props.guest);
    }

    const steps = useSteps({
        initialStep: serverStep,
        totalSteps: 6,
        routes: {
            1: '/biometry',
            2: '/biometry/instuctions',
            3: '/biometry/capture',
            4: '/biometry/processing',
            5: '/biometry/review',
            6: '/biometry/result'
        },
        lockedAfterCapture: lockedAfterCapture,
        onLeaveStep: {
            3: () => {
                stopFaceCamera();
            }
        }
    });

    provide('steps', steps);
</script>

<template>
    <Head title='Marina Blue Fox | Biometria Facial'></Head>

    <GlobalHeader 
        :currentStep="steps.currentStep.value"
        :totalSteps="6"
    />

    <main class="main-content">
        <Wellcome v-if="steps.currentStep.value === 1" />
        <Instructions v-if="steps.currentStep.value === 2" />
        <PhotoCapture v-else-if="steps.currentStep.value === 3" />
        <Processing v-else-if="steps.currentStep.value === 4" />
        <Review v-else-if="steps.currentStep.value === 5" />
        <Result v-else-if="steps.currentStep.value === 6" />
    </main>

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
        padding: 2rem 1rem;
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