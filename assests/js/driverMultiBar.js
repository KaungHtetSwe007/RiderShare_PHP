document.addEventListener('DOMContentLoaded', function() {
            // Rider login/signup toggle
            const showLogin = document.getElementById('showLogin');
            const showSignup = document.getElementById('showSignup');
            const riderSignupForm = document.getElementById('riderSignupForm');
            const riderLoginForm = document.getElementById('riderLoginForm');
            
            if (showLogin && showSignup) {
                showLogin.addEventListener('click', function(e) {
                    e.preventDefault();
                    riderSignupForm.style.display = 'none';
                    riderLoginForm.style.display = 'block';
                });
                
                showSignup.addEventListener('click', function(e) {
                    e.preventDefault();
                    riderLoginForm.style.display = 'none';
                    riderSignupForm.style.display = 'block';
                });
            }
            
            // Driver multi-step form functionality
            const steps = document.querySelectorAll('.step');
            const stepIndicators = document.querySelectorAll('.step-indicator');
            const progressBar = document.querySelector('.progress-bar');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            const driverForm = document.getElementById('driverSignupForm');
            
            // Function to update progress
            function updateProgress(currentStep) {
                // Calculate progress percentage
                const progress = (currentStep / steps.length) * 100;
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
                
                // Update step indicators
                stepIndicators.forEach((indicator, index) => {
                    indicator.classList.remove('active', 'completed');
                    if (index < currentStep - 1) {
                        indicator.classList.add('completed');
                    } else if (index === currentStep - 1) {
                        indicator.classList.add('active');
                    }
                });
            }
            
            // Next button click handler
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = this.closest('.step');
                    const nextStepId = this.getAttribute('data-next');
                    const nextStep = document.getElementById(`step${nextStepId}`);
                    
                    // Validate current step before proceeding
                    let isValid = true;
                    const inputs = currentStep.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        if (!input.checkValidity()) {
                            isValid = false;
                            input.reportValidity();
                        }
                    });
                    
                    if (!isValid) return;
                    
                    // Switch steps
                    currentStep.classList.remove('active');
                    nextStep.classList.add('active');
                    
                    // Update progress
                    updateProgress(parseInt(nextStepId));
                });
            });
            
            // Previous button click handler
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = this.closest('.step');
                    const prevStepId = this.getAttribute('data-prev');
                    const prevStep = document.getElementById(`step${prevStepId}`);
                    
                    // Switch steps
                    currentStep.classList.remove('active');
                    prevStep.classList.add('active');
                    
                    // Update progress
                    updateProgress(parseInt(prevStepId));
                });
            });
            
            // Form submission handler
            if (driverForm) {
                driverForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Show success message
                    alert('မှတ်ပုံတင်မှုအောင်မြင်ပါသည်! အတည်ပြုချက်အတွက် 24 နာရီအတွင်း ဆက်သွယ်ပေးပါမည်။');
                    
                    // Reset form (in a real app, you would submit to server)
                    driverForm.reset();
                    
                    // Reset to first step
                    steps.forEach(step => step.classList.remove('active'));
                    steps[0].classList.add('active');
                    updateProgress(1);
                });
            }
            
            // Initialize progress
            if (steps.length > 0) {
                updateProgress(1);
            }
        });

// Upload Button Functionality အတွက်
        document.addEventListener('DOMContentLoaded', function() {
    // Make document upload buttons work
    document.querySelectorAll('.document-upload button').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.document-upload').querySelector('input[type="file"]');
            if (input) {
                input.click();
            }
        });
    });
    
    // Show filename when file is selected
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const parent = this.closest('.document-upload');
            if (parent) {
                const fileName = this.files[0]?.name || 'No file selected';
                const fileNameDisplay = parent.querySelector('.file-name') || document.createElement('div');
                
                if (!parent.querySelector('.file-name')) {
                    fileNameDisplay.className = 'file-name small mt-2 text-success';
                    parent.appendChild(fileNameDisplay);
                }
                
                fileNameDisplay.textContent = fileName;
            }
        });
    });
});