/**
 * Common Notification System
 * Displays toast-like notifications across the application
 */

function showNotification(title, message, type = 'success') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'danger': 'alert-danger',
        'info': 'alert-info'
    }[type] || 'alert-success';

    const html = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <strong>${title}!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    const $alert = $(html);
    $('body').prepend($alert);

    setTimeout(() => {
        $alert.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 4000);
}

/**
 * Legacy alias for consistency with existing code
 */
function showAlert(message, type = 'success') {
    showNotification('Notification', message, type);
}

/**
 * Show success message with custom title
 */
function showSuccess(title, message) {
    showNotification(title, message, 'success');
}

/**
 * Show error message with custom title
 */
function showError(title, message) {
    showNotification(title, message, 'error');
}

/**
 * Show info message with custom title
 */
function showInfo(title, message) {
    showNotification(title, message, 'info');
}
