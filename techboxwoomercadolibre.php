<?php
session_start();
/**
 * @package TechboxWooMercadoLibre
 */

/**
 * Plugin Name: Techbox Woo
 * Plugin URI: http://www.mywebsite.com/my-first-plugin
 * Description: Example plugin for detect Products TODO sync with Mercado libre
 * Version: 1.0
 * Author: Carlos Alva Sandoval
 * Author URI: https://www.linkedin.com/in/carlosalva/
 */

defined('ABSPATH') or die('Hey, what are you doing here? You silly human!');
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

use Inc\Meli;

class TechboxWooMercadoLibre
{
    public $plugin;

    public function __construct()
    {
        $this->plugin = plugin_basename(__FILE__);

    }
    public function register()
    {
        add_action('admin_menu', [$this, 'plugin_setup_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('transition_post_status', [$this, 'save_product'], 10, 3);
        //add_action('before_delete_post', [$this, 'before_delete_product'],10, 3);
        add_filter("plugin_action_links_$this->plugin", array($this, 'settings_link'));
    }

    public function settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=techbox-woo-mercado-libre">Settings</a>';
        array_push($links, $settings_link);
        return $links;
    }

    private function login()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "techbox_woo_mercado_libre";
        $setting    = $wpdb->get_row("SELECT * FROM {$table_name}", OBJECT);
        if ($setting->meli_app_id && $setting->meli_secret_key && $setting->meli_site_id) {
            $meli_redirect_uri = get_site_url(null, 'wp-admin/admin.php?page=techbox-woo-mercado-libre', 'https');
            $meli              = new Meli($setting->meli_app_id, $setting->meli_secret_key);
            $link              = '<h5>Sincronizar con Mercado libre</h5>';
            $link .= '<a class="btn btn-outline-dark" href="' . $meli->getAuthUrl($meli_redirect_uri, $meli::$AUTH_URL[$setting->meli_site_id]) .
                '"> <i class="fas fa-sync"></i> Sincronizar Ahora</a>';

            if ($_GET['code'] || $_SESSION['access_token']) {
                // If code exist and session is empty
                if ($_GET['code'] && !$_SESSION['access_token']) {
                    // //If the code was in get parameter we authorize
                    try {
                        $user = $meli->authorize($_GET['code'], $meli_redirect_uri);
                        if (isset($user['body']->error)) {
                            return $link;
                            exit;
                        }
                        // Now we create the sessions with the authenticated user
                        $_SESSION['access_token']  = $user['body']->access_token;
                        $_SESSION['expires_in']    = time() + $user['body']->expires_in;
                        $_SESSION['refresh_token'] = $user['body']->refresh_token;
                        $_SESSION['user_id']       = $user['body']->user_id;

                    } catch (Exception $e) {
                        echo "Exception: ", $e->getMessage(), "\n";
                    }
                    $link = '<h5> <i class="fas fa-check" style="color:green"></i> Sincronizado correctamente con Mercado Libre <i class="fas fa-handshake"></i></h5>';
                } else {
                    // We can check if the access token in invalid checking the time
                    if ($_SESSION['expires_in'] < time()) {
                        try {
                            // Make the refresh proccess
                            $refresh = $meli->refreshAccessToken();
                            // Now we create the sessions with the new parameters
                            $_SESSION['access_token']  = $refresh['body']->access_token;
                            $_SESSION['expires_in']    = time() + $refresh['body']->expires_in;
                            $_SESSION['refresh_token'] = $refresh['body']->refresh_token;
                            $_SESSION['user_id']       = $refresh['body']->user_id;
                        } catch (Exception $e) {
                            echo "Exception: ", $e->getMessage(), "\n";
                        }
                    }
                    $link = '<h5> <i class="fas fa-check" style="color:green"></i> Sincronizado correctamente con Mercado Libre <i class="fas fa-handshake"></i></h5>';
                }

                /*   echo '<pre>';
            print_r($_SESSION);
            echo '</pre>'; */
            }
            /* var_dump($_SESSION); */

        }
        return $link ? $link : '';
    }

