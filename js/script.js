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
function validateForm(formId) {
    const form = document.getElementById(formId);
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
    // Update cart count if user is logged in
    if (document.querySelector('.cart-count')) {
        updateCartCount();
        setInterval(updateCartCount, 30000); // Update every 30 seconds
    }
    
    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
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
document.getElementById('togglePassword').addEventListener('click', function() {
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