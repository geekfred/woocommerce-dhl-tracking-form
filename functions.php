<?php
/**
 * DHL Tracking
 *
 * @package     Woo DHL Tracking Form
 * @author      Mattias Nording
 * @copyright   2018 Mnording
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: Woo DHL Tracking Form
 * Plugin URI:  https://github.com/mnording/woocommerce-woo-dhl-tracking-form
 * Description: Enabling fetching tracking info from DHL Freight.
 * Version:     1.0.1
 * Author:      Mnording
 * Author URI:  https://mnording.com
 * Text Domain: woo-dhl-tracking-form
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */
require_once 'classes/DHLWebService.php';
class DHLTracking {
    public function __construct()
    {

       add_shortcode('woo-dhl-tracking-form', array($this,'render_form'));
        add_action( 'wp_ajax_get_dhl_tracking', array($this,'get_dhl_tracking') );
        add_action( 'wp_ajax_nopriv_get_dhl_tracking', array($this,'get_dhl_tracking') );
        add_action('wp_enqueue_scripts',array($this,'register_dhl_scripts'));
        add_action('admin_menu', array($this,'dhl_tracking_plugin_create_menu'));
        add_action( 'admin_init', array($this,'dhl_tracking_plugin_settings') );
        add_action( 'plugins_loaded', array($this,'dhl_tracking_plugin_textdomain') );
    }
    function dhl_tracking_plugin_textdomain() {
        load_plugin_textdomain( 'woo-dhl-tracking-form', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }
    public function dhl_tracking_plugin_settings() {
        register_setting( 'dhl_tracking_settings-group', 'private_api' );
        register_setting( 'dhl_tracking_settings-group', 'api_password' );
        register_setting( 'dhl_tracking_settings-group', 'api_username' );
        register_setting( 'dhl_tracking_settings-group', 'should_log' );
    }
    public function dhl_tracking_plugin_create_menu() {
        add_options_page('Woo DHL Tracking Settings', 'Woo DHL Tracking', 'administrator','woo-dhl-tracking-form' ,array($this,'dhl_tracking_settings_page') );

    }
    public function dhl_tracking_settings_page() {
        ?>
        <div class="wrap">
            <h1>DHL Tracking Form</h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'dhl_tracking_settings-group' ); ?>
                <?php do_settings_sections( 'dhl_tracking_settings-group' ); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e("Use Private Methods?","woo-dhl-tracking-form"); ?></th>
                        <td><input name="private_api" type="checkbox" value="1" <?php checked( '1', get_option( 'private_api' ) ); ?> /><?php _e("Yes","woo-dhl-tracking-form")?></td>
                        <td><?php _e("This requires an API Key, but also ensure that only your own consignments are returned from the API.","woo-dhl-tracking-form");?>
                            <strong><?php _e(" Recommended if you use non-unique references","woo-dhl-tracking-form")?></strong>
                        <br/>
                            <?php _e("In order to enable private methods on your account, you must email se.ecom@dhl.com or call SE ECOM 0771 345 345 and request access to The ACT Webservice and specify your myACT account. ","woo-dhl-tracking-form"); ?>
                        </td>

                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e("Api Username","woo-dhl-tracking-form"); ?></th>
                        <td><input type="text" name="api_username" value="<?php echo esc_attr( get_option('api_username') ); ?>" /></td>
                        <td><?php _e("What is your API username. Should be the same as your login to the myACT portal","woo-dhl-tracking-form"); ?> -> <a href="https://activetracing.dhl.com/DatPublic/login.do?"><?php _e("Click here for Portal","woo-dhl-tracking-form");?></a></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e("API Password","woo-dhl-tracking-form"); ?></th>
                        <td><input type="password" name="api_password" value="<?php echo esc_attr( get_option('api_password') ); ?>" /></td>
                        <td><?php _e("What is your API password. Should be the same as your password to the myACT portal","woo-dhl-tracking-form"); ?></td>

                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e("Create Debug log?","woo-dhl-tracking-form"); ?></th>
                        <td><input name="should_log" type="checkbox" value="1" <?php checked( '1', get_option( 'should_log' ) ); ?> /><?php _e("Yes","woo-dhl-tracking-form")?></td>
                    </tr>
                </table>

                <?php submit_button(); ?>

            </form>
        </div>
    <?php }
    public function render_form(){
        $html = '<style>';
        $html .= '#dhl-tracking-form-container {border-bottom: 1px dotted black;float: left;width: 100%; padding: 10px;}';
        $html .= '#dhl-tracking-form-container button { float:right;}';
        $html .= '#dhl-tracking-response-container{ float:left;width:100%;position:relative;}';
        $html .= '.loader {border: 16px solid #f3f3f3;border-top: 16px solid #3498db;border-radius: 50%;width: 120px;height: 120px; animation: spin 2s linear infinite; position:absolute;left:45%;}';
        $html .= '@keyframes spin {0% { transform: rotate(0deg); }100% { transform: rotate(360deg); }}';
        $html .= '</style>';
        $html .= "<div id='dhl-tracking-form-container'>";
        $html .= __("Tracking ID","woo-dhl-tracking-form")." <input type='text' name='trackingid' id='trackingid' placeholder='Sändnings ID'>";
        $html .= " ".__("or","woo-dhl-tracking-form")." ".__("Order Id","woo-dhl-tracking-form")." <input type='text' id='orderid' name='orderid' placeholder='Order ID'>";
        $html .= "<button>".__("Track package","woo-dhl-tracking-form")."</button>";
        $html .= "</div>";
        wp_enqueue_script('woo-dhl-tracking-form');
        $html.="<div id='dhl-tracking-response-container'></div>";
        return $html;
    }
    function register_dhl_scripts()
    {
        wp_register_script( 'woo-dhl-tracking-form', plugins_url('dhl-main.js',__FILE__), array('jquery'), '1.0',true );
    }
    function dhl_tracking_scripts() {
        wp_enqueue_script( 'woo-dhl-tracking-form');
    }

