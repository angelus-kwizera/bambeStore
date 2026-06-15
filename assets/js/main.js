document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initQuantityControls();
});

function initMobileNav() {
    const toggle = document.getElementById('navToggle');
    const nav = document.getElementById('mainNav');

    if (!toggle || !nav) return;

    toggle.addEventListener('click', () => {
        nav.classList.toggle('nav--open');
        toggle.classList.toggle('nav-toggle--active');
    });

    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target) && !toggle.contains(e.target)) {
            nav.classList.remove('nav--open');
            toggle.classList.remove('nav-toggle--active');
        }
    });
}

function initQuantityControls() {
    document.querySelectorAll('.quantity-selector__controls').forEach((controls) => {
        const input = controls.querySelector('.qty-input');
        if (!input) return;

        const min = parseInt(input.min, 10) || 1;
        const max = parseInt(input.max, 10) || 999;

        controls.querySelectorAll('.qty-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                let value = parseInt(input.value, 10) || min;

                if (btn.dataset.action === 'increase') {
                    value = Math.min(value + 1, max);
                } else {
                    value = Math.max(value - 1, min);
                }

                input.value = value;
            });
        });

        input.addEventListener('change', () => {
            let value = parseInt(input.value, 10) || min;
            value = Math.max(min, Math.min(value, max));
            input.value = value;
        });
    });
}
