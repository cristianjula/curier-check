'use strict';

jQuery($ => {
  const counties = [["AB", "Alba"], ["AR", "Arad"], ["AG", "Arges"], ["BC", "Bacau"], ["BH", "Bihor"], ["BN", "Bistrita-Nasaud"], ["BT", "Botosani"], ["BR", "Braila"], ["BV", "Brasov"], ["B", "Bucuresti"], ["BZ", "Buzau"], ["CL", "Calarasi"], ["CS", "Caras-Severin"], ["CJ", "Cluj"], ["CT", "Constanta"], ["CV", "Covasna"], ["DB", "Dambovita"], ["DJ", "Dolj"], ["GL", "Galati"], ["GJ", "Gorj"], ["GR", "Giurgiu"], ["HR", "Harghita"], ["HD", "Hunedoara"], ["IL", "Ialomita"], ["IS", "Iasi"], ["IF", "Ilfov"], ["MM", "Maramures"], ["MH", "Mehedinti"], ["MS", "Mures"], ["NT", "Neamt"], ["OT", "Olt"], ["PH", "Prahova"], ["SJ", "Salaj"], ["SM", "Satu Mare"], ["SB", "Sibiu"], ["SV", "Suceava"], ["TR", "Teleorman"], ["TM", "Timis"], ["TL", "Tulcea"], ["VS", "Vaslui"], ["VL", "Valcea"], ["VN", "Vrancea"]];

  $(document.body).on('click', '#select_locker_map', () => {
    const field_type = document.getElementById('ship-to-different-address-checkbox')?.checked ? 'shipping' : 'billing';

    const selectors = {
      shippingMethod: document.querySelector('input[type=radio][value=curiero_sameday_lockers]'),
      lockerId: document.getElementById('curiero_sameday_lockers_select'),
      inputCounty: document.getElementById(`${field_type}_state`),
      inputCity: document.getElementById(`${field_type}_city`),
    };

    const options = {
      clientId: "b8cb2ee3-41b9-4c3d-aafe-1527b453d65e",
      countryCode: 'RO',
      langCode: 'ro',
      city: selectors.inputCity.value.replace('Sector ', 'Sectorul ')
    };

    window.LockerPlugin.init(options);
    const plugin = window.LockerPlugin.getInstance();

    let iframe_current_src = null;
    if (plugin?.lockerPluginService?.elements?.iframe) {
      iframe_current_src = new URL(plugin.lockerPluginService.elements.iframe.src);
      if (iframe_current_src.searchParams.get('city') != selectors.inputCity.value) {
        iframe_current_src.searchParams.set('city', selectors.inputCity.value);
        plugin.lockerPluginService.elements.iframe.src = iframe_current_src.toString();
      }
    }

    plugin.open();
    plugin.subscribe(({ city, county, lockerId }) => {
      city = city.replace('Sectorul ', 'Sector ');

      const found_county = counties.find((county_map) => county === county_map[1]);
      if (found_county !== undefined) {
        selectors.inputCounty.value = found_county[0];
        $(selectors.inputCounty).trigger("change");
      }

      setTimeout(() => {
        selectors.inputCity.value = city;
        $(selectors.inputCity).trigger("change");
      })

      setTimeout(() => {
        selectors.shippingMethod.checked = true;
        selectors.lockerId.value = lockerId;
        window.curiero_selected_sameday_lockerId = lockerId;
        $(selectors.lockerId).trigger("change.select2");
        $(document.body).trigger("update_checkout");
      })

      plugin.close();
    })
  })
})
