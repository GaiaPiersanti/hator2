document.addEventListener('DOMContentLoaded', function(){
  const cardInput   = document.getElementById('card-number');
  const expiryInput = document.getElementById('expiry-date');
  const cvvInput    = document.getElementById('cvv');
  const form        = document.querySelector('form.contact-form');
  const nameInput   = document.getElementById('name');

  // 1) Format credit card: space every 4 digits
  cardInput.addEventListener('input', (e) => {
    let v = e.target.value.replace(/\D/g, '').slice(0,16);
    // inserisci uno spazio ogni 4 cifre
    v = v.match(/.{1,4}/g)?.join(' ') || '';
    e.target.value = v;
  });

  // 2) Format expiry date: slash after 2 digits
  expiryInput.addEventListener('input', (e) => {
    let v = e.target.value.replace(/\D/g, '').slice(0,4);
    if (v.length > 2) {
      v = v.slice(0,2) + '/' + v.slice(2);
    }
    e.target.value = v;
  });

  // 3) CVV: allow only digits and max length 3
  cvvInput.addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0,3);
  });

  // 4) Final form validation on submit
  form.addEventListener('submit', function(e){
    const cvv = cvvInput.value;
    const exp = expiryInput.value;
    const card = cardInput.value.replace(/\s/g, '');
    const nameValue = nameInput.value.trim();

    let errors = [];

    if (nameInput && nameValue === '') {
      errors.push('Cardholder name is required.');
    }
    if (card.length !== 16) {
      errors.push('Credit card number must be 16 digits.');
    }
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(exp)) {
      errors.push('Expiration date must be in MM/YY format.');
    }
    if (cvv.length !== 3) {
      errors.push('CVV must be exactly 3 digits.');
    }

    if (errors.length) {
      e.preventDefault();
      alert(errors.join('\n'));
    }
  });
});