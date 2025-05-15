<?php
//public/class-exigo-public.php
class Exigo_Public {
    private $api_handler;

    public function __construct($api_handler) {
        $this->api_handler = $api_handler;
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('the_content', array($this, 'add_client_registration_fields'));
        
        // Hooks AJAX
        add_action('wp_ajax_process_exigo_form', array($this, 'process_exigo_ajax_form'));
        add_action('wp_ajax_nopriv_process_exigo_form', array($this, 'process_exigo_ajax_form'));

        //WooCommerce Hooks
        add_action('woocommerce_thankyou', array($this, 'process_exigo_order'), 10, 1);
    }

    public function enqueue_styles() {
        wp_enqueue_style('exigo-public-style', plugin_dir_url(__FILE__) . '../css/exigo-plugin.css');
    }

    public function enqueue_scripts() {
        wp_enqueue_script('exigo-public-script', plugin_dir_url(__FILE__) . '../js/exigo-public.js', array('jquery'), EXIGO_PLUGIN_VERSION, true);
        wp_localize_script('exigo-public-script', 'exigo_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('exigo_ajax_nonce')
        ));
    }


    public function add_client_registration_fields($content) {
        if (is_page('registro-cliente')) {
            ob_start();
            ?>
            <div class="exigo-registration-container">
                <h1 class="exigo-main-title">ARE YOU AN EXISTING CUSTOMER?</h1>
                
                <div class="exigo-registration-sections">
                    <!-- Sección New Customers -->
                    <div class="exigo-section new-customers">
                        <h2>New Customers</h2>
                        <p>Gracias por comprar en Stemtech hoy. Si esta es su primera vez comprando con nosotros, o si no tiene una cuenta todavía, haga clic aquí para terminar de procesar su pedido.</p>
                        
                        <!-- Paso 1: Buscar Reclutador -->
                        <div class="registration-step" id="step-recruiter-search">
                            <form id="exigo-new-customer-form" method="post">
                                <div class="form-group">
                                    <label for="recruiter_id">ID del Reclutador</label>
                                    <input type="text" id="recruiter_id" name="recruiter_id" placeholder="Ingrese el ID del reclutador" required>
                                </div>
                                <button type="submit" name="exigo_new_customer" class="exigo-button">Buscar Reclutador</button>
                            </form>
                        </div>

                        <!-- Paso 2: Confirmación del Reclutador -->
                        <div id="recruiter-info" class="registration-step" style="display: none;">
                            <h3>Información del Reclutador</h3>
                            <div class="recruiter-details"></div>
                            <div class="recruiter-actions">
                                <button type="button" id="confirm-recruiter" class="exigo-button">Confirmar y Continuar</button>
                                <button type="button" id="cancel-recruiter" class="exigo-button button-secondary">Buscar Otro</button>
                            </div>
                        </div>

                        <!-- Paso 3: Formulario de Registro -->
                        <form id="exigo-registration-form" class="registration-step" style="display: none;" method="post">
                            <h3>Información de Registro</h3>
                            <input type="hidden" name="confirmed_recruiter_id" value="">
                            
                            <div class="form-section">
                                <h4>Información Personal</h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstName" class="required">Nombre</label>
                                        <input type="text" id="firstName" name="firstName" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName" class="required">Apellidos</label>
                                        <input type="text" id="lastName" name="lastName" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email" class="required">Correo Electrónico</label>
                                        <input type="email" id="email" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="required">Teléfono</label>
                                        <input type="tel" id="phone" name="phone" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h4>Dirección</h4>
                                <div class="form-group">
                                    <label for="address1" class="required">Dirección Línea 1</label>
                                    <input type="text" id="address1" name="address1" required>
                                </div>
                                <div class="form-group">
                                    <label for="address2">Dirección Línea 2 (Opcional)</label>
                                    <input type="text" id="address2" name="address2">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city" class="required">Ciudad</label>
                                        <input type="text" id="city" name="city" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="state" class="required">Estado</label>
                                        <select id="state" name="state" required>
                                            <option value="">Seleccione un estado</option>
                                            <option value="AGU">Aguascalientes</option>
                                            <option value="BCN">Baja California</option>
                                            <option value="BCS">Baja California Sur</option>
                                            <option value="CAM">Campeche</option>
                                            <option value="CHP">Chiapas</option>
                                            <option value="CHH">Chihuahua</option>
                                            <option value="CMX">Ciudad de México</option>
                                            <option value="COA">Coahuila</option>
                                            <option value="COL">Colima</option>
                                            <option value="DUR">Durango</option>
                                            <option value="GUA">Guanajuato</option>
                                            <option value="GRO">Guerrero</option>
                                            <option value="HID">Hidalgo</option>
                                            <option value="JAL">Jalisco</option>
                                            <option value="MEX">Estado de México</option>
                                            <option value="MIC">Michoacán</option>
                                            <option value="MOR">Morelos</option>
                                            <option value="NAY">Nayarit</option>
                                            <option value="NLE">Nuevo León</option>
                                            <option value="OAX">Oaxaca</option>
                                            <option value="PUE">Puebla</option>
                                            <option value="QUE">Querétaro</option>
                                            <option value="ROO">Quintana Roo</option>
                                            <option value="SLP">San Luis Potosí</option>
                                            <option value="SIN">Sinaloa</option>
                                            <option value="SON">Sonora</option>
                                            <option value="TAB">Tabasco</option>
                                            <option value="TAM">Tamaulipas</option>
                                            <option value="TLA">Tlaxcala</option>
                                            <option value="VER">Veracruz</option>
                                            <option value="YUC">Yucatán</option>
                                            <option value="ZAC">Zacatecas</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="postalCode" class="required">Código Postal</label>
                                        <input type="text" id="postalCode" name="postalCode" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h4>Información de Cuenta</h4>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="loginName" class="required">Nombre de Usuario</label>
                                        <input type="text" id="loginName" name="loginName" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmPassword">Contraseña</label>
                                        <input type="password" id="password" name="password" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="confirmPassword" class="required">Confirmar Contraseña</label>
                                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="exigo_complete_registration" class="exigo-button">Completar Registro</button>
                                <button type="button" id="back-to-recruiter" class="exigo-button button-secondary">Volver a Reclutador</button>
                            </div>
                        </form>
                    </div>

                    <!-- Sección Returning Customers -->
                    <div class="exigo-section returning-customers">
                        <h2>Returning Customers</h2>
                        <p>¡Bienvenido de vuelta! Ingrese su nombre de usuario y contraseña para usar la información almacenada en su cuenta.</p>
                        
                        <form id="exigo-login-form" method="post">
                            <div class="form-group">
                                <label for="username">Nombre de usuario</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <button type="submit" name="exigo_login" class="exigo-button">INICIAR SESIÓN</button>
                        </form>
                    </div>
                </div>

                <div id="exigo-loader" style="display: none;">
                    <div class="loader-content">
                        <div class="spinner"></div>
                        <p>Verificando información...</p>
                    </div>
                </div>
            </div>
            <?php
            $registration_content = ob_get_clean();
            $content .= $registration_content;
        }
        return $content;
    }

    public function process_exigo_ajax_form() {
        check_ajax_referer('exigo_ajax_nonce', 'security');

        if (isset($_POST['exigo_login'])) {
            $this->process_existing_customer();
        } elseif (isset($_POST['exigo_new_customer'])) {
            $this->process_recruiter_search();
        } elseif (isset($_POST['exigo_complete_registration'])) {
            $this->process_complete_registration();
        } else {
            wp_send_json_error([
                'message' => 'Acción no válida',
                'raw_request' => $_POST
            ]);
        }
    }

    private function process_existing_customer() {
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];

        $result = $this->api_handler->authenticate_customer($username, $password);
        
        if ($result['success']) {
            $_SESSION['customer_validated'] = true;
            $_SESSION['cliente_id'] = $result['data']['customerID'];
            wp_send_json_success([
                'message' => 'Login exitoso',
                'redirect' => wc_get_cart_url(),
                'api_response' => $result
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Error de autenticación',
                'api_response' => $result
            ]);
        }
    }

    private function process_recruiter_search() {
        $recruiter_id = sanitize_text_field($_POST['recruiter_id']);
        $result = $this->api_handler->get_customer($recruiter_id);
        
        error_log('Resultado de búsqueda de reclutador: ' . print_r($result, true));
        
        if ($result['success'] && isset($result['data']['customers']) && !empty($result['data']['customers'])) {
            $recruiter = $result['data']['customers'][0];
            
            $response_data = [
                'success' => true,
                'message' => 'Reclutador encontrado',
                'recruiter_info' => [
                    'id' => $recruiter['customerID'],
                    'name' => trim($recruiter['firstName'] . ' ' . $recruiter['lastName'])
                ]
            ];
    
            error_log('Enviando respuesta: ' . print_r($response_data, true));
            wp_send_json($response_data);
        } else {
            error_log('Error al buscar reclutador: ' . print_r($result, true));
            wp_send_json_error([
                'message' => 'Reclutador no encontrado. Por favor, verifique el ID e intente de nuevo.',
                'debug' => $result
            ]);
        }
    }
    
    private function process_complete_registration() {
        error_log('Iniciando registro de cliente nuevo');
        
        // Verificar que tenemos el ID del reclutador
        $recruiter_id = sanitize_text_field($_POST['confirmed_recruiter_id']);
        if (empty($recruiter_id)) {
            wp_send_json_error([
                'message' => 'No se encontró el ID del reclutador'
            ]);
            return;
        }
    
        // Validar que las contraseñas coincidan
        if ($_POST['password'] !== $_POST['confirmPassword']) {
            wp_send_json_error([
                'message' => 'Las contraseñas no coinciden'
            ]);
            return;
        }
    
        // Validar campos requeridos
        $required_fields = ['firstName', 'lastName', 'email', 'phone', 'loginName', 'password', 
                           'address1', 'city', 'state', 'postalCode'];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error([
                    'message' => "El campo {$field} es requerido"
                ]);
                return;
            }
        }
    
        // Preparar datos para la API
        $customer_data = [
            'firstName' => sanitize_text_field($_POST['firstName']),
            'lastName' => sanitize_text_field($_POST['lastName']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'loginName' => sanitize_text_field($_POST['loginName']),
            'password' => $_POST['password'],
            'enrollerId' => $recruiter_id,
            'mainAddress1' => sanitize_text_field($_POST['address1']),
            'mainAddress2' => sanitize_text_field($_POST['address2'] ?? ''),
            'mainCity' => sanitize_text_field($_POST['city']),
            'mainState' => sanitize_text_field($_POST['state']),
            'mainZip' => sanitize_text_field($_POST['postalCode'])
        ];
    
        error_log('Datos del cliente preparados: ' . print_r($customer_data, true));
    
        $result = $this->api_handler->create_customer($customer_data);
        
        error_log('Resultado de crear cliente: ' . print_r($result, true));
        
        if ($result['success']) {
            $_SESSION['customer_validated'] = true;
            $_SESSION['cliente_id'] = $result['data']['customerID'] ?? null;
            wp_send_json_success([
                'message' => 'Registro exitoso',
                'redirect' => wc_get_checkout_url(),
                'api_response' => $result
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Error al crear la cuenta: ' . ($result['data']['message'] ?? 'Error desconocido'),
                'api_response' => $result
            ]);
        }
    }

    private function map_state_code($wp_state) {
        $state_mapping = [
            'AG' => 'AGU',  // Aguascalientes
            'BC' => 'BCN',  // Baja California
            'BS' => 'BCS',  // Baja California Sur
            'CM' => 'CAM',  // Campeche
            'CS' => 'CHP',  // Chiapas
            'CH' => 'CHH',  // Chihuahua
            'DF' => 'CMX',  // Ciudad de México
            'CO' => 'COA',  // Coahuila
            'CL' => 'COL',  // Colima
            'DG' => 'DUR',  // Durango
            'GT' => 'GUA',  // Guanajuato
            'GR' => 'GRO',  // Guerrero
            'HG' => 'HID',  // Hidalgo
            'JA' => 'JAL',  // Jalisco
            'MX' => 'MEX',  // Estado de México
            'MI' => 'MIC',  // Michoacán
            'MO' => 'MOR',  // Morelos
            'NA' => 'NAY',  // Nayarit
            'NL' => 'NLE',  // Nuevo León
            'OA' => 'OAX',  // Oaxaca
            'PU' => 'PUE',  // Puebla
            'QT' => 'QUE',  // Querétaro
            'QR' => 'ROO',  // Quintana Roo
            'SL' => 'SLP',  // San Luis Potosí
            'SI' => 'SIN',  // Sinaloa
            'SO' => 'SON',  // Sonora
            'TB' => 'TAB',  // Tabasco
            'TM' => 'TAM',  // Tamaulipas
            'TL' => 'TLA',  // Tlaxcala
            'VE' => 'VER',  // Veracruz
            'YU' => 'YUC',  // Yucatán
            'ZA' => 'ZAC'   // Zacatecas
        ];

        return isset($state_mapping[$wp_state]) ? $state_mapping[$wp_state] : $wp_state;
    }

    public function process_exigo_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('No se pudo obtener la orden WC: ' . $order_id);
            return;
        }
    
        // Verificar si ya fue procesada
        $exigo_order_id = get_post_meta($order_id, '_exigo_order_id', true);
        if (!empty($exigo_order_id)) {
            return;
        }
    
        // Verificar cliente validado
        if (!isset($_SESSION['customer_validated']) || !isset($_SESSION['cliente_id'])) {
            $order->add_order_note('Error: Cliente no validado en Exigo');
            return;
        }
    
        // Preparar detalles de items
        $details = [];
        $total_items = 0;
        $order_total = 0;
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $item_price = floatval($item->get_total());
            $order_total += $item_price;
            
            // Get SKU from the product object, not the item
            $sku = '';
            if ($product) {
                $sku = $product->get_sku();
            }
            
            $details[] = [
                'itemCode' => $sku, // Using SKU from the product
                'quantity' => $item->get_quantity(),
                'warehouseID' => 3,
                'priceType' => 2,
                'price' => $item_price,
                'description' => $item->get_name(),
                'currencyCode' => 'mxp'
            ];
            $total_items += $item->get_quantity();
        }
    
        // Obtener y convertir el código de estado
        $wp_state = $order->get_shipping_state();
        $exigo_state = $this->map_state_code($wp_state);
    
        // Datos básicos requeridos por la API
        $exigo_order_data = [
            'customerID' => intval($_SESSION['cliente_id']),
            'orderStatus' => null,
            'orderDate' => current_time('c'),
            'currencyCode' => 'mxp',
            'warehouseID' => 3,
            'shipMethodID' => 1,
            'priceType' => 1,
            'orderType' => null,
            'firstName' => $order->get_shipping_first_name(),
            'lastName' => $order->get_shipping_last_name(),
            'address1' => $order->get_shipping_address_1(),
            'address2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'state' => $exigo_state, // Usando el código de estado mapeado
            'zip' => $order->get_shipping_postcode(),
            'country' => 'MX',
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'details' => $details,
            'suppressPackSlipPrice' => true
        ];
    
        try {
            $result = $this->api_handler->create_order($exigo_order_data);
            
            if ($result['success']) {
                update_post_meta($order_id, '_exigo_order_id', $result['data']['orderID']);
                
                // Crear una nota más detallada con información del pedido
                $order_note = sprintf(
                    '✅ Orden creada en Exigo exitosamente. ID: %s
                    📦 Resumen del pedido enviado:
                    - Cliente ID: %s
                    - Productos: %d items
                    - Total enviado: $%s MXN
                    - Nombre: %s %s
                    - Dirección: %s, %s, %s, %s
                    - Email: %s
                    - Teléfono: %s',
                    $result['data']['orderID'],
                    $_SESSION['cliente_id'],
                    $total_items,
                    number_format($order_total, 2),
                    $order->get_shipping_first_name(),
                    $order->get_shipping_last_name(),
                    $order->get_shipping_address_1(),
                    $order->get_shipping_city(),
                    $exigo_state,
                    $order->get_shipping_postcode(),
                    $order->get_billing_email(),
                    $order->get_billing_phone()
                );
                
                $order->add_order_note($order_note);
            } else {
                $error_details = sprintf(
                    '❌ Error al crear orden en Exigo:
                    Mensaje: %s
                    Status: %s
                    
                    📊 Datos enviados a la API:
                    %s
                    
                    💰 Total del pedido: $%s MXN
                    📦 Total items: %d',
                    $result['message'],
                    $result['status'],
                    json_encode($exigo_order_data, JSON_PRETTY_PRINT),
                    number_format($order_total, 2),
                    $total_items
                );
                $order->add_order_note($error_details);
                error_log('Error en Exigo - Datos enviados: ' . print_r($exigo_order_data, true));
            }
        } catch (Exception $e) {
            $error_message = sprintf(
                '❌ Error al procesar orden en Exigo: %s
                
                📊 Datos que se intentaron enviar:
                %s
                
                💰 Total del pedido: $%s MXN',
                $e->getMessage(),
                json_encode($exigo_order_data, JSON_PRETTY_PRINT),
                number_format($order_total, 2)
            );
            $order->add_order_note($error_message);
            error_log('Excepción en Exigo: ' . $e->getMessage());
        }
    }
}