=== Woo DHL Tracking Form ===
Contributors: mnording10
Tags: woocommerce,dhl,shipment,shipment tracking,dhl tracking
Requires at least: 4.9.0
Tested up to: 4.9.6
Requires PHP: 5.6.31
License: MIT
Stable tag: trunk
License URI: https://opensource.org/licenses/MIT

Creating a shortcode to display DHL Tracking information to end-user.
Connects to the public API of DHL in order to track shipments made on DHL Freight

== Description ==
This plugin connects to  the Active Tracing API of DHL Freight and creates a shortcode that can be inserted anywhere in your woocommerce site.
It creates a form where you can search based on shipment ID or your own order-reference.
Place shortcode `[woo-dhl-tracking-form][/woo-dhl-tracking-form]` in any page or post to render the form.
In the case that you are using non-unique order-references you can define your API keys in order to limit the consignments to your accounts.

** Disclaimer **
This plugin has no affiliation with DHL Freight. All product names, logos, and brands are property of their respective owners.
All company, product and service names used in this website are for identification purposes only.
Use of these names, logos, and brands does not imply endorsement.

== Installation ==
Upload to the `/wp-content/plugins/` directory
Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= When searching for order-references, I get someone else's order =
That is because your ID is not unique in DHLs systems. Either search by trackingID, or sign up with myACT in order to limit your requests to only your shipments.

== Changelog ==
1.0.2 - Bug for missing NoPriv
1.0.1 - Fix bug for requests without debug-mode
1.0.0 - Initial release