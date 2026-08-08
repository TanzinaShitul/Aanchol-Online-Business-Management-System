// Update cart count
function updateCartCount() {
    fetch('includes/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(span => {
                span.textContent = data.count;
            });
        });
}

// Product stock check
function checkStock(productId, requestedQty) {
    fetch(`includes/check-stock.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.stock < requestedQty) {
                alert(`Only ${data.stock} items available in stock!`);
                document.querySelector(`#quantity-${productId}`).value = data.stock;
            }
        });
}

// Form validation
function validateForm(formOrId) {
    const form = typeof formOrId === 'string' ? document.getElementById(formOrId) : formOrId;
    if (!form) return true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Shared header controls. These do not depend on the Bootstrap CDN, so the
    // category and account menus remain usable on every customer page.
    const closeNavigationDropdowns = function(except) {
        document.querySelectorAll('[data-nav-dropdown]').forEach(function(toggle) {
            if (toggle !== except) {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.parentElement.querySelector('.dropdown-menu')?.classList.remove('show');
            }
        });
    };

    document.querySelectorAll('[data-nav-dropdown]').forEach(function(toggle) {
        toggle.addEventListener('click', function(event) {
            event.preventDefault();
            const menu = this.parentElement.querySelector('.dropdown-menu');
            if (!menu) return;

            const isOpen = menu.classList.contains('show');
            closeNavigationDropdowns(this);
            menu.classList.toggle('show', !isOpen);
            this.setAttribute('aria-expanded', String(!isOpen));
        });
    });

    const navigationToggler = document.querySelector('[data-nav-collapse]');
    if (navigationToggler) {
        navigationToggler.addEventListener('click', function() {
            const menu = document.getElementById(this.dataset.navCollapse);
            if (!menu) return;

            const isOpen = menu.classList.toggle('show');
            this.setAttribute('aria-expanded', String(isOpen));
        });
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.navbar .dropdown')) {
            closeNavigationDropdowns();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeNavigationDropdowns();
    });

    // Update cart count if user is logged in
    if (document.querySelector('.cart-count')) {
        updateCartCount();
        setInterval(updateCartCount, 30000); // Update every 30 seconds
    }
    
    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                alert('Please fill all required fields!');
            }
        });
    });
    
    // Quantity validation
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.max);
            const min = parseInt(this.min);
            const value = parseInt(this.value);
            
            if (value > max) this.value = max;
            if (value < min) this.value = min;
        });
    });
});

// Password visibility toggle functionality
const passwordToggle = document.getElementById('togglePassword');

if (passwordToggle) {
    passwordToggle.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
    });
}
