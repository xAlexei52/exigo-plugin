 
<?php
// Si no se llama desde WordPress, salir.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Aquí puedes añadir código para limpiar después de la desinstalación del plugin
// Por ejemplo, eliminar opciones, tablas personalizadas, etc.