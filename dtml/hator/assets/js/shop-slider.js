$(function(){
      var min = parseFloat('<[min_price]>'),
          globalMax = parseFloat('<[max_price]>'),
          selMin = parseFloat('<[selected_min]>'),
          selMax = parseFloat('<[selected_max]>');

      // Clamp handles to valid range
      selMin = Math.max(selMin, min);
      selMax = Math.min(selMax, globalMax);

      // Remove price_max param if it equals the global maximum to reset bound
      var params = new URLSearchParams(window.location.search);
      if (params.has('price_max') && Number(params.get('price_max')) >= globalMax) {
        params.delete('price_max');
        history.replaceState(null, '', window.location.pathname + (params.toString() ? '?' + params.toString() : ''));
      }

      console.log('PRICE SLIDER', { min, globalMax, selMin, selMax });

      $("#slider-range").slider({
        range: true,
        min: min,
        max: globalMax, // always use the real maximum value from DB
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
