import { touchAPI } from "../lib/api.js"
import { showAlert } from "../ui/alerts.js";
import { loadCart } from "./cart-content.js";

export function updateProducts(form, page) {
    let searchParams;

    if (form) {
        const formData = new FormData(form);

        searchParams = new URLSearchParams(formData);

        const minPrice = searchParams.get('filters[minPrice]');
        const maxPrice = searchParams.get('filters[maxPrice]');

        if (minPrice && minPrice.trim() !== "") {
            searchParams.set('filters[minPrice]', Math.round(parseFloat(minPrice) * 100).toString());
        }
        if (maxPrice && maxPrice.trim() !== "") {
            searchParams.set('filters[maxPrice]', Math.round(parseFloat(maxPrice) * 100).toString());
        }

        [...searchParams.keys()].forEach(key => {
            const value = searchParams.get(key);
            if (value === null || value.trim() === "") {
                searchParams.delete(key);
            }
        });
    } else {
        searchParams = new URLSearchParams();
    }

    searchParams.set('page', page);

    window.history.replaceState({}, '', `?${searchParams.toString()}`);

    searchParams.set('pageSize', 6);

    loadProducts(searchParams);
}

export function loadProducts(searchParams) {
    fetch(`/content/products-content.php?${searchParams.toString()}`)
        .then(res => res.text())
        .then(html => {
            const container = document.querySelector('#products-content');
            if (container) container.innerHTML = html;
        }).catch(() => showAlert('Ошибка сервера'));
}

export function initProductsContent() {
    const container = document.querySelector('#products-content');
    if (!container) return;

    container.addEventListener('click', (e) => {
        const cartButton = e.target.closest('.add-to-cart-button');
        if (cartButton) {
            cartButton.disabled = true;

            touchAPI("cart/add", {"product": cartButton.dataset.product, "quantity": 1})
                .then(res => res.json()).then(data => {
                if (data.success) {
                    loadCart();
                    showAlert("Добавлено", "success")
                } else {
                    showAlert(data.error);
                }
            }).catch(() => {
                showAlert("Ошибка связи с сервером");
            }).finally(() => cartButton.disabled = false);
            return;
        }

        const pageButton = e.target.closest('.pagination-button');
        if (pageButton) {
            pageButton.disabled = true;

            const filterForm = document.querySelector('#filter-form');
            const targetPage = pageButton.dataset.page;
            updateProducts(filterForm, targetPage);

            pageButton.disabled = false;

            document.getElementById('filter-form')?.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        const productCard = e.target.closest('.product-card');
        if (productCard) {
            window.location.href = `/product.php?id=${productCard.dataset.product}`;
            return;
        }
    });
}