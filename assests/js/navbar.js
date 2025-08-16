
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            navbar.classList.toggle('navbar-scrolled', window.scrollY > 10);
        });

        // Simulate notification (for demo)
        function simulateNotification() {
            const logo = document.querySelector('.navbar-brand');
            logo.classList.add('notification-pulse');
            
            setTimeout(() => {
                logo.classList.remove('notification-pulse');
            }, 5000);
        }

        // Trigger notification after 3 seconds (demo)
        setTimeout(simulateNotification, 3000);

        //profile section
   
        const profileDropdown = document.getElementById('profileDropdown');
        
        profileDropdown.addEventListener('click', function() {
            this.classList.toggle('active');
        });
        
        // Demo functionality for profile options
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const text = this.querySelector('i').nextSibling.textContent.trim();
                alert(`Selected: ${text}`);
            });
        });

        // navbar profile popup js-------------------------------------------------------------------------------------

            // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const navbar = document.querySelector('.navbar');
        navbar.classList.toggle('navbar-scrolled', window.scrollY > 10);
    });

    // Profile dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.profile-dropdown');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target)) {
                    const content = dropdown.querySelector('.profile-dropdown-content');
                    if (content) content.style.display = 'none';
                }
            });
        });
        
        // Toggle dropdown on mobile
        const profileBtns = document.querySelectorAll('.profile-btn');
        profileBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const content = this.nextElementSibling;
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                } else {
                    content.style.display = 'block';
                }
            });
        });
    });