jQuery(($) => {
  // wc_city_select_params is required to continue, ensure the object exists
  // wc_country_select_params is used for select2 texts. This one is added by WC
  if (typeof wc_country_select_params === 'undefined' || typeof wc_city_select_params === 'undefined') {
    return false;
  }

  function getEnhancedSelectFormatString() {
    const formatString = {
      formatMatches: function (matches) {
        if (1 === matches) {
          return wc_country_select_params.i18n_matches_1;
        }

        return wc_country_select_params.i18n_matches_n.replace('%qty%', matches);
      },
      formatNoMatches: function () {
        return wc_country_select_params.i18n_no_matches;
      },
      formatAjaxError: function () {
        return wc_country_select_params.i18n_ajax_error;
      },
      formatInputTooShort: function (input, min) {
        const number = min - input.length;

        if (1 === number) {
          return wc_country_select_params.i18n_input_too_short_1;
        }

        return wc_country_select_params.i18n_input_too_short_n.replace('%qty%', number);
      },
      formatInputTooLong: function (input, max) {
        const number = input.length - max;

        if (1 === number) {
          return wc_country_select_params.i18n_input_too_long_1;
        }

        return wc_country_select_params.i18n_input_too_long_n.replace('%qty%', number);
      },
      formatSelectionTooBig: function (limit) {
        if (1 === limit) {
          return wc_country_select_params.i18n_selection_too_long_1;
        }

        return wc_country_select_params.i18n_selection_too_long_n.replace('%qty%', limit);
      },
      formatLoadMore: function () {
        return wc_country_select_params.i18n_load_more;
      },
      formatSearching: function () {
        return wc_country_select_params.i18n_searching;
      }
    };

    return formatString;
  }

  // Select2 Enhancement if it exists
  if ($().selectWoo) {
    const wc_city_select_select2 = function () {
      $('select.city_select').each(function () {
        let select2_args = $.extend({
          placeholderOption: 'first',
          placeholder: $(this).attr('placeholder'),
          width: '100%'
        }, getEnhancedSelectFormatString());
        $(this).selectWoo(select2_args);
      });
    };

    wc_city_select_select2();

    $(document.body).bind('city_to_select', wc_city_select_select2);
  }

  /* City select boxes */
  const cities = JSON.parse(
    wc_city_select_params.cities || '[]'
  );

  const stateSelector = '[name=billing_state], [name=shipping_state], [name=calc_shipping_state]';
  const countrySelector = '[name=billing_country], [name=shipping_country], [name=calc_shipping_country]';
  const citySelector = '[name=billing_city], [name=shipping_city], [name=calc_shipping_city]';
  const sectionWrapperSelector = 'div[class*="-fields"], div.cart-collaterals, div[class^="wfacp_divider_"]';

  $(document.body).on('country_to_state_changing', function (e, country, $container) {
    const $statebox = $container.find(stateSelector);
    const state = $statebox.val();

    $(document.body).trigger('state_changing', [country, state, $container]);
  });

  $(document.body).on('change', stateSelector, function () {
    const $container = $(this).closest(sectionWrapperSelector);
    const country = $container.find(countrySelector).val() || 'RO';
    const state = $(this).val();

    $(document.body).trigger('state_changing', [country, state, $container]);
  });

  $(document.body).on('state_changing', function (e, country, state, $container) {
    const $citybox = $container.find(citySelector);
    if (cities[country]) {
      if (cities[country] instanceof Array) {
        cityToSelect($citybox, cities[country]);
      } else if (state) {
        if (cities[country][state]) {
          cityToSelect($citybox, cities[country][state]);
        } else {
          cityToInput($citybox);
        }
      } else {
        disableCity($citybox);
      }
    } else {
      cityToInput($citybox);
    }
  });

  $(document.body).on('change', '[name=payment_method], [name=billing_city], [name=shipping_city], [name=billing_postcode], [name=shipping_postcode], [name=curiero_sameday_lockers], [name=curiero_fan_collectpoint], [name=curiero_cargus_pudo], [name=curiero_dpd_box], [name=curiero_fan_fanbox], [name=curiero_mygls_box], [name=curiero_cargus_locker], [name=curiero_innoship_locker]', () => $(document.body).trigger('update_checkout'));

  /* Ajax replaces .cart_totals (child of .cart-collaterals) on shipping calculator */
  if ($('.cart-collaterals').length && $('#calc_shipping_state').length) {
    const calc_observer = new MutationObserver(() => $('#calc_shipping_state').change() );
    calc_observer.observe(document.querySelector('.cart-collaterals'), { childList: true });
  }

  function cityToInput($citybox) {
    if ($citybox.is('input')) {
      $citybox.prop('disabled', false);
      return;
    }

    const input_name = $citybox.attr('name');
    const input_id = $citybox.attr('id');
    const placeholder = $citybox.attr('placeholder');

    $citybox.parent().find('.select2-container').remove();
    $citybox.replaceWith(`<input type="text" class="input-text" name="${input_name}" id="${input_id}" placeholder="${placeholder}" />`);
  }

  function disableCity($citybox) {
    $citybox.val('').change();
    $citybox.prop('disabled', true);
  }

  function cityToSelect($citybox, current_cities) {
    const value = $citybox.val();

    if ($citybox.is('input')) {
      const input_name = $citybox.attr('name');
      const input_id = $citybox.attr('id');
      const placeholder = $citybox.attr('placeholder');

      $citybox.replaceWith(`<select name="${input_name}" id="${input_id}" class="city_select" placeholder="${placeholder}"></select>`);
      $citybox = $(`#${input_id}`);
    } else {
      $citybox.prop('disabled', false);
    }

    let options = '';
    for (const cityName of current_cities) {
      options += `<option value="${cityName}">${cityName}</option>`;
    }

    $citybox.html(`<option value="">${wc_city_select_params.i18n_select_city_text}</option>` + options);

    if ($(`option[value="${value}"]`, $citybox).length) {
      $citybox.val(value).change();
    } else {
      $citybox.val('').change();
    }

    $(document.body).trigger('city_to_select');
  }
});
