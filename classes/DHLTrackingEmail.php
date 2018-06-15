<?php

/**
 * Created by PhpStorm.
 * User: matti
 * Date: 2018-05-25
 * Time: 20:11
 */
class DHLTrackingEmail
{
    function __construct($shouldlog = false){
        $this->shouldlog = false;
        if($shouldlog == true){
            $this->shouldlog = true;
        }
        add_action("woocommerce_email_order_meta",array($this,"add_meta_tracking_to_order"));

    }
    function add_meta_tracking_to_order($order){
        $this->logger = new WC_Logger();

        if($order->get_status() === "completed" && get_option("add_to_tracking_page")){ // Order is completed and we want to display the link
            if($this->shouldlog){
                $this->logger->info("Order was completed and settings was enabled",array( 'source' => 'dhl-tracking-form' ));
            }
            $trackingID = get_post_meta($order->get_id(), "woo_dhl_tracking_form_trackingid", true);
            if($this->shouldlog){
                $this->logger->info("tracking ID for order was set to ".$trackingID,array( 'source' => 'dhl-tracking-form' ));
            }
            if(($trackingID !== "" || get_option('orderid_fallback')) && get_option( 'tracking_page' )){ // We have defined a tracking ID or fallback for the order and we have a valid page
               if(get_option('orderid_fallback')){
                   $link = get_permalink(get_option( 'tracking_page' ))."?orderid=".$order->get_id();
               }
               else if($trackingID !== "" ){
                   $link = get_permalink(get_option( 'tracking_page' ))."?trackingid=".$trackingID;
               }
                $order->add_order_note( "tracking link sent as ".$link );

                if($this->shouldlog){
                    $this->logger->info("Link created was: ".$link,array( 'source' => 'dhl-tracking-form' ));
                }
                echo wp_kses_post('<div style="padding: 20px 0;">');
                echo "<h2>". __("Track package","woo-dhl-tracking-form")."</h2>";
                echo "<a href='".$link."'>".__('Click here to track your package', 'woo-dhl-tracking-form' )."</a>";
                echo "</div>";
            }

        }
    }

}