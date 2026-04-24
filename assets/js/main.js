document.addEventListener('DOMContentLoaded', function() {

    /* ─── SCROLL PROGRESS BAR ─── */
    const scrollProgress = document.querySelector('.scroll-progress');
    if (scrollProgress) {
        window.addEventListener('scroll', () => {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            scrollProgress.style.width = (scrollTop / docHeight * 100) + '%';
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

    /* ─── HERO PARTICLES (cont.) ─── */
    const particleContainer = document.querySelector('.hero-particles');
    if (particleContainer) {
        for (let i = 0; i < 12; i++) {
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
        if (qtyInput && waBtn && window.whConfig) {
            const qty = qtyInput.value;
            const productName = waBtn.getAttribute('data-product');
            const msg = window.whConfig.orderMsg.replace('{product}', productName).replace('{quantity}', qty);
            waBtn.href = 'https://wa.me/' + window.whConfig.number + '?text=' + encodeURIComponent(msg);
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

    /* ─── PAGE PRELOADER ─── */
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            setTimeout(() => { preloader.classList.add('hidden'); }, 1600);
        });
        // Fallback: hide after 4s max
        setTimeout(() => { if (preloader) preloader.classList.add('hidden'); }, 4000);
    }

    /* ─── PAGE TRANSITION ─── */
    document.querySelector('.admin-content, main, .section:first-of-type')?.classList.add('page-transition');

    /* ─── DARK MODE TOGGLE ─── */
    const darkToggle = document.querySelector('.dark-toggle');
    if (darkToggle) {
        // Restore preference
        if (localStorage.getItem('wh_dark') === '1') {
            document.body.classList.add('dark-mode');
            darkToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        darkToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('wh_dark', isDark ? '1' : '0');
            darkToggle.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
    }

    /* ─── SITE TOAST SYSTEM ─── */
    window.siteToast = function(type, title, message) {
        let container = document.querySelector('.site-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'site-toast-container';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = 'site-toast toast-' + type;
        const icon = type === 'ok' ? 'fa-check-circle' : 'fa-exclamation-circle';
        toast.innerHTML = '<div class="site-toast-icon"><i class="fas ' + icon + '"></i></div>' +
            '<div class="site-toast-body"><strong>' + title + '</strong><small>' + (message || '') + '</small></div>' +
            '<button class="site-toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>';
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 4000);
    };

    /* ─── FORM SUBMIT LOADING STATE ─── */
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (btn && btn.tagName === 'BUTTON') {
                btn.classList.add('loading');
                const text = btn.innerHTML;
                btn.setAttribute('data-original', text);
                btn.innerHTML = '<span class="btn-text">' + text + '</span>';
            }
        });
    });

    /* ─── COOKIE CONSENT ─── */
    const cookieBanner = document.querySelector('.cookie-banner');
    if (cookieBanner && !localStorage.getItem('wh_cookies')) {
        setTimeout(() => { cookieBanner.classList.add('show'); }, 2000);
        cookieBanner.querySelector('.cookie-btn-accept')?.addEventListener('click', () => {
            localStorage.setItem('wh_cookies', 'accepted');
            cookieBanner.classList.remove('show');
        });
        cookieBanner.querySelector('.cookie-btn-decline')?.addEventListener('click', () => {
            localStorage.setItem('wh_cookies', 'declined');
            cookieBanner.classList.remove('show');
        });
    }

    /* ─── IMAGE LAZY LOADING ─── */
    document.querySelectorAll('img[data-src]').forEach(img => {
        const imgObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.src = entry.target.dataset.src;
                    entry.target.removeAttribute('data-src');
                    imgObs.unobserve(entry.target);
                }
            });
        }, { rootMargin: '100px' });
        imgObs.observe(img);
    });

});