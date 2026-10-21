class LoanApplication {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 3;
        this.form = document.getElementById('applyForm');
        this.panels = document.querySelectorAll('.wf-step-panel');
        this.nodes = document.querySelectorAll('.wf-step-node');
        this.progressLine = document.querySelector('.wf-progress-line');
        this.btnPrev = document.getElementById('btnPrev');
        this.btnNext = document.getElementById('btnNext');
        
        this.init();
    }

    init() {
        if (!this.form) return;

        // Button Listeners
        this.btnNext.addEventListener('click', () => this.nextStep());
        this.btnPrev.addEventListener('click', () => this.prevStep());

        // Real-time Validation Listeners
        this.form.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('blur', (e) => this.validateField(e.target));
            input.addEventListener('input', (e) => {
                if (e.target.classList.contains('is-invalid') || e.target.closest('.wf-input-group').classList.contains('error')) {
                    this.validateField(e.target);
                }
                this.handleMasking(e.target);
            });
        });

        // Initial UI State
        this.updateUI();
    }

    handleMasking(field) {
        if (field.id === 'panField') {
            field.value = field.value.toUpperCase().substring(0, 10);
        }
        if (field.id === 'phoneField') {
            field.value = field.value.replace(/\D/g, '').substring(0, 10);
        }
    }

    validateField(field) {
        const group = field.closest('.wf-input-group');
        if (!group) return true;

        let isValid = true;
        let errorMsg = "";

        // Required check
        if (field.hasAttribute('required') && !field.value.trim()) {
            isValid = false;
            errorMsg = "This field is required";
        } 
        // PAN Check
        else if (field.id === 'panField' && !/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(field.value.trim())) {
            isValid = false;
            errorMsg = "Invalid PAN format (e.g. ABCDE1234F)";
        }
        // Phone Check
        else if (field.id === 'phoneField' && !/^[6-9]\d{9}$/.test(field.value.trim())) {
            isValid = false;
            errorMsg = "Invalid 10-digit mobile number";
        }
        // Email Check if exists
        else if (field.type === 'email' && field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            isValid = false;
            errorMsg = "Invalid email address";
        }

        if (isValid) {
            group.classList.remove('error');
            group.classList.add('success');
            field.classList.remove('is-invalid');
        } else {
            group.classList.remove('success');
            group.classList.add('error');
            field.classList.add('is-invalid');
            const errorLabel = group.querySelector('.wf-error-msg');
            if (errorLabel) errorLabel.innerText = errorMsg;
        }

        return isValid;
    }

    validateCurrentStep() {
        const currentPanel = this.panels[this.currentStep - 1];
        const fields = currentPanel.querySelectorAll('input[required], select[required]');
        let stepValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                stepValid = false;
            }
        });

        if (!stepValid) {
            this.form.classList.add('shake');
            setTimeout(() => this.form.classList.remove('shake'), 400);
        }

        return stepValid;
    }

    nextStep() {
        if (this.currentStep < this.totalSteps) {
            if (this.validateCurrentStep()) {
                this.currentStep++;
                this.updateUI();
            }
        } else {
            // Final Submit
            if (this.validateCurrentStep()) {
                this.submitForm();
            }
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateUI();
        }
    }

    updateUI() {
        // Panels
        this.panels.forEach((p, i) => {
            p.classList.toggle('active', i === (this.currentStep - 1));
        });

        // Nodes
        this.nodes.forEach((n, i) => {
            const stepNum = i + 1;
            n.classList.remove('active', 'completed');
            if (stepNum < this.currentStep) {
                n.classList.add('completed');
                n.innerHTML = '<i class="fas fa-check"></i>';
            } else if (stepNum === this.currentStep) {
                n.classList.add('active');
                n.innerHTML = stepNum;
            } else {
                n.innerHTML = stepNum;
            }
        });

        // Progress Line
        const progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
        this.progressLine.style.width = `${progress}%`;

        // Buttons
        this.btnPrev.style.display = this.currentStep === 1 ? 'none' : 'flex';
        this.btnNext.innerHTML = this.currentStep === this.totalSteps ? 
            'Complete Application <i class="fas fa-shield-alt ms-2"></i>' : 
            'Save & Continue <i class="fas fa-arrow-right ms-2"></i>';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    submitForm() {
        if (this.isSubmitting) return;
        this.isSubmitting = true;

        this.btnNext.disabled = true;
        this.btnNext.innerHTML = 'Securing your data... <i class="fas fa-spinner fa-spin ms-2"></i>';
        
        const formData = new FormData();
        formData.append('full_name', document.getElementById('fullName').value);
        formData.append('mobile', document.getElementById('phoneField').value);
        formData.append('pan', document.getElementById('panField').value);
        formData.append('employment_status', document.querySelector('input[name="emp"]:checked').value);
        formData.append('income', document.getElementById('income').value);
        formData.append('city', document.getElementById('city').value);
        formData.append('csrf_token', window.WAQT_CSRF_TOKEN || '');

        fetch('api/apply/step1_identity.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Smooth transition to OTP
                this.form.style.opacity = '0';
                this.form.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    window.location.href = "mobile-otp.php";
                }, 400);
            } else {
                throw new Error(data.message || 'Submission failed');
            }
        })
        .catch(err => {
            this.isSubmitting = false;
            this.btnNext.disabled = false;
            this.btnNext.innerHTML = 'Get Instant Cash <i class="fas fa-bolt ms-2"></i>';
            
            // Show meaningful error
            const errorPanel = document.createElement('div');
            errorPanel.className = 'alert alert-danger mt-3 shake';
            errorPanel.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> ${err.message}`;
            this.form.prepend(errorPanel);
            setTimeout(() => errorPanel.remove(), 5000);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new LoanApplication();
});
