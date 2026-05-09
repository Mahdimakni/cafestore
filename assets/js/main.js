document.addEventListener('DOMContentLoaded', () => {
    // Cookie Banner Logic
    const cookieBanner = document.getElementById('cookie-banner');
    const acceptBtn = document.getElementById('accept-cookies');
    const declineBtn = document.getElementById('decline-cookies');

    if (cookieBanner) {
        const hideBanner = () => {
            cookieBanner.style.transform = 'translateY(150%)';
            setTimeout(() => cookieBanner.remove(), 800);
        };

        const setCookie = (name, value, days) => {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        };

        if(acceptBtn) {
            acceptBtn.addEventListener('click', () => {
                setCookie('cookie_consent', 'accepted', 365);
                hideBanner();
            });
        }

        if(declineBtn) {
            declineBtn.addEventListener('click', () => {
                setCookie('cookie_consent', 'declined', 365);
                hideBanner();
            });
        }
    }
});

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = message;
    
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Remove after 3s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

function updateQty(btn, change) {
    const input = btn.parentElement.querySelector('.qty-input');
    let val = parseInt(input.value) || 1;
    const max = parseInt(input.getAttribute('max')) || 99;
    
    val += change;
    if (val < 1) val = 1;
    if (val > max) val = max;
    
    input.value = val;
}

function addToCart(productId, qty) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('id', productId);
    formData.append('qty', qty || 1);

    fetch('/cafestore/ajax_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message, 'success');
            // Update badge
            const badges = document.querySelectorAll('.cart-count');
            badges.forEach(b => {
                b.textContent = data.cartCount;
                b.classList.add('pop');
                setTimeout(() => b.classList.remove('pop'), 300);
            });
        } else {
            showToast(data.message, 'error');
            if (data.redirect) {
                setTimeout(() => { window.location.href = data.redirect; }, 1500);
            }
        }
    })
    .catch(err => {
        showToast('Une erreur est survenue.', 'error');
        console.error(err);
    });
}

function toggleFavorite(productId, btn) {
    const formData = new FormData();
    formData.append('id', productId);

    fetch('/cafestore/ajax_favorites.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message, 'success');
            if (data.is_favorite) {
                btn.classList.add('active');
                btn.innerHTML = '❤️';
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '🤍';
            }
            
            const countFav = document.getElementById('fav-count');
            if (countFav) {
                countFav.textContent = data.favoritesCount;
                countFav.classList.add('pop');
                setTimeout(() => countFav.classList.remove('pop'), 300);
            }
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        showToast('Une erreur est survenue.', 'error');
    });
}

// For panier.php
function updateCartItem(productId, qty) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', productId);
    formData.append('qty', qty);

    fetch('/cafestore/ajax_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            if (qty <= 0) {
                // Remove row
                const row = document.getElementById(`cart-row-${productId}`);
                if(row) row.remove();
                showToast("Produit retiré.", 'success');
                // Check if empty
                if(data.cartCount === 0) {
                    location.reload(); // To show empty state
                }
            } else {
                // Update subtotal text
                const subtotalElem = document.getElementById(`subtotal-${productId}`);
                if (subtotalElem) {
                    subtotalElem.textContent = data.itemSubtotal.toFixed(2) + ' TND';
                }
            }

            // Update badge
            document.querySelectorAll('.cart-count').forEach(b => b.textContent = data.cartCount);
            
            // Update total summary
            const totalElems = document.querySelectorAll('.cart-total-value');
            totalElems.forEach(el => el.textContent = data.cartTotal.toFixed(2) + ' TND');
            
            // Update shipping and grand total
            let shipping = 5;
            if (data.cartTotal >= 50 || data.cartTotal == 0) shipping = 0;
            
            const shippingElem = document.getElementById('shipping-cost');
            if(shippingElem) shippingElem.textContent = shipping === 0 ? (data.cartTotal == 0 ? '0.00 TND' : 'Gratuite 🎉') : '5.00 TND';
            
            const grandTotalElem = document.getElementById('grand-total');
            if(grandTotalElem) grandTotalElem.textContent = (data.cartTotal + shipping).toFixed(2) + ' TND';

        }
    });
}

function removeCartItem(productId) {
    if(confirm('Retirer ce produit ?')) {
        updateCartItem(productId, 0);
    }
}

// Filtering system
function applyFilters() {
    const form = document.getElementById('filter-form');
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();

    // Show loading
    const grid = document.getElementById('dynamic-products-grid');
    if(grid) grid.style.opacity = '0.5';

    fetch('/cafestore/ajax_filter.php?' + params)
    .then(res => res.text())
    .then(html => {
        if(grid) {
            grid.innerHTML = html;
            grid.style.opacity = '1';
        }
        // Update URL without reload
        const newUrl = window.location.pathname + '?' + params;
        window.history.pushState({path:newUrl}, '', newUrl);
    })
    .catch(err => {
        console.error(err);
        if(grid) grid.style.opacity = '1';
    });
}

// Event delegation for dynamically loaded inputs in cart or filters
document.addEventListener('change', function(e) {
    if(e.target.closest('#filter-form')) {
        applyFilters();
    }
});