    public function activate()
    {
        global $wpdb;
        $nombreTabla = $wpdb->prefix . "techbox_woo_mercado_libre";
        require_once ABSPATH . '/wp-admin/includes/upgrade.php';

        $created = dbDelta(
            "CREATE TABLE $nombreTabla (
                id  int(11) NOT NULL ,
                techbox_token  varchar(50) NULL ,
                meli_app_id varchar(50) NULL,
                meli_secret_key  varchar(50) NULL,
                $setting->meli_site_id  char(3) NULL ,
                PRIMARY KEY (id)
                );"
        );

        $wpdb->insert(
            $nombreTabla,
            array(
                'id' => 1,
            )
        );
        flush_rewrite_rules();
    }

    public function desactivate()
    {
        flush_rewrite_rules();
    }

    public function plugin_setup_menu()
    {
        add_menu_page('TechBoxWooMercadoLibre', 'TechBoxWooMercadoLibre', 'manage_options', 'techbox-woo-mercado-libre', [$this, 'load_form_settings'], 'dashicons-admin-links');
    }

    public function load_form_settings()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "techbox_woo_mercado_libre";
        if ($_POST) {

            $wpdb->update($table_name, [
                'techbox_token'               => $_POST['techbox_token'],
                'meli_secret_key'             => $_POST['meli_secret_key'],
                'meli_app_id'                 => $_POST['meli_app_id'],
                '$setting->meli_redirect_uri' => $_POST['$setting->meli_redirect_uri'],
                '$setting->meli_site_id'      => $_POST['$setting->meli_site_id'],
            ],
                ['id' => 1]);
            wp_redirect('admin.php?page=techbox-woo-mercado-libre&message=Los-cambios-se-guardaron-exitosmente');
        } else {
            $siteIds = [
                "MLA" => "Argentina", // Argentina
                "MLB" => "Brasil", // Brasil
                "MCO" => "Colombia", // Colombia
                "MCR" => "Costa Rica", // Costa Rica
                "MEC" => "Ecuador", // Ecuador
                "MLC" => "Chile", // Chile
                "MLM" => "Mexico", // Mexico
                "MLU" => "Uruguay", // Uruguay
                "MLV" => "Venezuela", // Venezuela
                "MPA" => "Panama", // Panama
                "MPE" => "Peru", // Peru
                "MPT" => "Portugal", // Prtugal
                "MRD" => "Dominicana", // Dominicana
            ];
            $setting          = $wpdb->get_row("SELECT * FROM {$table_name}", OBJECT);
            $link_integration = $this->login();
            $url_img          = plugins_url('assets/img/mercadoLibreImgAppCredenciales.JPG', __FILE__);
            require_once plugin_dir_path(__FILE__) . 'templates/form_settings.php';
        }

    }

    public function enqueue()
    {
        wp_enqueue_script('mypluginscript', plugins_url('/assets/js/myscript.js', __FILE__));
    }
    //hooks crud de productos
    public function save_product($new_status, $old_status, $post)
    {
        if ($post->post_type !== 'product') {
            return;
        }
        // $this->login();
        if ('publish' !== $old_status && 'publish' === $new_status) { //create a new product

        }

        if ('trash' !== $old_status && 'trash' === $new_status) { //delete product

        }
        global $wpdb;
        $table_name = $wpdb->prefix . "techbox_woo_mercado_libre";
        $setting    = $wpdb->get_row("SELECT * FROM {$table_name}", OBJECT);
        if ($setting->techbox_token && $setting->meli_app_id && $setting->meli_secret_key && $setting->meli_site_id) {
            $data = [
                'token' => $setting->techbox_token,
            ];

            $params = '';
            foreach ($data as $key => $value) {
                $params .= $key . '=' . $value . '&';
            }
            $params = trim($params, '&');

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://toolapi.test/IntegrationMeli?" . $params);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $contenido = curl_exec($curl);
            curl_close($curl);
            /* var_dump($contenido);
            die(); */
        }

        //var_dump($post);
        //die();
        //add_post_meta($post->ID, 'total_amount', '0', true); // This is the action to take
    }
}

$woo = new TechboxWooMercadoLibre();
$woo->register();
register_activation_hook(__FILE__, [$woo, 'activate']);
register_deactivation_hook(__FILE__, [$woo, 'desactivate']);
