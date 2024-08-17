(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {
        const counties = [["AB", "Alba"], ["AR", "Arad"], ["AG", "Arges"], ["BC", "Bacau"], ["BH", "Bihor"], ["BN", "Bistrita-Nasaud"], ["BT", "Botosani"], ["BR", "Braila"], ["BV", "Brasov"], ["B", "Bucuresti"], ["BZ", "Buzau"], ["CL", "Calarasi"], ["CS", "Caras-Severin"], ["CJ", "Cluj"], ["CT", "Constanta"], ["CV", "Covasna"], ["DB", "Dambovita"], ["DJ", "Dolj"], ["GL", "Galati"], ["GJ", "Gorj"], ["GR", "Giurgiu"], ["HR", "Harghita"], ["HD", "Hunedoara"], ["IL", "Ialomita"], ["IS", "Iasi"], ["IF", "Ilfov"], ["MM", "Maramures"], ["MH", "Mehedinti"], ["MS", "Mures"], ["NT", "Neamt"], ["OT", "Olt"], ["PH", "Prahova"], ["SJ", "Salaj"], ["SM", "Satu Mare"], ["SB", "Sibiu"], ["SV", "Suceava"], ["TR", "Teleorman"], ["TM", "Timis"], ["TL", "Tulcea"], ["VS", "Vaslui"], ["VL", "Valcea"], ["VN", "Vrancea"]];

        // Initialize default values for the map.
        let selectedPickUpPoint = null;

        // Event listener for the map.
        window.addEventListener("map:select-point", listenToChanges, true);

        // Initialize the map.
        jQuery(document.body).on('click', '#openMap', () => {
            const rootNode = document.getElementById("mapDiv");
            const field_type = document.getElementById('ship-to-different-address-checkbox')?.checked ? 'shipping' : 'billing';

            window.LoadMapFanBox({
                pickUpPoint: selectedPickUpPoint,
                county: counties.find((county_map) => document.getElementById(`${field_type}_state`).value == county_map[0])[1],
                locality: document.getElementById(`${field_type}_city`).value,
                rootNode,
                // rootId = mapDiv
            });
        });

        // Reset the selected pick up point if the user changes the shipping method or the shipping address.
        jQuery(document.body).on('change', 'input[name="shipping_method[0]"], #ship-to-different-address-checkbox', () => {
            selectedPickUpPoint = null;
        });

        function listenToChanges(event) {
            selectedPickUpPoint = event.detail.item;
            const field_type = document.getElementById('ship-to-different-address-checkbox')?.checked ? 'shipping' : 'billing';

            const selectors = {
                lockerId: document.getElementById('curiero_fan_lockers_select'),
                inputCounty: document.getElementById(`${field_type}_state`),
                inputCity: document.getElementById(`${field_type}_city`),
            };

            let address = selectedPickUpPoint.address.split(",").slice(0, 2).map((value) => value.trim());

            setTimeout(() => {
                // Match county string from map to the county character from the counties array for the select2 'billing_state' field and trigger change event.
                let county = counties.find((county_map) => address[0] == county_map[1]);
                selectors.inputCounty.value = county[0];
                $(selectors.inputCounty).trigger("change");

            });

            setTimeout(() => {
                // Match city string from map to the city string from the address array for the select2 'billing_city' field and trigger change event.
                // If the city is a sector, set the city to "Sector" + the sector number.
                let city = address[1];
                let zipcode = selectedPickUpPoint.address.split(",").slice(4, 5).map((value) => value.trim())[0];
                if (zipcode.at(0) === "0") {
                    if (zipcode.at(1) !== "7") {
                        city = "Sector " + zipcode.at(1)
                    }
                }
                city = city.replace(/(^\w{1})|(\s+\w{1})/g, letter => letter.toUpperCase()); // Capitalize first letter of each word.
                selectors.inputCity.value = city;

                // Trigger change event for the select2 'billing_state' and 'billing_city' fields.
                $(selectors.inputCity).trigger("change");
            });

            // For selected locker from the map, set value for select2 'curiero_fan_lockers_select' field and trigger change event.
            setTimeout(() => {
                window.curiero_selected_fan_lockerId = selectedPickUpPoint.id;
                selectors.lockerId.value = selectedPickUpPoint.id;

                $(selectors.lockerId).trigger("change.select2");
                $(document.body).trigger("update_checkout")
            });
        }
    });
}(jQuery));