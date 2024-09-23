<?php
class Exigo_Plugin {
    protected $db_handler;
    protected $public;

    public function __construct() {
        $this->load_dependencies();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        $this->db_handler = new Database_Handler();
        $this->public = new Exigo_Public($this->db_handler);
    }

    private function define_public_hooks() {
        add_action('woocommerce_before_checkout_form', array($this->public, 'check_customer_validation'));
        add_action('woocommerce_before_cart', array($this->public, 'check_customer_validation'));
        add_filter('woocommerce_get_checkout_url', array($this->public, 'modify_checkout_url'));
        // Cambiamos 'process_exigo_cliente_form' a 'process_exigo_ajax_form'
        add_action('wp_ajax_process_exigo_form', array($this->public, 'process_exigo_ajax_form'));
        add_action('wp_ajax_nopriv_process_exigo_form', array($this->public, 'process_exigo_ajax_form'));
    }

    public function run() {
        // Iniciar sesión si aún no está iniciada
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
}