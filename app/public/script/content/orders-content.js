import { showAlert } from "../ui/alerts.js";

export function loadOrders() {
    fetch('/content/orders-content.php')
        .then(res => res.text())
        .then(html => {
            const container = document.querySelector('#orders-content');
            if (container) container.innerHTML = html;
        }).catch(() => {
        showAlert('Ошибка сервера');
    });
}