/* eslint-disable */
(function ($) {
  "use strict";

  jQuery(document).ready(function ($) {

    // Map all counties to their respective codes
    const counties = [["AB", "Alba"], ["AR", "Arad"], ["AG", "Arges"], ["BC", "Bacau"], ["BH", "Bihor"], ["BN", "Bistrita-Nasaud"], ["BT", "Botosani"], ["BR", "Braila"], ["BV", "Brasov"], ["B", "Bucuresti"], ["BZ", "Buzau"], ["CL", "Calarasi"], ["CS", "Caras-Severin"], ["CJ", "Cluj"], ["CT", "Constanta"], ["CV", "Covasna"], ["DB", "Dambovita"], ["DJ", "Dolj"], ["GL", "Galati"], ["GJ", "Gorj"], ["GR", "Giurgiu"], ["HR", "Harghita"], ["HD", "Hunedoara"], ["IL", "Ialomita"], ["IS", "Iasi"], ["IF", "Ilfov"], ["MM", "Maramures"], ["MH", "Mehedinti"], ["MS", "Mures"], ["NT", "Neamt"], ["OT", "Olt"], ["PH", "Prahova"], ["SJ", "Salaj"], ["SM", "Satu Mare"], ["SB", "Sibiu"], ["SV", "Suceava"], ["TR", "Teleorman"], ["TM", "Timis"], ["TL", "Tulcea"], ["VS", "Vaslui"], ["VL", "Valcea"], ["VN", "Vrancea"]];

    const mapElements = $(".inchoo-gls-map");
    if (mapElements.length > 0) {
      mapElements.on("change", (e) => {
        const pickupInfo = e.detail;
        const pickupInfoDiv = $("#gls-pickup-info");
        if (pickupInfoDiv.length > 0) {
          pickupInfoDiv.html = (`
                      <strong>${gls_croatia.pickup_location}:</strong><br>
                      ${gls_croatia.name}: ${pickupInfo.name}<br>
                      ${gls_croatia.address}: ${pickupInfo.contact.address}, ${pickupInfo.contact.city}, ${pickupInfo.contact.postalCode}<br>
                      ${gls_croatia.country}: ${pickupInfo.contact.countryCode}
                  `).show();
        }

        // Create or update the hidden input field
        let hiddenInput = $("#gls-pickup-info-data");
        if (hiddenInput.length === 0) {
          hiddenInput = $("<input>", {
            type: "hidden",
            id: "gls-pickup-info-data",
            name: "gls_pickup_info"
          }).appendTo("form[name='checkout']");
        }
        hiddenInput.val(JSON.stringify(pickupInfo));
      });
    }

    function showMapModal(mapClass) {
      const selectedCountry = $("#billing_country").val();
      $(`.${mapClass}`).attr("country", selectedCountry.toLowerCase())[0].showModal(); // Show the map modal.
    }

    // Event listener for locker button
    $(document.body).on("click", "#gls-map-button", function () {
      showMapModal("gls-map-locker"); // Show the locker map.
    });

    var el = document.getElementById('mygls-map');
    el.addEventListener('change', (e) => {

      const selectors = {
        lockerId: document.getElementById('curiero_mygls_lockers_select'),
        inputCounty: document.getElementById('billing_state'),
        inputCity: document.getElementById('billing_city'),
      };

      let county = e.detail.contact.city.split(" ").slice(-1); // get the last word from the city field.

      setTimeout(() => {
        let found_county = counties.find((county_map) => county == county_map[0]); // find the county by the code.
        let county_value = county[0]

        // If the county is not found, the county acronym is a single letter and it's between 0 and 6, then it's Bucharest.
        if (
          !found_county &&
          county_value.length === 1 &&
          ['0', '1', '2', '3', '4', '5', '6'].includes(county_value)
        ) {
          found_county = ["B"]
        }

        selectors.inputCounty.value = found_county[0];
        $(selectors.inputCounty).trigger("change");
      })

      setTimeout(() => {
        let city = e.detail.contact.city.split(" ").slice(0, -1).join(" "); // remove the last word.

        // If the postal code starts with 0 and the second digit is not 7, then it's a sector.
        if (e.detail.contact.postalCode.at(0) == "0") {
          if (e.detail.contact.postalCode.at(1) !== "7") {
            city = "Sector " + e.detail.contact.postalCode.at(1)
          }
        }
        selectors.inputCity.value = city;
        $(selectors.inputCity).trigger("change");
      })

      setTimeout(() => {
        // For selected locker from the map, set the lockerId value and trigger the change event for dropdown.
        window.curiero_selected_mygls_lockerId = e.detail.id;
        selectors.lockerId.value = e.detail.id;
        if (selectors.lockerId.value === "") {
          console.log("Locker is not available");
          // return;
        }

        $(selectors.lockerId).trigger("change.select2");
        $(document.body).trigger("update_checkout");
      });

    });
  });
})(jQuery);

