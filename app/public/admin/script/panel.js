import { initOrdersContent, updateOrders } from "./content/orders-content.js";

document.addEventListener("DOMContentLoaded", () => {
    const filterForm = document.querySelector('#filter-form');
    if (!filterForm) return;

    let debounceTimeout;

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        clearTimeout(debounceTimeout);
        updateOrders(filterForm, 1);
    });

    filterForm.addEventListener('change', (e) => {
        if (e.target.tagName === 'SELECT') {
            updateOrders(filterForm, 1);
        }
    });

    filterForm.addEventListener('input', (e) => {
        if (e.target.tagName === 'SELECT') return;

        clearTimeout(debounceTimeout);

        debounceTimeout = setTimeout(() => {
            updateOrders(filterForm, 1);
        }, 400);
    });

    initOrdersContent();
});