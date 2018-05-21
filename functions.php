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
 * Plugin URI:  https://example.com/plugin-name
 * Description: Enabling fetching tracking info from DHL Freight.
 * Version:     1.0.0
 * Author:      Mnording
 * Author URI:  https://example.com
 * Text Domain: dhl-tracking-form
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

class DHLTracking {
    public function __construct()
    {

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
    public function getTracking($trackingIDUrl){
        $getTrackingHtml = $this->doCurl($trackingIDUrl);
        if($this->verifyResponse($getTrackingHtml)){
            $realhtml = $this->cleanHTML($getTrackingHtml);
            return $this->createHtml($realhtml);
        }
        else{
            return $this->createHtml("Kunde inte hitta din sändning ännu..");
        }
    }
    private function cleanHTML($html){
        $pos = strpos($html,'<table class="status-group"');
        $html = substr($html,$pos);
        $newend = strpos($html,'</table>');
        $html = substr($html,0,$newend+8);
        $html = preg_replace('/class=\"([a-z-]*)\"/i', '', $html);
        $html = preg_replace('/<font color=\"white\">/i', '', $html);
        $html = preg_replace('/<\/font>/i', '', $html);
        return $html;
    }
    private function verifyResponse($html){
        if (strpos($html, '<table class="status-group"') !== false) {
            return $html;
        }
    }
    private function createHtml($html){
        $header = "<div id='dhl-tracking-container'><h2>Din sändning:</h2>";
        $footer = "</div>";
        return $header."".$html."".$footer;
    }
    private function doCurl($url)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
            CURLOPT_SSL_VERIFYPEER => 0
        ));
        $resp = curl_exec($curl);
        if($resp == false) {
            die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        }
        curl_close($curl);
        return $resp;
    }
    function get_dhl_tracking() {
        $language = substr(get_bloginfo("language"),0,2);

        $trackingId = $_GET['trackingID'];
        $trackurl = 'https://service.apport.net/np/public/servicepoint/trackAndTrace?language='.$language.'&method=search&queryConsNo='.$trackingId;
        if($trackingId == ""){
            $orderid = urlencode($_GET["orderID"]);
            $trackurl = "https://service.apport.net/np/public/servicepoint/trackAndTrace?language=".$language."&queryReference=".$orderid;
        }

                echo $this->getTracking($trackurl);
        wp_die(); // this is required to terminate immediately and return a proper response
    }
}
$dhl = new DHLTracking();
