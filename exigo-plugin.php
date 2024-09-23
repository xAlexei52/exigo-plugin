<?php
/**
 * Plugin Name: Exigo Plugin
 * Plugin URI: http://tusitio.com/plugin
 * Description: Integración con WooCommerce para validación de clientes Exigo
 * Version: 1.0
 * Author: Alexei Palacios
 * Author URI: http://tusitio.com
 * Text Domain: exigo-plugin
 */

if (!defined('WPINC')) {
    die;
}

define('EXIGO_PLUGIN_VERSION', '1.0.0');
define('EXIGO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('EXIGO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Función para iniciar la sesión
function start_session() {
    if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);

// Incluir las clases necesarias
require_once EXIGO_PLUGIN_PATH . 'includes/class-database-handler.php';
require_once EXIGO_PLUGIN_PATH . 'includes/class-exigo-plugin.php';
require_once EXIGO_PLUGIN_PATH . 'public/class-exigo-public.php';

function run_exigo_plugin() {
    $plugin = new Exigo_Plugin();
    $plugin->run();
}

function init_exigo_hooks() {
    global $exigo_public;
    if (!isset($exigo_public)) {
        $db_handler = new Database_Handler();
        $exigo_public = new Exigo_Public($db_handler);
    }
    
    // Hooks para la funcionalidad pública
    add_filter('the_content', array($exigo_public, 'add_client_registration_fields'));
    add_action('wp_ajax_process_exigo_form', array($exigo_public, 'process_exigo_ajax_form'));
    add_action('wp_ajax_nopriv_process_exigo_form', array($exigo_public, 'process_exigo_ajax_form'));
    
    // Hooks adicionales para la validación del cliente y modificación de URL
    add_action('template_redirect', array($exigo_public, 'check_customer_validation'));
    add_filter('woocommerce_get_checkout_url', array($exigo_public, 'modify_checkout_url'));
}

// Usar hooks de WordPress para iniciar el plugin y configurar los hooks
add_action('plugins_loaded', 'run_exigo_plugin');
add_action('init', 'init_exigo_hooks', 20); // Cambiado a prioridad 20

// Registro de activación del plugin
register_activation_hook(__FILE__, 'exigo_plugin_activate');

function exigo_plugin_activate() {
    // Aquí puedes añadir cualquier lógica necesaria para la activación del plugin
    // Por ejemplo, crear tablas en la base de datos si es necesario
}

// Añadir manejo de errores
function exigo_error_handler($errno, $errstr, $errfile, $errline) {
    error_log("Exigo Plugin Error: [$errno] $errstr - $errfile:$errline");
    return true;
}
set_error_handler('exigo_error_handler');