// -------------------------
// Common JS for all pages
// -------------------------
document.addEventListener("DOMContentLoaded", () => {

  /** -------------------------
   * Navbar + Header
   * ------------------------- */



  const headerEl = document.getElementById("header-placeholder");
  if (headerEl) {
    const currentPage = window.location.pathname.split("/").pop();
    const headerFile = "header.html";

    fetch(headerFile)
      .then(res => res.text())
      .then(data => {
        headerEl.innerHTML = data;

        // Highlight active link
        document.querySelectorAll(".nav-item").forEach(link => {
          if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
          } else {
            link.classList.remove("active");
          }
        });
      })
      .catch(err => console.error("Navbar load error:", err));
  }





  /** -------------------------
   * Mobile Hamburger Menu
   * ------------------------- */
  const hamburgerBtn = document.getElementById("hamburgerBtn");
  const navLinks = document.getElementById("navLinks");
  if (hamburgerBtn && navLinks) {
    hamburgerBtn.addEventListener("click", () => {
      navLinks.classList.toggle("show");
    });
  }

  /** -------------------------
   * Unit Converter
   * ------------------------- */
  const converterForm = document.getElementById("converterForm");

  if (converterForm) {
    converterForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const value = parseFloat(document.getElementById("value").value);
      const from = document.getElementById("fromUnit").value;
      const to = document.getElementById("toUnit").value;

      if (isNaN(value)) {
        document.getElementById("result").innerText =
          "⚠️ Please enter a valid number";
        return;
      }

      const units = {
        meter: 1, kilometer: 1000, feet: 0.3048, inch: 0.0254,
        kg: 1, gram: 0.001, pound: 0.453592,
        liter: 1, ml: 0.001,
        CMT: 1, RMT: 1, ls: 1, pcs: 1
      };

      const categories = {
        meter: "length", kilometer: "length", feet: "length", inch: "length",
        kg: "weight", gram: "weight", pound: "weight",
        liter: "volume", ml: "volume",
        CMT: "special", RMT: "special", ls: "special", pcs: "special"
      };

      if (categories[from] !== categories[to]) {
        document.getElementById("result").innerText =
          `⚠️ Cannot convert ${from} to ${to}`;
        return;
      }

      const result = (value * units[from]) / units[to];
      document.getElementById("result").innerText =
        `${value} ${from} = ${result.toFixed(4)} ${to}`;
    });
  }

  /** -------------------------
   * Generic Form Success Message
   * ------------------------- */
  document.querySelectorAll("form").forEach(form => {
    const successMsg = form.querySelector(".success-message");
    if (successMsg) {
      form.addEventListener("submit", e => {
        e.preventDefault();
        successMsg.classList.remove("d-none");
        setTimeout(() => {
          successMsg.classList.add("d-none");
        }, 3000);
        form.reset();
      });
    }
  });

  /*-------------------------
    Dashboard Sidebar Toggle
  -------------------------*/
  const menuToggle = document.getElementById("menuToggle");
  const sidebar = document.getElementById("sidebar");
  if (menuToggle && sidebar) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
    });
  }

  /** -------------------------
   * Login Page - Password Toggle + Strength
   * ------------------------- */
  const togglePassword = document.querySelector('.toggle-password');
  const passwordField = document.querySelector('input[name="password"]');
  const loginStrength = document.getElementById('loginStrength');
  const loginForm = document.getElementById('loginForm');
  const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

  if (togglePassword && passwordField) {
    togglePassword.addEventListener('click', () => {
      const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordField.setAttribute('type', type);
      togglePassword.classList.toggle('fa-eye-slash');
    });
  }

  if (passwordField && loginStrength) {
    passwordField.addEventListener('input', () => {
      const val = passwordField.value;
      if (!val) {
        loginStrength.textContent = '';
        return;
      }
      if (strongRegex.test(val)) {
        loginStrength.textContent = '✅ Strong password';
        loginStrength.style.color = 'green';
      } else {
        loginStrength.textContent = '⚠️ Use 8+ chars with upper, lower, number & symbol';
        loginStrength.style.color = 'red';
      }
    });
  }

  if (loginForm && passwordField && loginStrength) {
    loginForm.addEventListener('submit', e => {
      if (!strongRegex.test(passwordField.value)) {
        e.preventDefault();
        loginStrength.textContent = '❌ Please enter a strong password!';
        loginStrength.style.color = 'red';
      }
    });
  }

  /** -------------------------
   * Issue Form Validation
   * ------------------------- */
  const issueForm = document.getElementById("issueForm");
  const customAlert = document.getElementById("customAlert");

  if (issueForm && customAlert) {
    issueForm.addEventListener("submit", e => {
      let valid = true;
      issueForm.querySelectorAll("[required]").forEach(input => {
        if (!input.value.trim()) valid = false;
      });

      if (!valid) {
        e.preventDefault();
        customAlert.classList.remove("d-none");
        customAlert.scrollIntoView({ behavior: "smooth" });
      } else {
        customAlert.classList.add("d-none");
      }
    });

    issueForm.querySelectorAll("[required]").forEach(input => {
      input.addEventListener("input", () => {
        customAlert.classList.add("d-none");
      });
    });
  }

  /** -------------------------
   * Datepicker Default Value
   * ------------------------- */
  const dateInput = document.getElementById("pageDate");
  if (dateInput) {
    const today = new Date().toISOString().split("T")[0];
    dateInput.value = today;
  }

  /** -------------------------
   * Table Search Functionality
   * ------------------------- */
  const searchInput = document.getElementById("searchInput");
  const dataTable = document.getElementById("dataTable");

  if (searchInput && dataTable) {
    searchInput.addEventListener("keyup", () => {
      const filter = searchInput.value.toLowerCase();
      const rows = dataTable.getElementsByTagName("tr");

      for (let i = 1; i < rows.length; i++) {
        let cells = rows[i].getElementsByTagName("td");
        let match = false;

        for (let j = 0; j < cells.length; j++) {
          if (cells[j]) {
            let text = cells[j].innerText.toLowerCase();
            if (text.includes(filter)) {
              match = true;
              break;
            }
          }
        }

        rows[i].style.display = match ? "" : "none";
      }
    });
  }

  /** -------------------------
   * Render Table
   * ------------------------- */
  const tbody = document.getElementById("reportTableBody");
  const totalCostEl = document.getElementById("totalCost");

  function renderTable(data) {
    if (!tbody || !totalCostEl) return;
    tbody.innerHTML = "";
    let total = 0;

    data.forEach(item => {
      const rowTotal = item.qty * item.unitCost;
      total += rowTotal;

      tbody.innerHTML += `
        <tr>
          <td>${item.date}</td>
          <td>${item.material}</td>
          <td>${item.supplier}</td>
          <td>${item.contractor}</td>
          <td>${item.site}</td>
          <td>${item.qty}</td>
          <td>${item.unitCost} ₹</td>
          <td>${rowTotal} ₹</td>
        </tr>
      `;
    });

    totalCostEl.textContent = total.toFixed(2);
  }



});


