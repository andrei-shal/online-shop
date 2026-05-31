import { showAlert } from "../../../script/ui/alerts.js";
import {touchAPI} from "../lib/api.js";

export function updateUsers(form, page) {
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

    loadUsers(searchParams);
}

export function loadUsers(searchParams) {
    fetch(`/admin/content/users-content.php?${searchParams.toString()}`)
        .then(res => res.text())
        .then(html => {
            const container = document.querySelector('#users-content');
            if (container) container.innerHTML = html;
        }).catch(() => showAlert('Ошибка сервера'));
}

export function initUsersContent() {
    const container = document.querySelector('#users-content');
    if (!container) return;

    container.addEventListener('click', (e) => {
        const pageButton = e.target.closest('.pagination-button');
        if (pageButton) {
            if (pageButton.parentElement?.classList.contains('disabled')) return;

            const filterForm = document.querySelector('#filter-form');
            const targetPage = pageButton.dataset.page;
            updateUsers(filterForm, targetPage);

            document.getElementById('filter-form')?.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        const userRow = e.target.closest('.user-row');
        if (userRow && !e.target.closest('select') && !e.target.closest('a') && !e.target.closest('button')) {
            const userId = userRow.dataset.userId;
            if (userId) {
                window.location.href = `/admin/user.php?id=${userId}`;
            }
        }
    });

    container.addEventListener('change', (e) => {
        const select = e.target.closest('.status-select');
        if (!select) return;

        const userId = select.dataset.user;
        const newRole = select.value;

        select.disabled = true;

        touchAPI("users/update-role", {"userId": userId, "role": newRole})
            .then(res => res.json()).then(data => {
                if (!data.success) {
                    showAlert(data.error);

                    const filterForm = document.querySelector('#filter-form');
                    const targetPage = 1;
                    updateUsers(filterForm, targetPage);
                }
            })
            .catch((err) => {
                showAlert("Ошибка связи с сервером");

                const filterForm = document.querySelector('#filter-form');
                const targetPage = 1;
                updateUsers(filterForm, targetPage);
            })
            .finally(() => {
                select.disabled = false;
            });
    });
}