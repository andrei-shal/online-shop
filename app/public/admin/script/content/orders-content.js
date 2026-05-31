import { showAlert } from "../../../script/ui/alerts.js";
import { touchAPI } from "../lib/api.js";

export function updateOrders(form, page) {
    let searchParams;

    if (form) {
        const formData = new FormData(form);
        searchParams = new URLSearchParams(formData);

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

    searchParams.set('pageSize', 10);

    loadOrders(searchParams);
}

export function loadOrders(searchParams) {
    fetch(`/admin/content/orders-content.php?${searchParams.toString()}`)
        .then(res => res.text())
        .then(html => {
            const container = document.querySelector('#orders-content');
            if (container) container.innerHTML = html;
        }).catch(() => showAlert('Ошибка сервера'));
}

export function initOrdersContent() {
    const container = document.querySelector('#orders-content');
    if (!container) return;

    container.addEventListener('click', (e) => {
        const pageButton = e.target.closest('.pagination-button');
        if (pageButton) {
            if (pageButton.parentElement?.classList.contains('disabled')) return;

            const filterForm = document.querySelector('#filter-form');
            const targetPage = pageButton.dataset.page;
            updateOrders(filterForm, targetPage);

            document.getElementById('filter-form')?.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        const orderRow = e.target.closest('.order-row');
        if (orderRow && !e.target.closest('select') && !e.target.closest('a') && !e.target.closest('button')) {
            const orderId = orderRow.dataset.orderId;
            if (orderId) {
                window.location.href = `/admin/order.php?id=${orderId}`;
            }
        }
    });

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

                const filterForm = document.querySelector('#filter-form');
                const targetPage = 1;
                updateOrders(filterForm, targetPage);
            }
        })
            .catch((err) => {
                showAlert("Ошибка связи с сервером");

                const filterForm = document.querySelector('#filter-form');
                const targetPage = 1;
                updateOrders(filterForm, targetPage);
            })
            .finally(() => {
                select.disabled = false;
            });
    });
}