<?php

/**
 * Created by PhpStorm.
 * User: matti
 * Date: 2018-05-25
 * Time: 20:11
 */
class DHLMetaBox
{
     function __construct(){
         add_action("add_meta_boxes", array($this,"add_custom_meta_box"));
         add_action("save_post", array($this,"save_woo_dhl_tracking_meta_box"), 10, 3);
     }

    function woo_dhl_tracking_meta_box_markup($object)
    {
        wp_nonce_field(basename(__FILE__), "woo-dhl-tracking-form-nonce");
        ?>
        <div>
            <label for="woo-dhl-tracking-form-trackingid"><?php _e("Tracking ID","woo-dhl-tracking-form") ?></label>
            <input name="woo-dhl-tracking-form-trackingid" type="text" value="<?php echo get_post_meta($object->ID, "woo-dhl-tracking-form-trackingid", true); ?>">
        </div>
        <?php
    }
    function add_custom_meta_box()
    {
        add_meta_box("woo-dhl-tracking-meta-box", "Woo DHL Tracking", array($this,"woo_dhl_tracking_meta_box_markup"), "shop_order", "side", "high", null);
    }
    function save_woo_dhl_tracking_meta_box($post_id, $post, $update)
    {
        if (!isset($_POST["woo-dhl-tracking-form-nonce"]) || !wp_verify_nonce($_POST["woo-dhl-tracking-form-nonce"], basename(__FILE__)))
            return $post_id;

        if(!current_user_can("edit_post", $post_id))
            return $post_id;

        if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
            return $post_id;

        if(isset($_POST["woo-dhl-tracking-form-trackingid"]))
        {
            $meta_box_text_value = $_POST["woo-dhl-tracking-form-trackingid"];
        }
        update_post_meta($post_id, "woo-dhl-tracking-form-trackingid", $meta_box_text_value);
    }
}