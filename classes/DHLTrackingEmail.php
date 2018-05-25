<?php

/**
 * Created by PhpStorm.
 * User: matti
 * Date: 2018-05-25
 * Time: 20:11
 */
class DHLTrackingEmail
{
    function __construct(){
        add_action("woocommerce_email_order_meta",array($this,"add_meta_tracking_to_order"));
    }
    function add_meta_tracking_to_order($order){
        $this->logger = new WC_Logger();
        if($order->status === "completed" && get_option("add_to_tracking_page")){
            if($this->shouldlog){
                $this->logger->info("Order was completed and settings was enabled","dhl-tracking-form");
            }
            $trackingID = get_post_meta($order->id, "woo-dhl-tracking-form-trackingid", true);
            if($this->shouldlog){
                $this->logger->info("tracking ID for order was set to ".$trackingID,"dhl-tracking-form");
            }
            if($trackingID !== "" && get_option( 'tracking_page' )){
                $link = get_permalink(get_option( 'tracking_page' ))."?trackingid=".$trackingID;
                if($this->shouldlog){
                    $this->logger->info("Link created was: ".$link,"dhl-tracking-form");
                }
                echo wp_kses_post('<div style="padding: 20px 0;">');
                echo "<h2>". __("Track package","woo-dhl-tracking-form")."</h2>";
                echo "<a href='".$link."'>".__('Click here to track your package', 'woo-dhl-tracking-form' )."</a>";
                echo "</div>";
            }

        }
    }

}