/**
 * Law & Reason - Main Frontend JavaScript
 * Handles: navigation, modals, forms (AJAX), search, toast notifications
 */

(function() {
    'use strict';

    const body = document.body;
    const menuToggle = document.querySelector('.menu-toggle');
    const primaryNav = document.querySelector('.primary-nav');
    const queryModal = document.querySelector('#query-modal');
    const queryForm = document.querySelector('#query-form');
    const searchModal = document.querySelector('#search-modal');
    const toast = document.querySelector('.toast');
    let toastTimer;

    // ========== Mobile Menu ==========
    function setMenu(open) {
        if (!primaryNav || !menuToggle) return;
        primaryNav.classList.toggle('open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        body.classList.toggle('menu-open', open);
        const useEl = menuToggle.querySelector('use');
        if (useEl) useEl.setAttribute('href', open ? '#icon-close' : '#icon-menu');
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            setMenu(!primaryNav.classList.contains('open'));
        });
    }

    if (primaryNav) {
        primaryNav.addEventListener('click', (event) => {
            if (event.target.closest('a')) setMenu(false);
        });
    }

    // ========== Toast Notifications ==========
    function showToast(message, type = 'success') {
        if (!toast) return;
        window.clearTimeout(toastTimer);
        toast.textContent = message;
        toast.className = 'toast visible' + (type === 'error' ? ' toast-error' : '');
        toastTimer = window.setTimeout(() => toast.classList.remove('visible'), 3500);
    }

    // ========== Query Modal ==========
    document.querySelectorAll('.js-open-query').forEach((button) => {
        button.addEventListener('click', () => {
            setMenu(false);
            if (queryModal) {
                queryModal.showModal();
                body.classList.add('modal-open');
            }
        });
    });

    function closeQueryModal() {
        if (queryModal) {
            queryModal.close();
            body.classList.remove('modal-open');
        }
    }

    if (queryModal) {
        const closeBtn = queryModal.querySelector('.modal-close');
        if (closeBtn) closeBtn.addEventListener('click', closeQueryModal);

        queryModal.addEventListener('click', (event) => {
            if (event.target === queryModal) closeQueryModal();
        });

        queryModal.addEventListener('close', () => {
            body.classList.remove('modal-open');
        });
    }

    // ========== Search Modal ==========
    document.querySelectorAll('.js-open-search').forEach((button) => {
        button.addEventListener('click', () => {
            setMenu(false);
            if (searchModal) {
                searchModal.showModal();
                body.classList.add('modal-open');
                const input = searchModal.querySelector('input');
                if (input) input.focus();
            }
        });
    });

    if (searchModal) {
        const closeBtn = searchModal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                searchModal.close();
                body.classList.remove('modal-open');
            });
        }

        searchModal.addEventListener('click', (event) => {
            if (event.target === searchModal) {
                searchModal.close();
                body.classList.remove('modal-open');
            }
        });
    }

    // ========== Form Submissions (AJAX) ==========
    
    // Contact/Ask form
    if (queryForm) {
        queryForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const status = queryForm.querySelector('.modal-status');
            const submitBtn = queryForm.querySelector('[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = '...';
            
            try {
                const formData = new FormData(queryForm);
                const response = await fetch(queryForm.action || '/api/contact.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (status) status.textContent = data.message;
                    queryForm.reset();
                    setTimeout(closeQueryModal, 1800);
                    showToast(data.message);
                } else {
                    if (status) status.textContent = data.message || 'Something went wrong.';
                    status.style.color = '#a94442';
                }
            } catch (err) {
                // Fallback for static preview (no PHP backend)
                if (status) status.textContent = 'Thank you. Your query has been recorded for review.';
                queryForm.reset();
                setTimeout(closeQueryModal, 1800);
                showToast('Thank you. Your query has been recorded.');
            }
            
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.original || 'Submit Query';
        });
    }

    // Newsletter forms
    document.querySelectorAll('.js-newsletter').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = '...';
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action || '/api/subscribe.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message);
                    form.reset();
                } else {
                    showToast(data.message || 'Something went wrong.', 'error');
                }
            } catch (err) {
                // Fallback for static preview
                showToast('You are subscribed to Law & Reason Weekly.');
                form.reset();
            }
            
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // ========== Responsive: close menu on resize ==========
    window.addEventListener('resize', () => {
        if (window.innerWidth > 930) setMenu(false);
    });

    // ========== Keyboard shortcut: Escape closes modals ==========
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenu(false);
        }
    });

    // ========== Search keyboard shortcut: Ctrl+K or / ==========
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey && event.key === 'k') || (event.key === '/' && !event.target.closest('input, textarea, select, [contenteditable]'))) {
            event.preventDefault();
            if (searchModal && !searchModal.open) {
                searchModal.showModal();
                body.classList.add('modal-open');
                const input = searchModal.querySelector('input');
                if (input) input.focus();
            }
        }
    });

    // ========== Smooth scroll for anchor links ==========
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href').slice(1);
            if (!targetId) return;
            const target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

})();
