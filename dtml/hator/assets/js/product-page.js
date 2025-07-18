$(function(){
  const p = PRODUCT;

  // 1) Gallery di immagini
  const images = [p.img1_url, p.img2_url, p.img3_url, p.img4_url].filter(Boolean);
  const $tabContent = $('#myTabContent').empty();
  const $thumbMenu  = $('#myTab').empty();

  images.forEach((url, i) => {
    const paneId = 'thumb'+(i+1);
    // main pane
    $tabContent.append(`
      <div class="tab-pane fade${i===0?' show active':''}" 
           id="${paneId}" role="tabpanel" 
           aria-labelledby="${paneId}-tab">
        <a data-fancybox="images" href="${url}">
          <img src="${url}" alt="product-view">
        </a>
      </div>
    `);
    // thumbnail button
    $thumbMenu.append(`
      <li class="nav-item" role="presentation">
        <button class="nav-link${i===0?' active':''}"
                id="${paneId}-tab"
                data-bs-toggle="tab"
                data-bs-target="#${paneId}"
                type="button"
                role="tab"
                aria-controls="${paneId}"
                aria-selected="${i===0}">
          <img src="${url}" alt="product-thumbnail">
        </button>
      </li>
    `);
  });

  // inizializza OwlCarousel sulla thumb-menu se serve
  if ($thumbMenu.hasClass('owl-carousel')) {
    $thumbMenu.trigger('destroy.owl.carousel')
             .owlCarousel({ items:4, loop:false, margin:10, nav:true });
  }

  // 2) Titolo e descrizioni
  $('.product-header').text(p.name);
  $('.pro-desc-details').text(p.short_description);
  $('.long-desc').html(p.long_description.replace(/\n/g,'<br>'));

  // 3) Meta-info
  $('.product-type').text(p.type_name);
  const rawCat = p.category_name;
  const displayCat = rawCat.toLowerCase()==='unisex' ? rawCat : `For ${rawCat}`;
  $('.product-category').text(displayCat);
  $('.product-brand').text(p.brand_name);
  $('.product-family').text(p.family_name);

  // 4) Prezzo, stock, variant select, quantity e add-to-cart
  const variants = p.variants;
  const $sizeSel = $('#variant-select').empty();
  const $qtyWrap = $('.box-quantity').empty();
  variants.forEach(v =>
    $sizeSel.append(
      `<option value="${v.variant_id}" 
               data-price="${v.price}" 
               data-stock="${v.stock}">
         ${v.size_ml} ML
       </option>`
    )
  );

  const $priceEl = $('.price');
  const $stockEl = $('.in-stock');
  // const $qtyIn   = $('#quantity'); // removed as per instructions
  const $btn     = $('#add-to-cart-btn');

  function updateDetails(v) {
    // prezzo
    $priceEl.text('€' + parseFloat(v.price).toFixed(2).replace(',', ','));
    // stock display
    $stockEl.html(
      v.stock > 0
        ? `<i class="ion-checkmark-round"></i> ${v.stock} in stock`
        : 'Out of stock'
    );
    // ricostruisci l'input quantity con max uguale allo stock
    const maxQty = v.stock || 1;
    $qtyWrap.html(
      `<input class="quantity form-control" id="quantity" type="number" min="1" max="${maxQty}" value="1">`
    );
  }

  // prima istanzia
  updateDetails(variants[0]);

  // al cambio di variante
  $sizeSel.on('change', () => {
    const sel = variants.find(x => x.variant_id == $sizeSel.val());
    if (sel) updateDetails(sel);
  });

  // click add-to-cart
  $btn.on('click', () => {
    let vid = $sizeSel.val();
    const $currentQty = $('.box-quantity input');
    let qty = parseInt($currentQty.val(), 10) || 1;
    const max = parseInt($currentQty.attr('max'), 10);
    if (qty > max) qty = max;
    window.location.href = 
      `index.php?page=cart&action=add&variant_id=${vid}&quantity=${qty}`;
  });
});