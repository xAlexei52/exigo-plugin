<?php
//includes/class-exigo-api-handler.php
class Exigo_API_Handler {
    private $api_base_url;
    private $api_auth;

    public function __construct() {
        $this->api_base_url = 'https://stem-api.exigo.com/3.0/';
        
        // Obtener credenciales del .env
        $api_user = $_ENV['EXIGO_API_USER'] ?? '';
        $api_company = $_ENV['EXIGO_API_COMPANY_KEY'] ?? '';
        $api_password = $_ENV['EXIGO_API_PASSWORD'] ?? '';

        // Debug de credenciales
        error_log('API User: ' . $api_user);
        error_log('API Company: ' . $api_company);

        // Construir la autenticación
        $auth_string = $api_user . '@' . $api_company . ':' . $api_password;
        $this->api_auth = base64_encode($auth_string);

        error_log('Auth string (before base64): ' . $auth_string);
        error_log('Auth header (after base64): ' . $this->api_auth);
    }

    /**
     * Buscar un cliente por ID
     */
    public function get_customer($customer_id) {
        $url = $this->api_base_url . "customers?customerID=" . $customer_id;
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . $this->api_auth,
                'Content-Type' => 'application/json'
            )
        );

        error_log('Get customer request URL: ' . $url);
        error_log('Get customer request headers: ' . print_r($args['headers'], true));

        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            error_log('Get customer API Error: ' . $response->get_error_message());
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'raw_response' => $response
            );
        }

        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);
        $data = json_decode($body, true);

        error_log('Get customer response status: ' . $status);
        error_log('Get customer response body: ' . print_r($data, true));

        return array(
            'success' => $status === 200 && isset($data['recordCount']) && $data['recordCount'] > 0,
            'data' => $data,
            'status' => $status,
            'raw_response' => $response
        );
    }

    /**
     * Autenticar un cliente existente
     */
    public function authenticate_customer($username, $password) {
        $url = $this->api_base_url . "customers/authenticate";
        
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . $this->api_auth,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'loginName' => $username,
                'password' => $password
            ))
        );

        error_log('Auth request URL: ' . $url);
        error_log('Auth request body: ' . $args['body']);

        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            error_log('Auth API Error: ' . $response->get_error_message());
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'raw_response' => $response
            );
        }

        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);
        $data = json_decode($body, true);

        error_log('Auth response status: ' . $status);
        error_log('Auth response body: ' . print_r($data, true));

        return array(
            'success' => $status === 200 && isset($data['customerID']) && $data['customerID'] > 0,
            'data' => $data,
            'status' => $status,
            'raw_response' => $response
        );
    }

    /**
     * Crear un nuevo cliente
     */
    public function create_customer($customer_data) {
        $url = $this->api_base_url . "customers";
        
        error_log('Datos recibidos para crear cliente: ' . print_r($customer_data, true));
    
        $api_data = array(
            // Campos requeridos
            'firstName' => $customer_data['firstName'],
            'lastName' => $customer_data['lastName'],
            'email' => $customer_data['email'],
            'phone' => $customer_data['phone'],
            'mainAddress1' => $customer_data['mainAddress1'],
            'mainCity' => $customer_data['mainCity'],
            'mainState' => $customer_data['mainState'],
            'mainZip' => $customer_data['mainZip'],
            'mainCountry' => 'MX',
            'loginName' => $customer_data['loginName'],
            'loginPassword' => $customer_data['password'],
            'enrollerID' => intval($customer_data['enrollerId']),
            
            // Campos opcionales con valores por defecto
            'customerType' => 7,
            'customerStatus' => 1,
            'canLogin' => true,
            'insertEnrollerTree' => true,
            'mainAddress2' => $customer_data['mainAddress2'] ?? '',
            'currencyCode' => 'mxp',
            'languageID' => 1,
            'mainAddressVerified' => true,
            'defaultWarehouseID' => 3
        );
    
        error_log('Datos preparados para API: ' . print_r($api_data, true));
    
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . $this->api_auth,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($api_data)
        );
    
        error_log('Request body: ' . $args['body']);
    
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            error_log('Error en la petición: ' . $response->get_error_message());
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'raw_response' => $response
            );
        }
    
        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);
        $data = json_decode($body, true);
    
        error_log('Respuesta de la API - Status: ' . $status);
        error_log('Respuesta de la API - Body: ' . print_r($data, true));
    
        return array(
            'success' => $status === 200 || $status === 201,
            'data' => $data,
            'status' => $status,
            'raw_response' => $response
        );
    }
    /**
     * Método auxiliar para manejar errores
     */
    private function handle_api_error($response) {
        if (is_wp_error($response)) {
            return array(
                'error' => true,
                'message' => $response->get_error_message()
            );
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200 && $status !== 201) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            return array(
                'error' => true,
                'message' => $data['message'] ?? 'Error desconocido',
                'status' => $status,
                'data' => $data
            );
        }

        return false;
    }

    public function create_order($order_data) {
        $url = $this->api_base_url . "orders";
        
        // Log detallado de la petición
        error_log('=== INICIO PETICIÓN CREATE ORDER EXIGO ===');
        error_log('URL: ' . $url);
        error_log('Datos de orden: ' . print_r($order_data, true));
    
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . $this->api_auth,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($order_data)
        );
    
        // Log de los headers y body
        error_log('Headers: ' . print_r($args['headers'], true));
        error_log('Body: ' . $args['body']);
    
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            error_log('Error WP_Error: ' . $response->get_error_message());
            return array(
                'success' => false,
                'message' => 'Error de conexión: ' . $response->get_error_message(),
                'raw_response' => $response
            );
        }
    
        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);
        
        error_log('Código de respuesta: ' . $status);
        error_log('Respuesta body: ' . $body);
    
        $data = json_decode($body, true);
        
        // Log detallado de la respuesta
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Error al decodificar JSON: ' . json_last_error_msg());
            error_log('Body recibido: ' . $body);
        }
    
        $result = array(
            'success' => $status === 200 || $status === 201,
            'status' => $status,
            'raw_response' => $response
        );
    
        if ($result['success']) {
            $result['data'] = $data;
            $result['message'] = 'Orden creada exitosamente';
        } else {
            // Manejo detallado de errores
            if (!empty($data['message'])) {
                $result['message'] = $data['message'];
            } elseif (!empty($data['error'])) {
                $result['message'] = $data['error'];
            } elseif (!empty($data['errorMessage'])) {
                $result['message'] = $data['errorMessage'];
            } else {
                $result['message'] = 'Error en la respuesta de Exigo (Status: ' . $status . ')';
            }
            $result['data'] = $data;
        }
    
        error_log('Resultado final: ' . print_r($result, true));
        error_log('=== FIN PETICIÓN CREATE ORDER EXIGO ===');
    
        return $result;
    }
}