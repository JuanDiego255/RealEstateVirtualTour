/**
 * Sistema de Favoritos
 * Maneja agregar/eliminar propiedades de favoritos con AJAX
 */

(function($) {
    'use strict';

    // CSRF Token para peticiones AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /**
     * Toggle favorito (agregar o eliminar)
     */
    window.toggleFavorite = function(propertyId, button) {
        const $btn = $(button);
        const $icon = $btn.find('i');
        const isFavorite = $icon.hasClass('fa-heart'); // filled (es favorito actualmente)

        // Prevenir múltiples clicks
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);

        const url = isFavorite
            ? `/favorites/${propertyId}`
            : `/favorites/${propertyId}`;

        const method = isFavorite ? 'DELETE' : 'POST';

        $.ajax({
            url: url,
            type: method,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Toggle icono
                    if (response.action === 'added') {
                        $icon.removeClass('fa-heart-o').addClass('fa-heart');
                        $btn.removeClass('btn-outline-danger').addClass('btn-danger');
                        showNotification('success', response.message || 'Agregado a favoritos');
                    } else if (response.action === 'removed') {
                        $icon.removeClass('fa-heart').addClass('fa-heart-o');
                        $btn.removeClass('btn-danger').addClass('btn-outline-danger');
                        showNotification('info', response.message || 'Eliminado de favoritos');

                        // Si estamos en la página de favoritos, remover el card
                        if (window.location.pathname === '/favorites') {
                            $btn.closest('.col-md-4, .col-lg-3, .property-card-col').fadeOut(400, function() {
                                $(this).remove();
                                // Si no quedan favoritos, mostrar mensaje
                                if ($('.property-card-col').length === 0) {
                                    $('.properties-grid').html(`
                                        <div class="col-12 text-center py-5">
                                            <i class="fa fa-heart-o fa-4x text-muted mb-3"></i>
                                            <h4>No tienes favoritos</h4>
                                            <p class="text-muted">Explora propiedades y guarda tus favoritas aquí</p>
                                            <a href="/" class="btn btn-primary mt-3">Explorar Propiedades</a>
                                        </div>
                                    `);
                                }
                            });
                        }
                    }
                } else {
                    showNotification('warning', response.message || 'Ya está en favoritos');
                }
            },
            error: function(xhr) {
                let msg = 'Error al actualizar favoritos';
                if (xhr.status === 401) {
                    msg = 'Debes iniciar sesión para guardar favoritos';
                    // Opcional: redirigir a login
                    setTimeout(() => window.location.href = '/login', 1500);
                }
                showNotification('error', msg);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    };

    /**
     * Mostrar notificación temporal
     */
    function showNotification(type, message) {
        // Tipos: success, error, info, warning
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'info': 'alert-info',
            'warning': 'alert-warning'
        }[type] || 'alert-info';

        const $alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                 style="top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <strong>${message}</strong>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `);

        $('body').append($alert);

        // Auto-cerrar después de 3 segundos
        setTimeout(function() {
            $alert.alert('close');
        }, 3000);
    }

    /**
     * Inicializar tooltips para botones de favorito
     */
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

})(jQuery);
