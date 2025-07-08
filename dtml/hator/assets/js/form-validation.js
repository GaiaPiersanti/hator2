document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.form-control');

    inputs.forEach(input => {
        input.addEventListener('input', function () {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
        });
    });

    const radios = document.querySelectorAll('input[name="user_title"]');
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            const errorDiv = document.querySelector('.text-danger');
            if (errorDiv) {
                errorDiv.remove();
            }
        });
    });
});