import { touchAPI } from "../lib/api.js"
import { showAlert } from "../ui/alerts.js";

export function loadCart() {
    fetch('/content/cart-content.php')
        .then(res => res.text())
        .then(html => {
            const container = document.querySelector('#cart-content');
            if (container) container.innerHTML = html;
        }).catch(() => {
        showAlert('Ошибка сервера');
    });
}

export function initCartContent() {
    const container = document.querySelector('#cart-content');
    if (!container) return;

    container.addEventListener('click', (e) => {
        const plusToCartButton = e.target.closest('.plus-to-cart-button');
        if (plusToCartButton) {
            touchAPI("cart/add", {"product": plusToCartButton.dataset.product, "quantity": 1})
                .then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            });
            return;
        }

        const minusFromCartButton = e.target.closest('.minus-from-cart-button');
        if (minusFromCartButton) {
            touchAPI("cart/remove", {"product": minusFromCartButton.dataset.product, "quantity": 1})
                .then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            });
            return;
        }

        const removeFromCartButton = e.target.closest('.remove-from-cart-button');
        if (removeFromCartButton) {
            touchAPI("cart/remove", {"product": removeFromCartButton.dataset.product, "quantity": removeFromCartButton.dataset.quantity})
                .then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            });
            return;
        }

        const clearButton = e.target.closest('#clear-cart-button');
        if (clearButton) {
            e.preventDefault();

            touchAPI("cart/clear", {}).then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            });
            return;
        }

        const buyButton = e.target.closest('#buy-button');
        if (buyButton) {
            e.preventDefault();

            window.location.href = "/account.php"
        }
    });
}