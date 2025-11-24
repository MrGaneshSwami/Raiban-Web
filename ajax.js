
//  ajax js for demand page
// Generic autocomplete setup
function setupAutocomplete(inputId, suggestionBoxId, field) {
  const input = document.getElementById(inputId);
  const suggestionBox = document.getElementById(suggestionBoxId);

  input.addEventListener("keyup", function () {
    const query = this.value.trim();

    if (query.length < 1) {
      suggestionBox.innerHTML = "";
      return;
    }

    fetch(`fetch_suggestions.php?field=${field}&q=${encodeURIComponent(query)}`)
      .then(res => res.text())
      .then(html => {
        suggestionBox.innerHTML = html;
        // Attach click event to each suggestion
        suggestionBox.querySelectorAll(".list-group-item-action").forEach(item => {
          item.addEventListener("click", function () {
            input.value = item.dataset.value || item.textContent;
            suggestionBox.innerHTML = "";

            // If selecting material_code → also fetch material_name, unit, rate
            if (field === "material_code") {
              const code = this.textContent;
              fetch(`fetch_suggestions.php?field=material_name_by_code&q=${code}`)
                .then(res => res.text())
                .then(name => {
                  document.getElementById("materialName").value = name;
                  // Fetch unit
                  fetch(`fetch_suggestions.php?field=material_unit_by_code&q=${code}`)
                    .then(res => res.text())
                    .then(unit => {
                      document.getElementById("materialUnit").value = unit;
                    });
                  // Fetch rate
                  fetch(`fetch_suggestions.php?field=product_rate_by_code&q=${code}`)
                    .then(res => res.json())
                    .then(data => {
                      document.getElementById("rate").value = data.rate || 0;
                      // Recalculate total price
                      const qty = parseFloat(document.getElementById("materialQuantity").value) || 0;
                      document.getElementById("totalPrice").value = (qty * (data.rate || 0)).toFixed(2);
                    });
                });
            }

            // If selecting material_name → also fetch material_code, unit, rate
            if (field === "material_name") {
              const name = this.textContent;
              fetch(`fetch_suggestions.php?field=material_code_by_name&q=${name}`)
                .then(res => res.text())
                .then(code => {
                  document.getElementById("materialCode").value = code;
                  // Fetch unit
                  fetch(`fetch_suggestions.php?field=material_unit_by_name&q=${name}`)
                    .then(res => res.text())
                    .then(unit => {
                      document.getElementById("materialUnit").value = unit;
                    });
                  // Fetch rate
                  fetch(`fetch_suggestions.php?field=product_rate_by_code&q=${code}`)
                    .then(res => res.json())
                    .then(data => {
                      document.getElementById("rate").value = data.rate || 0;
                      // Recalculate total price
                      const qty = parseFloat(document.getElementById("materialQuantity").value) || 0;
                      document.getElementById("totalPrice").value = (qty * (data.rate || 0)).toFixed(2);
                    });
                });
            }

            // If selecting site_name → fetch all site details and autofill
            if (field === "site_name") {
              const siteName = this.dataset.value || this.textContent;
              fetch(`fetch_suggestions.php?field=site_details_by_name&q=${encodeURIComponent(siteName)}`)
                .then(res => res.json())
                .then(data => {
                  if (data.success) {
                    document.getElementById("siteSupervisor").value = data.siteSupervisor || "";
                    document.getElementById("siteIncharge").value = data.siteIncharge || "";
                    document.getElementById("contractor").value = data.contractor || "";
                    document.querySelector("input[name='division']").value = data.division || "";
                    document.querySelector("input[name='subDivision']").value = data.subDivision || "";
                    document.querySelector("input[name='sectionName']").value = data.sectionName || "";
                    document.querySelector("input[name='circle']").value = data.circle || "";
                    var locationField = document.getElementById("location");
                    if (locationField) locationField.value = data.location || "";
                  }
                });
            }
          });
        });
      })
      .catch(err => {
        suggestionBox.innerHTML = `<div class='list-group-item text-danger'>Error: ${err.message}</div>`;
      });
  });
}

// Attach autocomplete for all fields
document.addEventListener("DOMContentLoaded", () => {
  setupAutocomplete("siteSupervisor", "siteSupervisorSuggestions", "site_supervisor");
  setupAutocomplete("contractor", "contractorSuggestions", "contractor");
  setupAutocomplete("siteIncharge", "siteInchargeSuggestions", "site_incharge");
  setupAutocomplete("materialCode", "materialCodeSuggestions", "material_code");
  setupAutocomplete("materialName", "materialNameSuggestions", "material_name");

  // For purchase.html fields
  setupAutocomplete("storeIncharge", "storeInchargeSuggestions", "store_incharge");
  setupAutocomplete("supplierName", "supplierNameSuggestions", "supplier_name");
  setupAutocomplete("supplierGst", "supplierGstSuggestions", "supplier_gst");
  // Material fields already included above
});

