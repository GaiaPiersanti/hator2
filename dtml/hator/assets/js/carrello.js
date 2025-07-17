document.addEventListener('DOMContentLoaded', () => {

  // prendi tutti i campi quantità

  document.querySelectorAll('input.quantity').forEach(input => {

    input.addEventListener('change', e => {

      // leggi la nuova quantità

      var newQty = parseInt(e.target.value, 10) || 1;

      // risali alla riga e prendi il variant-id

      const row = e.target.closest('tr');

      const variantId = row.dataset.variantId;
      const max = parseInt(e.target.max, 10) || 1;
      if (newQty > max) {
        newQty = max; // non superare il massimo
      };

      // redirect: lascia che cart.php rigeneri tutto

      window.location.href = 

        `index.php?page=cart`

        + `&action=update`

        + `&variant_id=${encodeURIComponent(variantId)}`

        + `&quantity=${encodeURIComponent(newQty)}`;

    });

  });

});