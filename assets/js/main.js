document.addEventListener('DOMContentLoaded', function() {

    /* ─── PRELOADER ─── */
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            setTimeout(() => { preloader.classList.add('hidden'); setTimeout(() => preloader.remove(), 800); }, 800);
        });
        setTimeout(() => { preloader.classList.add('hidden'); }, 4000);
    }

    /* ─── SCROLL PROGRESS BAR ─── */
    const scrollProgress = document.querySelector('.scroll-progress');
    if (scrollProgress) {
        window.addEventListener('scroll', () => {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            scrollProgress.style.width = (scrollTop / docHeight * 100) + '%';
        });
    }

    /* ─── CUSTOM CURSOR ─── */
    const cursorDot = document.querySelector('.cursor-dot');
    const cursorRing = document.querySelector('.cursor-ring');
    if (cursorDot && cursorRing && window.innerWidth > 992) {
        let mouseX = 0, mouseY = 0, ringX = 0, ringY = 0;
        document.addEventListener('mousemove', e => {
            mouseX = e.clientX; mouseY = e.clientY;
            cursorDot.style.left = mouseX + 'px';
            cursorDot.style.top = mouseY + 'px';
        });
        function animateCursor() {
            ringX += (mouseX - ringX) * 0.15;
            ringY += (mouseY - ringY) * 0.15;
            cursorRing.style.left = ringX + 'px';
            cursorRing.style.top = ringY + 'px';
            requestAnimationFrame(animateCursor);
        }
        animateCursor();
        document.querySelectorAll('a, button, .btn, .product-card, .category-card, .feature-card').forEach(el => {
            el.addEventListener('mouseenter', () => { cursorRing.classList.add('hover'); cursorDot.style.transform = 'scale(2)'; });
            el.addEventListener('mouseleave', () => { cursorRing.classList.remove('hover'); cursorDot.style.transform = 'scale(1)'; });
        });
    }

    /* ─── NAVBAR ─── */
    const navbar = document.querySelector('.navbar');
    function handleScroll() {
        if (window.scrollY > 60) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
        const btn = document.querySelector('.back-to-top');
        if (btn) {
            if (window.scrollY > 500) btn.classList.add('visible');
            else btn.classList.remove('visible');
        }
    }
    window.addEventListener('scroll', handleScroll);
    handleScroll();

    /* ─── MOBILE MENU ─── */
    const toggle = document.querySelector('.navbar-toggle');
    const menu = document.querySelector('.navbar-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', () => { toggle.classList.toggle('active'); menu.classList.toggle('open'); });
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => { toggle.classList.remove('active'); menu.classList.remove('open'); });
        });
        document.querySelectorAll('.has-dropdown > a').forEach(item => {
            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) { e.preventDefault(); this.parentElement.classList.toggle('active'); }
            });
        });
    }

    /* ─── SCROLL ANIMATIONS (IntersectionObserver) ─── */
    const animClasses = ['.animate-on-scroll', '.animate-left', '.animate-right', '.animate-scale', '.animate-rotate'];
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
    animClasses.forEach(cls => document.querySelectorAll(cls).forEach(el => observer.observe(el)));

    /* ─── STAGGERED CHILDREN ANIMATION ─── */
    document.querySelectorAll('[data-stagger]').forEach(parent => {
        const children = parent.children;
        const staggerObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    Array.from(children).forEach((child, i) => {
                        child.style.animation = `slideInStagger 0.7s cubic-bezier(0.34,1.56,0.64,1) ${i * 0.12}s both`;
                    });
                    staggerObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        staggerObserver.observe(parent);
    });

    /* ─── BACK TO TOP ─── */
    const backTop = document.querySelector('.back-to-top');
    if (backTop) backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    /* ─── SMOOTH SCROLL ─── */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* ─── COUNTER ANIMATION ─── */
    function animateCounters() {
        document.querySelectorAll('.counter').forEach(counter => {
            if (counter.dataset.animated) return;
            counter.dataset.animated = 'true';
            const target = +counter.getAttribute('data-target');
            const duration = 2200;
            const startTime = performance.now();
            function easeOutExpo(t) { return t === 1 ? 1 : 1 - Math.pow(2, -10 * t); }
            function update(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const val = Math.floor(easeOutExpo(progress) * target);
                counter.textContent = val + '+';
                if (progress < 1) requestAnimationFrame(update);
                else counter.textContent = target + '+';
            }
            requestAnimationFrame(update);
        });
    }
    const counterSection = document.querySelector('.hero-stats');
    if (counterSection) {
        const counterObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if (entry.isIntersecting) { animateCounters(); counterObs.unobserve(entry.target); } });
        }, { threshold: 0.4 });
        counterObs.observe(counterSection);
    }

    /* ─── TILT EFFECT ON CARDS ─── */
    if (window.innerWidth > 992) {
        document.querySelectorAll('.feature-card, .engagement-card, .product-card').forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / centerY * -5;
                const rotateY = (x - centerX) / centerX * 5;
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-12px) scale(1.01)`;
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    /* ─── MAGNETIC BUTTONS ─── */
    if (window.innerWidth > 992) {
        document.querySelectorAll('.btn, .nav-cta').forEach(btn => {
            btn.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                this.style.transform += ` translate(${x * 0.15}px, ${y * 0.15}px)`;
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    /* ─── HERO PARTICLES ─── */
    const particleContainer = document.querySelector('.hero-particles');
    if (particleContainer) {
        for (let i = 0; i < 30; i++) {
            const p = document.createElement('div');
            p.className = 'hero-particle';
            p.style.left = Math.random() * 100 + '%';
            p.style.top = Math.random() * 100 + '%';
            p.style.width = (Math.random() * 4 + 2) + 'px';
            p.style.height = p.style.width;
            p.style.opacity = Math.random() * 0.5 + 0.2;
            p.style.animation = `particleFloat ${Math.random() * 15 + 10}s linear ${Math.random() * 10}s infinite`;
            particleContainer.appendChild(p);
        }
    }

    /* ─── PARALLAX ON HERO SHAPES ─── */
    if (window.innerWidth > 992) {
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 2;
            const y = (e.clientY / window.innerHeight - 0.5) * 2;
            document.querySelectorAll('.hero-shape').forEach((shape, i) => {
                const speed = (i + 1) * 12;
                shape.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    }

    /* ─── QUANTITY SELECTOR ─── */
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            let val = parseInt(input.value) || 1;
            if (this.classList.contains('qty-minus')) { if (val > 1) input.value = val - 1; }
            else { input.value = val + 1; }
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

    /* ─── RIPPLE EFFECT ON BUTTONS ─── */
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.cssText = `position:absolute;border-radius:50%;background:rgba(255,255,255,0.3);width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px;animation:ripple 0.6s ease-out;pointer-events:none;`;
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    /* ─── TEXT REVEAL ON SECTION HEADERS ─── */
    document.querySelectorAll('.section-header h2').forEach(heading => {
        const textObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s cubic-bezier(0.34,1.56,0.64,1) forwards';
                    textObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        textObs.observe(heading);
    });

});