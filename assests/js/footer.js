// Back to Top Button
        const backToTop = document.querySelector('.back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('active');
            } else {
                backToTop.classList.remove('active');
            }
        });

        // Smooth scroll for footer links
        document.querySelectorAll('.footer-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                // Add your smooth scroll logic here
                console.log('Navigating to:', link.getAttribute('href'));
            });
        });

        // Newsletter form submission
        const newsletterForm = document.querySelector('.newsletter-form');
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = newsletterForm.querySelector('input').value;
            console.log('Subscribed with:', email);
            // Add your form submission logic here
            newsletterForm.querySelector('input').value = '';
            newsletterForm.querySelector('button').innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                newsletterForm.querySelector('button').innerHTML = '<i class="fas fa-paper-plane"></i>';
            }, 2000);
        });