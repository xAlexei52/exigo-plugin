<?php
//includes/class-exigo-plugin.php
class Exigo_Plugin {
    protected $api_handler;
    protected $public;

    public function __construct() {
        $this->load_dependencies();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        $this->api_handler = new Exigo_API_Handler();
        $this->public = new Exigo_Public($this->api_handler);
    }

    private function define_public_hooks() {
        // Scripts y estilos
        add_action('wp_enqueue_scripts', array($this->public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this->public, 'enqueue_scripts'));
        
        // Formulario de registro
        add_filter('the_content', array($this->public, 'add_client_registration_fields'));
        
        // AJAX handlers
        add_action('wp_ajax_process_exigo_form', array($this->public, 'process_exigo_ajax_form'));
        add_action('wp_ajax_nopriv_process_exigo_form', array($this->public, 'process_exigo_ajax_form'));
        
        // Validaciones de WooCommerce
        add_action('template_redirect', array($this, 'exigo_redirect_checkout'));
        add_action('woocommerce_checkout_process', array($this, 'exigo_validate_checkout'));
        add_action('woocommerce_before_checkout_form', array($this, 'exigo_validate_checkout_access'));
        add_action('woocommerce_before_cart', array($this, 'exigo_validate_checkout_access'));
    }

    public function run() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }

    public function exigo_redirect_checkout() {
        // Si está en checkout y no está validado, redirigir
        if ((is_checkout() || is_cart()) && !isset($_SESSION['customer_validated'])) {
            wp_redirect(home_url('/registro-cliente'));
            exit;
        }
    }

    public function exigo_validate_checkout() {
        // Evitar el proceso de checkout si no está validado
        if (!isset($_SESSION['customer_validated'])) {
            wc_add_notice(__('Por favor, valida tu cuenta antes de continuar con la compra.', 'exigo-plugin'), 'error');
            wp_redirect(home_url('/registro-cliente'));
            exit;
        }
    }

    public function exigo_validate_checkout_access() {
        // Validar acceso al checkout/cart
        if (!isset($_SESSION['customer_validated'])) {
            wp_redirect(home_url('/registro-cliente'));
            exit;
        }
    }
}