import { loadCart } from "./content/cart-content.js";
import { showAlert } from "./ui/alerts.js";
import { touchAPI } from "./lib/api.js";

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('detail-count');
    const minusBtn = document.getElementById('detail-minus');
    const plusBtn = document.getElementById('detail-plus');
    const addBtn = document.getElementById('detail-add-btn');

    if (!input || !addBtn) return;

    minusBtn.onclick = () => {
        let val = parseInt(input.value) || 1;
        if (val > 1) input.value = val - 1;
    };

    plusBtn.onclick = () => {
        let val = parseInt(input.value) || 1;
        if (val < 99) input.value = val + 1;
    };

    input.onclick = () => {
        let val = parseInt(input.value);
        if (isNaN(val) || val < 1) input.value = 1;
        if (val > 99) input.value = 99;
    };

    addBtn.onclick = () => {
        touchAPI("cart/add", {"product": addBtn.dataset.product, "quantity": parseInt(input.value) || 1})
            .then(res => res.json()).then(data => {
            if (data.success) {
                loadCart();
            } else {
                showAlert(data.error);
            }
        }).catch(() => {
            showAlert("Ошибка связи с сервером");
        });
    };
});