document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-dish-filter-form]');

    if (!form) {
        return;
    }

    const searchInput = form.querySelector('[data-dish-search]');
    const instantFields = form.querySelectorAll('[data-dish-instant]');

    let searchTimer = null;

    function submitFilterForm() {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }

        form.submit();
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            window.clearTimeout(searchTimer);

            searchTimer = window.setTimeout(() => {
                submitFilterForm();
            }, 450);
        });
    }

    instantFields.forEach((field) => {
        field.addEventListener('change', submitFilterForm);
    });
});
