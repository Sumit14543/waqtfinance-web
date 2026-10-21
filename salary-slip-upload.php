<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/loan_flow.php';

$application = loan_flow_require_application($conn);
$nextUrl = loan_flow_next_url($conn, $application);
if (basename($nextUrl) !== 'salary-slip-upload.php') {
    header('Location: ' . $nextUrl);
    exit;
}

include 'includes/header.php';
include 'includes/main-header.php';
?>

<link rel="stylesheet" href="assets/css/loan-flow.css">

<div class="lf-page">
    <div class="lf-container">
        <div class="lf-progress">
            <div class="lf-progress-track">
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Basic<br>Details</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">PAN<br>Verify</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Aadhaar<br>Verify</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Work<br>Details</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">Bank<br>Details</div>
                </div>
                <div class="lf-step done">
                    <div class="lf-step-dot"><i class="fa-solid fa-check"></i></div>
                    <div class="lf-step-label">References</div>
                </div>
                <div class="lf-step active">
                    <div class="lf-step-dot">7</div>
                    <div class="lf-step-label">Upload<br>Docs</div>
                </div>
            </div>
        </div>

        <div class="lf-card">
            <div class="lf-card-header">
                <div class="lf-card-icon">
                    <i class="fa-solid fa-file-arrow-up"></i>
                </div>
                <h1 class="lf-card-title">Upload Documents</h1>
                <p class="lf-card-sub">Upload salary proof and complete live selfie verification.</p>
            </div>

            <div class="lf-card-body">
                <form id="wm-unique-form" enctype="multipart/form-data">
                    <div class="lf-form-group">
                        <label class="lf-label">Current Month Salary Slip <span class="lf-req">*</span></label>
                        <label class="lf-upload-card" id="salaryUploadCard">
                            <input type="file" name="current_salary_slip" accept=".pdf,.jpg,.jpeg,.png" class="lf-upload-input" id="salarySlipInput">
                            <div class="lf-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="lf-upload-title">Choose salary slip</div>
                            <div class="lf-upload-hint">PDF, JPG, or PNG up to 2MB</div>
                            <div class="lf-upload-filename" id="salaryFileName"></div>
                        </label>
                        <small class="lf-error-text" id="salarySlipError"></small>
                    </div>

                    <div class="lf-form-group">
                        <label class="lf-label">Live Selfie Verification <span class="lf-req">*</span></label>
                        <div class="lf-camera-wrap">
                            <video id="wm-unique-camera" autoplay playsinline></video>
                            <img id="wm-unique-preview" alt="Selfie preview" style="display:none;">
                        </div>
                        <small class="lf-error-text" id="selfieError"></small>

                        <div class="lf-action-row">
                            <button type="button" id="wm-unique-capture" class="lf-btn lf-btn-outline">
                                <i class="fa-solid fa-camera"></i> Take Selfie
                            </button>
                            <button type="button" id="wm-unique-retake" class="lf-btn lf-btn-ghost" style="display:none;">
                                Retake
                            </button>
                        </div>

                        <canvas id="wm-unique-canvas" style="display:none;"></canvas>
                    </div>

                    <button type="submit" class="lf-btn lf-btn-primary">
                        Submit Application <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

            <div class="lf-trust">
                <div class="lf-trust-item"><i class="fa-solid fa-shield-halved"></i> Secure Upload</div>
                <div class="lf-trust-item"><i class="fa-solid fa-lock"></i> Encrypted Storage</div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const video = document.getElementById("wm-unique-camera");
        const canvas = document.getElementById("wm-unique-canvas");
        const captureBtn = document.getElementById("wm-unique-capture");
        const retakeBtn = document.getElementById("wm-unique-retake");
        const preview = document.getElementById("wm-unique-preview");
        const form = document.getElementById("wm-unique-form");
        const fileInput = document.getElementById("salarySlipInput");
        const uploadCard = document.getElementById("salaryUploadCard");
        const fileName = document.getElementById("salaryFileName");
        const salaryError = document.getElementById("salarySlipError");
        const selfieError = document.getElementById("selfieError");

        let stream;
        let selfieFile = null;

        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then(s => {
                stream = s;
                video.srcObject = s;
            })
            .catch(() => {
                selfieError.innerText = "Camera permission is required for selfie verification";
            });

        fileInput.addEventListener("change", () => {
            salaryError.innerText = "";
            uploadCard.classList.remove("lf-error-border");

            const file = fileInput.files[0];
            
            // Remove existing preview container if any
            const existingPreview = uploadCard.querySelector('.lf-upload-preview-container');
            if (existingPreview) {
                existingPreview.remove();
            }

            if (!file) {
                fileName.innerText = "";
                uploadCard.classList.remove("has-file");
                uploadCard.classList.remove("lf-has-preview");
                return;
            }

            const err = validateFile(file);
            if (err) {
                salaryError.innerText = err;
                fileInput.value = "";
                fileName.innerText = "";
                uploadCard.classList.remove("has-file");
                uploadCard.classList.remove("lf-has-preview");
                uploadCard.classList.add("lf-error-border");
                return;
            }

            fileName.innerText = file.name;
            uploadCard.classList.add("has-file");
            uploadCard.classList.add("lf-has-preview");

            // Create and append preview
            const container = document.createElement('div');
            container.className = 'lf-upload-preview-container';
            
            // Create Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'lf-upload-preview-remove';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                fileInput.value = '';
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
            container.appendChild(removeBtn);
            
            const isImage = file.type.startsWith('image/');
            if (isImage) {
                const img = document.createElement('img');
                img.className = 'lf-upload-preview-image';
                img.alt = 'Salary slip preview';
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);
                container.appendChild(img);
            } else {
                const icon = document.createElement('div');
                icon.className = 'lf-upload-preview-icon';
                icon.innerHTML = file.type === 'application/pdf' 
                    ? '<i class="fas fa-file-pdf"></i>' 
                    : '<i class="fas fa-file-lines"></i>';
                container.appendChild(icon);
            }
            
            const nameEl = document.createElement('div');
            nameEl.className = 'lf-upload-preview-filename';
            nameEl.innerText = file.name;
            container.appendChild(nameEl);
            
            const sizeKB = (file.size / 1024).toFixed(1);
            const metaEl = document.createElement('div');
            metaEl.className = 'lf-upload-preview-meta';
            metaEl.innerText = `${sizeKB} KB`;
            container.appendChild(metaEl);
            
            uploadCard.appendChild(container);
        });

        let faceModelsLoaded = false;
        captureBtn.onclick = async () => {
            selfieError.innerText = "";

            if (!video.videoWidth || !video.videoHeight) {
                selfieError.innerText = "Camera is still loading. Please try again.";
                return;
            }

            selfieError.innerText = "";
            captureBtn.disabled = true;
            captureBtn.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Verifying face...";

            try {
                if (typeof faceapi === 'undefined') {
                    throw new Error("Face detection library is loading. Please wait.");
                }
                if (!faceModelsLoaded) {
                    await faceapi.nets.tinyFaceDetector.loadFromUri('assets/models');
                    faceModelsLoaded = true;
                }

                const detections = await faceapi.detectAllFaces(
                    video,
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.5 })
                );

                if (detections.length === 0) {
                    throw new Error("No face detected. Please position your face clearly in a well-lit area.");
                }
                if (detections.length > 1) {
                    throw new Error("Multiple faces detected. Please make sure only one person is in the frame.");
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext("2d").drawImage(video, 0, 0);

                canvas.toBlob(blob => {
                    selfieFile = new File([blob], "selfie.png", { type: "image/png" });
                    preview.src = URL.createObjectURL(selfieFile);
                    preview.style.display = "block";
                    video.style.display = "none";
                    retakeBtn.style.display = "flex";
                    captureBtn.disabled = false;
                    captureBtn.innerHTML = "<i class='fa-solid fa-check'></i> Selfie Captured";
                }, "image/png");

            } catch (err) {
                selfieError.innerText = err.message || "Face verification failed. Please try again.";
                captureBtn.disabled = false;
                captureBtn.innerHTML = "<i class='fa-solid fa-camera'></i> Take Selfie";
            }
        };

        retakeBtn.onclick = () => {
            selfieFile = null;
            preview.style.display = "none";
            video.style.display = "block";
            retakeBtn.style.display = "none";
            captureBtn.innerHTML = "<i class='fa-solid fa-camera'></i> Take Selfie";
        };

        function validateFile(file) {
            const allowed = ["application/pdf", "image/jpeg", "image/png"];
            const max = 2 * 1024 * 1024;

            if (!allowed.includes(file.type)) return "Upload PDF, JPG, or PNG file only";
            if (file.size > max) return "File size must be under 2MB";

            return null;
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
            }
        }

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            salaryError.innerText = "";
            selfieError.innerText = "";
            uploadCard.classList.remove("lf-error-border");

            const file = fileInput.files[0];

            if (!file) {
                salaryError.innerText = "Upload salary slip";
                uploadCard.classList.add("lf-error-border");
                return;
            }

            const err = validateFile(file);
            if (err) {
                salaryError.innerText = err;
                uploadCard.classList.add("lf-error-border");
                return;
            }

            if (!selfieFile) {
                selfieError.innerText = "Please take selfie";
                return;
            }

            const submitBtn = form.querySelector("button[type='submit']");
            if (submitBtn.disabled) return;

            submitBtn.disabled = true;
            submitBtn.innerHTML = "<div class='lf-spinner'></div> Uploading...";

            const formData = new FormData();
            formData.append("current_salary_slip", file);
            formData.append("selfie_image", selfieFile);

            try {
                const res = await fetch("api/apply/save_upload_doc.php", {
                    method: "POST",
                    headers: {
                        "X-CSRF-Token": window.WAQT_CSRF_TOKEN || ""
                    },
                    body: formData
                });

                const data = await res.json();

                if (data.status === "success") {
                    stopCamera();
                    window.location.href = "application-status.php";
                } else {
                    throw new Error(data.message);
                }
            } catch (err) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = "Submit Application <i class='fa-solid fa-arrow-right'></i>";
                alert(err.message || "Upload failed");
            }
        });
    })();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/face-api.js/0.22.2/face-api.min.js"></script>
<?php include 'includes/footer.php' ?>
