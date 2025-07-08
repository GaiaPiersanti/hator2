//script per il tasto show pw, 
// Funziona con qualsiasi bottone show-btn che ha un attributo data-target.
document.addEventListener('DOMContentLoaded', function () {
    const showBtns = document.querySelectorAll('.show-btn');

    showBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = 'Hide';
            } else {
                input.type = 'password';
                this.textContent = 'Show';
            }
        });
    });
});