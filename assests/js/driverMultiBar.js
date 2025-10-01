document.addEventListener('DOMContentLoaded', function() {
    // Set date constraints
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="license_expiry"]').min = today;
    
    // Set max DOB to 18 years ago
    const dobMax = new Date();
    dobMax.setFullYear(dobMax.getFullYear() - 18);
    document.querySelector('input[name="dob"]').max = dobMax.toISOString().split('T')[0];
    
    // Initialize form
    updateProgressBar(1);
    
    // Form navigation
    document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', function() {
            const currentStep = this.closest('.step');
            const nextStepId = this.getAttribute('data-next');
            
            // Validate current step before proceeding
            if (validateStep(currentStep)) {
                navigateToStep(nextStepId);
                updateProgressBar(nextStepId);
                
                // If moving to review step, populate review data
                if (nextStepId === '5') {
                    populateReviewData();
                }
            }
        });
    });
    
    document.querySelectorAll('.prev-step').forEach(button => {
        button.addEventListener('click', function() {
            const prevStepId = this.getAttribute('data-prev');
            navigateToStep(prevStepId);
            updateProgressBar(prevStepId);
        });
    });
    
    // File input handlers
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            handleFileInputChange(this);
        });
    });
    
    // Form submission - FIXED: Properly handle form submission
    const form = document.getElementById('driverSignupForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Only prevent default if we need to validate
            e.preventDefault();
            
            // Validate entire form
            if (validateForm()) {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> တင်နေသည်...';
                submitBtn.disabled = true;
                
                // Submit the form programmatically
                this.submit();
            }
        });
    }
    
    // Toggle between signup and login for riders
    document.getElementById('showLogin')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('riderSignupForm').style.display = 'none';
        document.getElementById('riderLoginForm').style.display = 'block';
    });
    
    document.getElementById('showSignup')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('riderLoginForm').style.display = 'none';
        document.getElementById('riderSignupForm').style.display = 'block';
    });
    
    // Add input event listeners for fields that need duplicate checking
    const fieldsToValidate = ['phone', 'nrc', 'license_number', 'vehicle_registration', 'engine_number'];
    
    fieldsToValidate.forEach(fieldName => {
        const field = document.querySelector(`input[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('blur', function() {
                validateField(fieldName, this.value);
            });
        }
    });
    
    // Function to validate field against database
    function validateField(fieldName, value) {
        if (!value) return;
        
        // Create form data
        const formData = new FormData();
        formData.append('field', fieldName);
        formData.append('value', value);
        
        // Send AJAX request to check for duplicates
        fetch('/auth/check_duplicate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger mt-1 small';
                errorDiv.id = `${fieldName}-error`;
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${data.message}`;
                
                // Remove existing error if any
                const existingError = document.getElementById(`${fieldName}-error`);
                if (existingError) {
                    existingError.remove();
                }
                
                // Add error message after field
                const field = document.querySelector(`input[name="${fieldName}"]`);
                field.classList.add('is-invalid');
                field.parentNode.appendChild(errorDiv);
            } else {
                // Remove error if exists
                const existingError = document.getElementById(`${fieldName}-error`);
                if (existingError) {
                    existingError.remove();
                }
                field.classList.remove('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    // Functions
    function navigateToStep(stepNumber) {
        // Hide all steps
        document.querySelectorAll('.step').forEach(step => {
            step.classList.remove('active');
        });
        
        // Show current step
        document.querySelector(`#step${stepNumber}`).classList.add('active');
    }
    
    function updateProgressBar(stepNumber) {
        const progressPercentage = ((stepNumber - 1) / 4) * 100;
        document.querySelector('.progress-bar').style.width = `${progressPercentage}%`;
        document.querySelector('.progress-bar').setAttribute('aria-valuenow', progressPercentage);
        
        // Update step indicators
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            indicator.classList.remove('active', 'completed');
            
            if (index < stepNumber - 1) {
                indicator.classList.add('completed');
            }
            
            if (index === stepNumber - 1) {
                indicator.classList.add('active');
            }
        });
    }
    
    function validateStep(stepElement) {
        let isValid = true;
        const inputs = stepElement.querySelectorAll('input, select, textarea');
        
        // Clear previous errors
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        // Validate each input
        inputs.forEach(input => {
            // Skip validation for file inputs that haven't been interacted with
            if (input.type === 'file') return;
            
            if (input.hasAttribute('required') && !input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            }
            
            if (input.type === 'tel' && input.pattern) {
                const regex = new RegExp(input.pattern);
                if (!regex.test(input.value)) {
                    input.classList.add('is-invalid');
                    isValid = false;
                }
            }
            
            if (input.type === 'number' && (input.min || input.max)) {
                const value = parseInt(input.value);
                if (value < parseInt(input.min) || value > parseInt(input.max)) {
                    input.classList.add('is-invalid');
                    isValid = false;
                }
            }
            
            if (input.type === 'date') {
                const dateValue = new Date(input.value);
                const today = new Date();
                
                if (input.name === 'dob' && dateValue > new Date(input.max)) {
                    input.classList.add('is-invalid');
                    isValid = false;
                }
                
                if (input.name === 'license_expiry' && dateValue < new Date(input.min)) {
                    input.classList.add('is-invalid');
                    isValid = false;
                }
            }
        });
        
        return isValid;
    }
    
    function validateForm() {
        let isValid = true;
        const errorList = document.getElementById('errorList');
        const errorAlert = document.getElementById('errorAlert');
        
        // Clear previous errors
        if (errorList) errorList.innerHTML = '';
        if (errorAlert) errorAlert.classList.add('d-none');
        
        // Validate all steps
        for (let i = 1; i <= 5; i++) {
            const step = document.getElementById(`step${i}`);
            if (step && !validateStep(step)) {
                isValid = false;
            }
        }
        
        // Validate file uploads
        const fileInputs = document.querySelectorAll('input[type="file"][required]');
        fileInputs.forEach(input => {
            if (input.multiple) {
                if (input.files.length === 0) {
                    document.getElementById(`${input.id}_info`).innerHTML = 
                        `<i class="fas fa-exclamation-circle me-1"></i> ဓာတ်ပုံတင်ရန် လိုအပ်ပါသည်`;
                    document.getElementById(`${input.id}_info`).classList.add('text-danger');
                    isValid = false;
                }
            } else {
                if (!input.files[0]) {
                    document.getElementById(`${input.id}_info`).innerHTML = 
                        `<i class="fas fa-exclamation-circle me-1"></i> ဓာတ်ပုံတင်ရန် လိုအပ်ပါသည်`;
                    document.getElementById(`${input.id}_info`).classList.add('text-danger');
                    isValid = false;
                }
            }
        });
        
        // Validate terms agreement
        const termsCheckbox = document.getElementById('terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            termsCheckbox.classList.add('is-invalid');
            isValid = false;
        }
        
        // Show errors if any
        if (!isValid) {
            if (errorAlert) {
                errorAlert.classList.remove('d-none');
                const errorItem = document.createElement('li');
                errorItem.textContent = 'ကျေးဇူးပြု၍ ဖော်ပြထားသော အမှားများကို ပြင်ဆင်ပါ';
                if (errorList) errorList.appendChild(errorItem);
                
                // Scroll to errors
                errorAlert.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        return isValid;
    }
    
    function handleFileInputChange(input) {
        const infoElement = document.getElementById(`${input.id}_info`);
        
        if (input.files.length > 0) {
            if (input.id === 'vehicle_photos') {
                infoElement.innerHTML = `<i class="fas fa-check-circle me-1"></i> ဓာတ်ပုံ ${input.files.length} ပုံ ရွေးချယ်ပြီးပါပြီ`;
            } else {
                const fileName = input.files[0].name;
                infoElement.innerHTML = `<i class="fas fa-check-circle me-1"></i> ${fileName.substring(0, 20)}${fileName.length > 20 ? '...' : ''}`;
            }
            infoElement.classList.remove('text-danger');
        } else {
            infoElement.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ဓာတ်ပုံတင်ရန် လိုအပ်ပါသည်`;
            infoElement.classList.add('text-danger');
        }
    }
    
    function populateReviewData() {
        document.getElementById('review-phone').textContent = document.querySelector('input[name="phone"]').value;
        document.getElementById('review-name').textContent = document.querySelector('input[name="name"]').value;
        
        const vehicleType = document.querySelector('select[name="vehicle_type"]');
        document.getElementById('review-vehicle-type').textContent = 
            vehicleType.options[vehicleType.selectedIndex].text;
        
        document.getElementById('review-nrc').textContent = document.querySelector('input[name="nrc"]').value;
        document.getElementById('review-license').textContent = document.querySelector('input[name="license_number"]').value;
        document.getElementById('review-registration').textContent = document.querySelector('input[name="vehicle_registration"]').value;
    }

}); 