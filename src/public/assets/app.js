/**
 * JavaScript para el sistema de tickets
 * Funcionalidades del lado del cliente
 */

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Confirmación antes de cerrar tickets
    const closeTicketForms = document.querySelectorAll('form[action*="ticket_close"]');
    closeTicketForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('¿Estás seguro de que deseas cerrar este ticket?')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-cerrar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Contador de caracteres para el título
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        const maxLength = titleInput.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.style.float = 'right';
        counter.style.color = 'var(--text-secondary)';
        
        titleInput.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            counter.textContent = `${remaining} caracteres restantes`;
            
            if (remaining < 20) {
                counter.style.color = 'var(--warning-color)';
            } else {
                counter.style.color = 'var(--text-secondary)';
            }
        });
        
        const titleGroup = titleInput.closest('.form-group');
        if (titleGroup) {
            const label = titleGroup.querySelector('label');
            if (label) {
                label.appendChild(counter);
            }
        }
    }
    
    // Validación del lado del cliente para formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                } else {
                    field.style.borderColor = 'var(--border-color)';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios.');
            }
        });
    });
    
    // Mejorar UX: Focus automático en el primer campo
    const firstInput = document.querySelector('input[autofocus]');
    if (firstInput) {
        firstInput.focus();
    }
    
    // Animación suave al hacer clic en enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Función de utilidad: mostrar notificación
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.boxShadow = 'var(--shadow-lg)';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// Detectar errores de red
window.addEventListener('online', () => {
    showNotification('Conexión restaurada', 'success');
});

window.addEventListener('offline', () => {
    showNotification('Sin conexión a internet', 'error');
});
