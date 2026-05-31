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
                }).then(() => {
                    buyButton.disabled = false;
                });
            }
        }
    });
}