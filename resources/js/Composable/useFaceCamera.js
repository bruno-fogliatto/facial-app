import { FaceMesh } from "@mediapipe/face_mesh";
import { Camera } from "@mediapipe/camera_utils";

let cam = null;
let faceMesh = null;
let lastFrame = 0;
let initializing = false;

export async function startFaceCamera(videoRef, onResults)
{
    if (cam || initializing) return;
    initializing = true;

    faceMesh = new FaceMesh({
        locateFile: file => 
            `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`
    });

    faceMesh.setOptions({
        maxNumFaces: 1,
        refineLandmarks: true
    });

    faceMesh.onResults(onResults);

    cam = new Camera(videoRef.value, {
        onFrame: async () => {
            if (!faceMesh) return;

            const now = performance.now();
            if (now - lastFrame < 80) return; //~12 FPS

            await faceMesh.send({
                image: videoRef.value
            })
        }
    });

    await cam.start();
    initializing = false;
}

export async function stopFaceCamera(videoRef)
{
    if (cam) {
        cam.stop();
        cam = null;
    }

    if (videoRef?.value?.srcObject) {
        videoRef.value.srcObject.getTracks().forEach(t => t.stop());
        videoRef.value.srcObject = null;
    }

    if (faceMesh) {
        faceMesh.close?.();
        faceMesh = null;
    }

    lastFrame = 0;
}
