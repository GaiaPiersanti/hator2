document.addEventListener('DOMContentLoaded', function() {
  const $cartBtnSelector = '.pro-cart';
  const modalEl = document.getElementById('product-window');

  modalEl.addEventListener('show.bs.modal', function(e) {
    const trigger   = e.relatedTarget;
    const $modal    = $(this);
    const $btn      = $(trigger);

    // leggi raw category
    const rawCat    = $btn.data('category') || '';

    // applica la regola: se non è “unisex” metti “For ”
    const displayCat = rawCat.toLowerCase() === 'unisex'
      ? rawCat
      : `for ${rawCat}`;

    // leggi gli altri data-…
    const title     = $btn.data('title')    || '';
    const desc      = $btn.data('desc')     || '';
    const type      = $btn.data('type')     || '';
    const brand     = $btn.data('brand')    || '';
    const family    = $btn.data('family')   || '';
    const images    = $btn.data('images')   || [];
    const variants  = $btn.data('variants') || [];

    //
    // 1) Titolo e descrizione
    //
    $modal.find('.product-header').text(title);
    $modal.find('.pro-desc-details').text(desc);

    //
    // 1bis) Meta‐info
    //
    $modal.find('.product-type').text(type);
    $modal.find('.product-category').text(displayCat);
    $modal.find('.product-brand').text(brand);
    $modal.find('.product-family').text(family);

    //
    // 2) Costruisci la gallery e la thumb-carousel
    //
    const $tabContent = $modal.find('.tab-content').empty();
    const $thumbMenu  = $modal.find('.thumb-menu').empty();

    images.forEach((imgUrl, i) => {
      const paneId = `pro-${i+1}`;
      // il riquadro grande
      $tabContent.append(`
        <div id="${paneId}" class="tab-pane fade${i===0?' show active':''} my-modal-pane">
          <a data-fancybox="images" href="${imgUrl}">
            <img class="pro-img-modal" src="${imgUrl}" alt="product-view">
          </a>
        </div>
      `);
      // la mini-thumb
      $thumbMenu.append(`
        <a class="${i===0?'active':''}" data-bs-toggle="tab" href="#${paneId}">
          <img class="pro-img-modal-mini" src="${imgUrl}" alt="product-thumbnail">
        </a>
      `);
    });

    // (ri)inizializza OwlCarousel
    if ($thumbMenu.hasClass('owl-carousel')) {
      $thumbMenu.trigger('destroy.owl.carousel'); // se già inizializzato, distruggilo
      $thumbMenu.owlCarousel({
        items: 4,
        loop: false,
        margin: 10,
        nav: true,
        responsive: {
          0:   { items: 2 },
          576: { items: 3 },
          768: { items: 4 }
        }
      });
    }

    //
    // 3) Prezzo, stock, select delle varianti e input quantità
    //
    const $price     = $modal.find('.pro-thumb-price .price');
    const $inStock   = $modal.find('.in-stock');
    const $sizeSelect= $modal.find('.product-size select').empty();
    const $quantityInput = $modal.find('.box-quantity').empty();
   

    // funzione di aggiornamento dettagli
    function updateDetails(v) {
      $price.text('€' + parseFloat(v.price).toFixed(2));
      $inStock.html(
        v.stock > 0
          ? `<i class="ion-checkmark-round"></i> ${v.stock} in stock`
          : 'Out of stock'
      );
      const stock = v.stock || 2;
      $quantityInput.html('<input class="quantity form-control" id="quantity" type="number" min="1" max="'+ stock +'" value="1">');
      // Disable/enable Add to Cart button based on stock
      const $cartBtn = $modal.find($cartBtnSelector);
      if (v.stock > 0) {
        $cartBtn.prop('disabled', false).removeClass('disabled').css({
          'background-color': '',
          'opacity': '',
          'cursor': ''
        });
      } else {
        $cartBtn.prop('disabled', true).addClass('disabled').css({
          'background-color': '#ccc',
          'opacity': '0.6',
          'cursor': 'not-allowed'
        });
      }
    }

    // riempi il menu size
    variants.forEach(v => {
      $sizeSelect.append(
        `<option value="${v.variant_id}">${v.size_ml} ML</option>`
      );
    });
    // primo aggiornamento
    if (variants.length) updateDetails(variants[0]);

    // al cambio di select
    $sizeSelect.off('change').on('change', function() {
      const selId = $(this).val();
      const chosen = variants.find(x => x.variant_id == selId);
      if (chosen) updateDetails(chosen);
    });

    //
    // 4) (opzionale) aggiorna anche il link “add to cart” nel footer
    //
    $modal
      .find('.pro-cart')  // se il bottone ha classe .pro-cart
      .off('click')
      .on('click', function() {
        const selectedId = $sizeSelect.val();
        const $selectedQuant = $modal.find('.box-quantity input');
        const selectedQuant = $selectedQuant.val();
        window.location.href =
          `index.php?page=cart&action=add&variant_id=${selectedId}&quantity=${selectedQuant}`;
      });
  });
});