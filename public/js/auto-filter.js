'use strict';

(() => {
    const forms = document.querySelectorAll('[data-auto-filter-form]');

    forms.forEach((form) => {
        let timer;

        form.querySelectorAll('input[type="search"]').forEach((input) => {
            input.addEventListener('input', () => {
                window.clearTimeout(timer);

                timer = window.setTimeout(() => {
                    form.submit();
                }, 400);
            });
        });
    });
})();