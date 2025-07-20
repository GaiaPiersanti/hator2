
    $(function(){
      var min = parseFloat('<[min_price]>'),
          max = parseFloat('<[max_price]>'),
          selMin = parseFloat('<[selected_min]>'),
          selMax = parseFloat('<[selected_max]>');
          console.log('PRICE SLIDER', { min, max, selMin, selMax });

      $("#slider-range").slider({
        range: true,
        min: min,
        max: max,
        values: [ selMin, selMax ],
        step: 0.01,
        slide: function(e, ui) {
          $("#amount").val("€" + ui.values[0].toFixed(2) + " - €" + ui.values[1].toFixed(2));
          $("input[name=price_min]").val(ui.values[0]);
          $("input[name=price_max]").val(ui.values[1]);
        }
      });

      // inizializzo la casella di testo
      $("#amount").val("€" + selMin.toFixed(2) + " - €" + selMax.toFixed(2));
    });
