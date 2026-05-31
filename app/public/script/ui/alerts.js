export function showAlert(message, type = 'danger') {
    const alerts = document.querySelector('#alerts');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    `;
    alerts.appendChild(alert);
    setTimeout(() => {
        alert.remove();
    }, 5000);
}