    private function createHtml($html){
        $header = "<div id='dhl-tracking-container'><h2>".__("Your shipment:","woo-dhl-tracking-form")."</h2>";
        $footer = "</div>";
        return $header."".$html."".$footer;
    }

    function get_dhl_tracking() {
        $lang = get_bloginfo( $show = 'language');
        $lang = substr($lang,0,2);
        $this->dhl = new DhlWebservice(get_option('api_password'),get_option('api_username'),$lang,get_option('should_log'));
        $trackingId = $_GET['trackingID'];
        $resp = "";
        $privateAPI = get_option('private_api');

        if($trackingId != ""){
            if($privateAPI === "1"){
                $resp = $this->dhl->GetByShipmentId($trackingId);
            }else{
                $resp = $this->dhl->GetByShipmentIdPublic($trackingId);
            }

        }
        if($trackingId == ""){
            $orderid = urlencode($_GET["orderID"]);
            if($privateAPI === "1"){
                $resp = $this->dhl->GetShipmentByReference($orderid);
            }else {
                $resp = $this->dhl->GetShipmentByReferencePublic($orderid);
            }
        }
        $html = "<table>";
        $html .= "<th>".__("Date","woo-dhl-tracking-form")."</th>";
        $html .= "<th>".__("Location","woo-dhl-tracking-form")."</th>";
        $html .= "<th>".__("Event","woo-dhl-tracking-form")."</th>";
        foreach($resp as $data){
            $html .= "<tr>";
            $html .= "<td>";
            $html .=   $data["date"]." ".$data["time"];
            $html .= "</td>";
            $html .= "<td>";
            $html .= $data["location"];
            $html .= "</td>";
            $html .= "<td>";
            $html .= $data["descr"];
            $html .= "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";
        echo $this->createHtml($html);
        wp_die(); // this is required to terminate immediately and return a proper response
    }
}
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    $dhl = new DHLTracking();
}

