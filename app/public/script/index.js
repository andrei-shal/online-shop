import { initProductsContent, updateProducts } from "./content/products-content.js";

document.addEventListener("DOMContentLoaded", () => {
    const filterForm = document.querySelector('#filter-form');
    if (!filterForm) return;

    let debounceTimeout;

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        clearTimeout(debounceTimeout);
        updateProducts(filterForm, 1);
    });

    filterForm.addEventListener('change', (e) => {
        if (e.target.tagName === 'SELECT') {
            updateProducts(filterForm, 1);
        }
    });

    filterForm.addEventListener('input', (e) => {
        if (e.target.tagName === 'SELECT') return;

        clearTimeout(debounceTimeout);

        debounceTimeout = setTimeout(() => {
            updateProducts(filterForm, 1);
        }, 400);
    });

    initProductsContent();
});