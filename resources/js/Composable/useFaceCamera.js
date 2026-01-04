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

    try {
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
                const video = videoRef.value;
    
                if (
                    !faceMesh ||
                    !video ||
                    !video.srcObject ||
                    video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA ||
                    video.videoWidth === 0 ||
                    video.videoHeight === 0
                ) return;
    
                const now = performance.now();
                if (now - lastFrame < 80) return; //~12 FPS
                lastFrame = now;
    
                await faceMesh.send({
                    image: video
                })
            }
        });
    
        await cam.start();
    } finally {
        initializing = false;
    }
}

export async function stopFaceCamera(videoRef)
{
    if (cam) {
        cam.stop();
        cam = null;
    }

    const video = videoRef?.value;
    if (video?.srcObject) {
        video.srcObject.getTracks().forEach(t => t.stop());
        video.srcObject = null;
    }

    if (faceMesh) {
        faceMesh.close?.();
        faceMesh = null;
    }

    lastFrame = 0;
}
