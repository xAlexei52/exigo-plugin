<?php
/**
 * Plugin Name: Exigo Plugin
 * Plugin URI: http://tusitio.com/plugin
 * Description: Integración con WooCommerce para validación de clientes Exigo
 * Version: 1.0
 * Author: Alexei Palacios
 * Author URI: http://tusitio.com
 * Text Domain: exigo-plugin
 * Domain Path: /languages
 */

if (!defined('WPINC')) {
    die;
}

define('EXIGO_PLUGIN_VERSION', '1.0.0');
define('EXIGO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('EXIGO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Cargar Composer autoload
require_once EXIGO_PLUGIN_PATH . 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(EXIGO_PLUGIN_PATH);
$dotenv->load();

// Incluir las clases necesarias
require_once EXIGO_PLUGIN_PATH . 'includes/class-exigo-plugin.php';
require_once EXIGO_PLUGIN_PATH . 'includes/class-exigo-api-handler.php';
require_once EXIGO_PLUGIN_PATH . 'public/class-exigo-public.php';

function run_exigo_plugin() {
    $plugin = new Exigo_Plugin();
    $plugin->run();
}

add_action('plugins_loaded', 'run_exigo_plugin');