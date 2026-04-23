document.addEventListener('DOMContentLoaded', function() {
    // Preloader
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', function() {
            preloader.classList.add('hidden');
            setTimeout(() => preloader.remove(), 500);
        });
        setTimeout(() => { preloader.classList.add('hidden'); }, 3000);
    }

    // Navbar scroll
    const navbar = document.querySelector('.navbar');
    function handleScroll() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        // Back to top
        const btn = document.querySelector('.back-to-top');
        if (btn) {
            if (window.scrollY > 400) btn.classList.add('visible');
            else btn.classList.remove('visible');
        }
    }
    window.addEventListener('scroll', handleScroll);
    handleScroll();

    // Mobile menu
    const toggle = document.querySelector('.navbar-toggle');
    const menu = document.querySelector('.navbar-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            toggle.classList.toggle('active');
            menu.classList.toggle('open');
        });
        // Close on link click
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                toggle.classList.remove('active');
                menu.classList.remove('open');
            });
        });
        // Dropdown toggle on mobile
        document.querySelectorAll('.has-dropdown > a').forEach(item => {
            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    this.parentElement.classList.toggle('active');
                }
            });
        });
    }

    // Scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

    // Back to top click
    const backTop = document.querySelector('.back-to-top');
    if (backTop) {
        backTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Smooth scroll for anchors
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    // Counter animation
    function animateCounters() {
        document.querySelectorAll('.counter').forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    counter.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current) + '+';
                }
            }, 16);
        });
    }

    const counterSection = document.querySelector('.hero-stats');
    if (counterSection) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counterObserver.observe(counterSection);
    }

    // Quantity selector
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            let val = parseInt(input.value) || 1;
            if (this.classList.contains('qty-minus')) {
                if (val > 1) input.value = val - 1;
            } else {
                input.value = val + 1;
            }
            // Update WhatsApp link
            updateWhatsAppLink();
        });
    });

    function updateWhatsAppLink() {
        const qtyInput = document.querySelector('.qty-input');
        const waBtn = document.querySelector('.btn-whatsapp-order');
        if (qtyInput && waBtn) {
            const qty = qtyInput.value;
            const productName = waBtn.getAttribute('data-product');
            const msg = encodeURIComponent('Bonjour, je souhaite commander ' + qty + ' unité(s) de: ' + productName);
            waBtn.href = 'https://wa.me/212652020702?text=' + msg;
        }
    }
});