<?php
class Exigo_Public {
    private $db_handler;

    public function __construct($db_handler) {
        $this->db_handler = $db_handler;
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enqueue_styles() {
        wp_enqueue_style('exigo-public-style', plugin_dir_url(__FILE__) . '../css/exigo-public.css');
    }

    public function check_customer_validation() {
        if (!is_user_logged_in() || !$this->is_customer_validated()) {
            wp_safe_redirect(home_url('/registro-cliente'));
            exit;
        }
    }

    public function is_customer_validated() {
        return isset($_SESSION['customer_validated']) && $_SESSION['customer_validated'] === true;
    }

    public function modify_checkout_url($url) {
        if (!$this->is_customer_validated()) {
            return home_url('/registro-cliente');
        }
        return $url;
    }

    public function add_client_registration_fields($content) {
        if (is_page('registro-cliente')) {
            ob_start();
            ?>
            <div class="exigo-registration-options">
                <h2>Are you an existing customer?</h2>
                
                <div class="existing-customer">
                    <h3>Existing Customers</h3>
                    <form id="exigo-login-form" method="post">
                        <input type="text" name="username" placeholder="Username or Exigo ID" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <input type="submit" name="exigo_login" value="Login">
                    </form>
                </div>

                <div class="new-customer">
                    <h3>New Customers</h3>
                    <p>Gracias por comprar en Stemtech hoy. Si esta es su primera vez comprando con nosotros, o si no tiene una cuenta todavía, haga clic aquí para terminar de procesar su pedido</p>
                    <form id="exigo-new-customer-form" method="post">
                        <input type="text" name="recruiter_id" placeholder="ID del reclutador" required>
                        <input type="submit" name="exigo_new_customer" value="Continuar">
                    </form>
                </div>

                <div id="exigo-loader" style="display: none;">
                    Verificando información...
                </div>
            </div>

            <script>
            jQuery(document).ready(function($) {
                $('#exigo-login-form, #exigo-new-customer-form').on('submit', function(e) {
                    e.preventDefault();
                    $('#exigo-loader').show();

                    var form = $(this);
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: form.serialize() + '&action=process_exigo_form',
                        success: function(response) {
                            $('#exigo-loader').hide();
                            if (response.success) {
                                alert(response.data.message);
                                window.location.href = response.data.redirect;
                            } else {
                                alert(response.data.message);
                            }
                        },
                        error: function() {
                            $('#exigo-loader').hide();
                            alert('Ocurrió un error. Por favor, inténtelo de nuevo.');
                        }
                    });
                });
            });
            </script>
            <?php
            $registration_content = ob_get_clean();
            $content .= $registration_content;
        }
        return $content;
    }

    public function process_exigo_ajax_form() {
        if (isset($_POST['exigo_login'])) {
            $this->process_existing_customer();
        } elseif (isset($_POST['exigo_new_customer'])) {
            $this->process_new_customer();
        } else {
            wp_send_json_error(['message' => 'Acción no válida']);
        }
    }

    private function process_existing_customer() {
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];

        $conexion = $this->db_handler->obtener_conexion();
        
        if ($conexion) {
            $stmt = $conexion->prepare("SELECT * FROM exigo_clientes WHERE nombre = :username OR id_exigo = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['customer_validated'] = true;
                $_SESSION['cliente_id'] = $user['id_exigo'];
                wp_send_json_success([
                    'message' => 'Login exitoso. Redirigiendo al carrito...',
                    'redirect' => wc_get_cart_url()
                ]);
            } else {
                wp_send_json_error(['message' => 'Credenciales inválidas. Por favor, verifique su usuario/ID y contraseña.']);
            }
        } else {
            wp_send_json_error(['message' => 'Error de conexión. Por favor, inténtelo de nuevo más tarde.']);
        }
        
        $this->db_handler->cerrar_conexion();
    }

    private function process_new_customer() {
        $recruiter_id = sanitize_text_field($_POST['recruiter_id']);

        $conexion = $this->db_handler->obtener_conexion();
        
        if ($conexion) {
            $stmt = $conexion->prepare("SELECT * FROM exigo_clientes WHERE id_exigo = :recruiter_id");
            $stmt->bindParam(':recruiter_id', $recruiter_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['customer_validated'] = true;
                $_SESSION['recruiter_id'] = $recruiter_id;
                wp_send_json_success([
                    'message' => 'ID de reclutador válido. Redirigiendo al checkout...',
                    'redirect' => wc_get_checkout_url()
                ]);
            } else {
                wp_send_json_error(['message' => 'ID de reclutador no encontrado. Por favor, verifique el ID e intente de nuevo.']);
            }
        } else {
            wp_send_json_error(['message' => 'Error de conexión. Por favor, inténtelo de nuevo más tarde.']);
        }
        
        $this->db_handler->cerrar_conexion();
    }
}