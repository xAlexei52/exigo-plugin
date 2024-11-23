// js/exigo-public.js
jQuery(document).ready(function($) {
    // Elementos del DOM
    const loader = $('#exigo-loader');
    const recruiterInfo = $('#recruiter-info');
    const registrationForm = $('#exigo-registration-form');
    const returningCustomers = $('.returning-customers');
    const newCustomerForm = $('#exigo-new-customer-form');

    // Funciones de utilidad
    function showLoader() {
        loader.show();
    }

    function hideLoader() {
        loader.hide();
    }

    function displayRecruiterInfo(info) {
        console.log('Mostrando información del reclutador:', info);
        const details = $('.recruiter-details');
        
        if (!info || !info.id || !info.name) {
            console.error('Información del reclutador incompleta:', info);
            return;
        }
    
        const html = `
            <div class="recruiter-info-box">
                <p><strong>ID del Reclutador:</strong> ${info.id}</p>
                <p><strong>Nombre:</strong> ${info.name}</p>
            </div>
        `;
    
        details.html(html);
        recruiterInfo.fadeIn(300); // Usando fadeIn para una transición suave
        recruiterInfo.addClass('visible');
    }

    // Búsqueda de reclutador
    newCustomerForm.on('submit', function(e) {
        e.preventDefault();
        showLoader();
        registrationForm.hide();
        recruiterInfo.hide();

        const formData = new FormData(this);
        formData.append('action', 'process_exigo_form');
        formData.append('security', exigo_ajax.nonce);
        formData.append('exigo_new_customer', '1');

        $.ajax({
            url: exigo_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            // En el archivo exigo-public.js, modifica la parte del éxito del AJAX:
            success: function(response) {
                hideLoader();
                console.log('Respuesta del servidor:', response);
                
                if (response.success && response.recruiter_info) {
                    displayRecruiterInfo(response.recruiter_info);
                } else {
                    alert(response.message || 'Error al buscar el reclutador');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                console.error('Error en la petición:', {xhr, status, error});
                alert('Error de conexión. Por favor, inténtelo de nuevo.');
            }
        });
    });

    // Confirmar reclutador
    $('#confirm-recruiter').on('click', function() {
        const recruiterId = $('#exigo-new-customer-form [name="recruiter_id"]').val();
        $('[name="confirmed_recruiter_id"]').val(recruiterId);
        registrationForm.slideDown();
        returningCustomers.hide();
    });

    // Buscar otro reclutador
    $('#cancel-recruiter').on('click', function() {
        recruiterInfo.hide();
        newCustomerForm[0].reset();
        returningCustomers.show();
    });

    // Login de cliente existente
    $('#exigo-login-form').on('submit', function(e) {
        e.preventDefault();
        showLoader();

        const formData = new FormData(this);
        formData.append('action', 'process_exigo_form');
        formData.append('security', exigo_ajax.nonce);
        formData.append('exigo_login', '1');

        $.ajax({
            url: exigo_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoader();
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                hideLoader();
                alert('Error de conexión. Por favor, inténtelo de nuevo.');
            }
        });
    });

    // Para debugging
    $(document).ajaxSend(function(event, xhr, settings) {
        console.log('Enviando petición AJAX:', settings.url, settings.data);
    });

    $(document).ajaxComplete(function(event, xhr, settings) {
        console.log('Respuesta AJAX recibida:', xhr.responseJSON);
    });

    $('#exigo-registration-form').on('submit', function(e) {
        e.preventDefault();
        showLoader();
    
        const formData = new FormData(this);
        formData.append('action', 'process_exigo_form');
        formData.append('security', exigo_ajax.nonce);
        formData.append('exigo_complete_registration', '1');
    
        $.ajax({
            url: exigo_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoader();
                console.log('Respuesta registro:', response);
                
                if (response.success) {
                    alert(response.data.message);
                    window.location.href = response.data.redirect;
                } else {
                    alert(response.data.message || 'Error al crear la cuenta');
                    console.error('Error detallado:', response);
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                console.error('Error en la petición:', {xhr, status, error});
                alert('Error de conexión. Por favor, inténtelo de nuevo.');
            }
        });
    });
});