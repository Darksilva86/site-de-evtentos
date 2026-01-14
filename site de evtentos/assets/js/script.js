// script.js - Funcionalidades JavaScript

document.addEventListener("DOMContentLoaded", function () {
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  // Confirm delete actions
  const deleteButtons = document.querySelectorAll(".btn-delete, .delete-btn");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!confirm("Tem certeza que deseja excluir este item?")) {
        e.preventDefault();
        return false;
      }
    });
  });

  // Form validation
  const forms = document.querySelectorAll(".needs-validation");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });

  // Password visibility toggle
  const togglePasswordButtons = document.querySelectorAll(".toggle-password");
  togglePasswordButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const icon = this.querySelector("i");

      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      }
    });
  });

  // Search functionality
  const searchForm = document.getElementById("searchForm");
  if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
      const searchInput = document.getElementById("searchInput");
      if (searchInput && searchInput.value.trim() === "") {
        e.preventDefault();
        alert("Por favor, insira um termo de pesquisa");
      }
    });
  }

  // Quantity controls for cart
  const minusButtons = document.querySelectorAll(".qty-minus");
  const plusButtons = document.querySelectorAll(".qty-plus");

  minusButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.nextElementSibling;
      let value = parseInt(input.value);
      if (value > 1) {
        input.value = value - 1;
        updateCartItem(input);
      }
    });
  });

  plusButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.previousElementSibling;
      let value = parseInt(input.value);
      const max = parseInt(input.getAttribute("max"));
      if (value < max) {
        input.value = value + 1;
        updateCartItem(input);
      }
    });
  });

  // Image preview for file upload
  const imageInputs = document.querySelectorAll(
    'input[type="file"][accept*="image"]'
  );
  imageInputs.forEach((input) => {
    input.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const preview = document.getElementById("imagePreview");
          if (preview) {
            preview.src = e.target.result;
            preview.style.display = "block";
          }
        };
        reader.readAsDataURL(file);
      }
    });
  });

  // Date validation - prevent past dates for events
  const eventDateInputs = document.querySelectorAll(
    'input[type="date"].future-date'
  );
  eventDateInputs.forEach((input) => {
    const today = new Date().toISOString().split("T")[0];
    input.setAttribute("min", today);
  });

  // Price formatting
  const priceInputs = document.querySelectorAll(
    'input[type="number"].price-input'
  );
  priceInputs.forEach((input) => {
    input.addEventListener("blur", function () {
      if (this.value) {
        this.value = parseFloat(this.value).toFixed(2);
      }
    });
  });

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // Loading overlay
  window.showLoading = function () {
    const overlay = document.createElement("div");
    overlay.id = "loadingOverlay";
    overlay.className = "spinner-overlay";
    overlay.innerHTML =
      '<div class="spinner-border text-light" role="status"><span class="visually-hidden">Carregando...</span></div>';
    document.body.appendChild(overlay);
  };

  window.hideLoading = function () {
    const overlay = document.getElementById("loadingOverlay");
    if (overlay) {
      overlay.remove();
    }
  };

  // Add to cart animation
  window.addToCartAnimation = function (button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i> Adicionado!';
    button.classList.remove("btn-primary");
    button.classList.add("btn-success");
    button.disabled = true;

    setTimeout(() => {
      button.innerHTML = originalText;
      button.classList.remove("btn-success");
      button.classList.add("btn-primary");
      button.disabled = false;
    }, 2000);
  };
});

// Update cart item quantity via AJAX
function updateCartItem(input) {
  const cartId = input.getAttribute("data-cart-id");
  const quantity = input.value;

  // This would typically be an AJAX call
  // For now, we'll submit the form
  const form = input.closest("form");
  if (form) {
    form.submit();
  }
}

// Format currency
function formatCurrency(value) {
  return new Intl.NumberFormat("pt-PT", {
    style: "currency",
    currency: "EUR",
  }).format(value);
}

// Validate email
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Validate phone (Portuguese format)
function validatePhone(phone) {
  const re = /^(\+351)?[0-9]{9}$/;
  return re.test(phone.replace(/\s/g, ""));
}
