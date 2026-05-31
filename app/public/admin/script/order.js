import { touchAPI } from "./lib/api.js";
import { showAlert } from "../../script/ui/alerts.js";

document.addEventListener("DOMContentLoaded", () => {
    var previousStatus = null;
    const select = document.querySelector('.status-select');
    if (select) previousStatus = select.value;

    const container = document.querySelector('#orders-content');
    if (!container) return;

    container.addEventListener('change', (e) => {
        const select = e.target.closest('.status-select');
        if (!select) return;

        const orderId = select.dataset.order;
        const newStatus = select.value;

        select.disabled = true;

        touchAPI("orders/update-status", { "orderId": orderId, "status": newStatus })
            .then(res => res.json()).then(data => {
            if (!data.success) {
                showAlert(data.error);
                select.value = previousStatus;
            } else {
                previousStatus = newStatus;
            }
        })
            .catch((err) => {
                showAlert("Ошибка связи с сервером");
                select.value = previousStatus;
            })
            .finally(() => {
                select.disabled = false;
            });
    });
});