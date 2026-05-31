import { touchAPI } from "../lib/api.js"
import { showAlert } from "../ui/alerts.js";
import { loadOrders } from "./orders-content.js";

export function loadCart() {
    fetch('/content/make-order-content.php')
        .then(res => res.text())
        .then(html => {
            const container = document.querySelector('#make-order-content');
            if (container) container.innerHTML = html;
        }).catch(() => {
        showAlert('Ошибка сервера');
    });
}

export function initMakeOrderContent() {
    const container = document.querySelector('#make-order-content');
    if (!container) return;

    container.addEventListener('click', (e) => {
        const plusToCartButton = e.target.closest('.plus-to-cart-button');
        if (plusToCartButton) {
            plusToCartButton.disabled = true;

            touchAPI("cart/add", {"product": plusToCartButton.dataset.product, "quantity": 1})
                .then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            }).finally(() => plusToCartButton.disabled = false);
            return;
        }

        const minusFromCartButton = e.target.closest('.minus-from-cart-button');
        if (minusFromCartButton) {
            minusFromCartButton.disabled = true;

            touchAPI("cart/remove", {"product": minusFromCartButton.dataset.product, "quantity": 1})
                .then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            }).finally(() => minusFromCartButton.disabled = false);
            return;
        }

        const removeFromCartButton = e.target.closest('.remove-from-cart-button');
        if (removeFromCartButton) {
            removeFromCartButton.disabled = true;

            touchAPI("cart/remove", {"product": removeFromCartButton.dataset.product, "quantity": removeFromCartButton.dataset.quantity})
                .then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            }).finally(() => removeFromCartButton.disabled = false);
            return;
        }

        const buyButton = e.target.closest('#buy-button');
        if (buyButton) {
            e.preventDefault();

            const form = document.getElementById("order-form");

            if (form) {
                if (!form.reportValidity()) {
                    return;
                }

                buyButton.disabled = true;

                let data = new FormData(form);

                touchAPI("order/make", {"email": data.get("email")})
                    .then(res => res.json()).then(data => {
                    if (data.success) {
                        loadCart();
                        loadOrders();
                    } else {
                        showAlert(data.error);
                    }
                }).catch(() => {
                    showAlert("Ошибка связи с сервером");
                }).finally(() => {
                    buyButton.disabled = false;
                });
            }
        }
    });
}