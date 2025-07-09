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


// dopo il caricamento del DOM
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.show-btn').forEach(button => {
    const input = document.getElementById(button.getAttribute('data-target'));
    if (!input) return;
    button.addEventListener('click', () => {
      if (input.type === 'password') {
        input.type = 'text';
        button.textContent = 'Hide';
      } else {
        input.type = 'password';
        button.textContent = 'Show';
      }
    });
  });
});
