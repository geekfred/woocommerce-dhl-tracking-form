<?php
/**
 * DHL Tracking
 *
 * @package     DHL Tracking Form
 * @author      Mattias Nording
 * @copyright   2018 Mnording
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: DHL Tracking Form
 * Plugin URI:  https://github.com/mnording/woocommerce-dhl-tracking-form
 * Description: Enabling fetching tracking info from DHL Freight.
 * Version:     1.0.0
 * Author:      Mnording
 * Author URI:  https://mnording.com
 * Text Domain: dhl-tracking-form
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */
require_once 'classes/DHLWebService.php';
class DHLTracking {
    public function __construct()
    {
        $this->dhl = new DhlWebservice("",""); // TODO: Create settings page to control API creds

       add_shortcode('dhl-tracking-form', array($this,'render_form'));
        add_action( 'wp_ajax_get_dhl_tracking', array($this,'get_dhl_tracking') );
        add_action('wp_enqueue_scripts',array($this,'register_dhl_scripts'));

    }
    public function render_form(){
        $html = '<style>';
        $html .= '#dhl-tracking-form-container {border-bottom: 1px dotted black;float: left;width: 100%; padding: 10px;}';
        $html .= '#dhl-tracking-form-container button { float:right;}';
        $html .= '</style>';
        $html .= "<div id='dhl-tracking-form-container'>";
        $html .= __("Tracking ID","dhl-tracking-form")." <input type='text' name='trackingid' id='trackingid' placeholder='Sändnings ID'>";
        $html .= " ".__("or","dhl-tracking-form")." ".__("Order Id","dhl-tracking-form")." <input type='text' id='orderid' name='orderid' placeholder='Order ID'>";
        $html .= "<button>".__("Track package","dhl-tracking-form")."</button>";
        $html .= "</div>";
        wp_enqueue_script('dhl-tracking-form');
        $html.="<div id='dhl-tracking-response-container'></div>";
        return $html;
    }
    function register_dhl_scripts()
    {
        wp_register_script( 'dhl-tracking-form', plugins_url('dhl-tracking.js',__FILE__), array('jquery'), '1.0',true );
    }
    function dhl_tracking_scripts() {
        wp_enqueue_script( 'dhl-tracking-form');
    }

    private function createHtml($html){
        $header = "<div id='dhl-tracking-container'><h2>Din sändning:</h2>";
        $footer = "</div>";
        return $header."".$html."".$footer;
    }

    function get_dhl_tracking() {

        $trackingId = $_GET['trackingID'];
        $resp = "";
        if($trackingId != ""){
            $resp = $this->dhl->GetByShipmentIdPublic($trackingId);
        }
        if($trackingId == ""){
            $orderid = urlencode($_GET["orderID"]);
            $resp =  $this->dhl->GetShipmentByReferencePublic($orderid);
        }
        $html = "<table>";
        foreach($resp as $data){
            $html .= "<tr>";
            $html .= "<td>";
            $html .=   $data["date"];
            $html .= "</td>";
            $html .= "<td>";
            $html .= $data["time"];
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
$dhl = new DHLTracking();
