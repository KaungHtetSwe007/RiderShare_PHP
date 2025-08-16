 // Animated Counter
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;
        
        function animateCounters() {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-count');
                const count = +counter.innerText;
                const increment = target / speed;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(animateCounters, 1);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            });
        }
        
        // Start counters when section is in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('.stats-section').forEach(section => {
            observer.observe(section);
        });
        
        // Animate elements on scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.feature-card, .step-card, .ride-card, .testimonial-card');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };
        
        // Set initial state
        document.querySelectorAll('.feature-card, .step-card, .ride-card, .testimonial-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
        });
        
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    
        // rider signup login အတွက်  js

        document.getElementById("showLogin").addEventListener("click", function(e) {
        e.preventDefault();
        document.getElementById("riderSignupForm").style.display = "none";
        document.getElementById("riderLoginForm").style.display = "block";
    });

    document.getElementById("showSignup").addEventListener("click", function(e) {
        e.preventDefault();
        document.getElementById("riderLoginForm").style.display = "none";
        document.getElementById("riderSignupForm").style.display = "block";
    });

   