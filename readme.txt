=== WPEC Bulk Tools ===
Contributors: toddhalfpenny
Donate link: http://gingerbreaddesign.co.uk/wordpress/plugins/plugins.php
Tags: ecommerce, bulk, e-commerce
Requires at least: 2.8
Tested up to: 2.9.1
Stable tag: 0.0.3

WPEC Bulk Tools provides bulk management tools for the WP e-Commerce plugin.

== Description ==

WPEC Bulk Tools provides bulk management tools for the WP e-Commerce plugin.
This version of the tool supports;
 * The setting of products across a *group* to the same price.
 * Uploading a CSV file of form;
     prod_name,sku,price


== Installation ==


1. Upload the directory `wpec-bulk-tools` and all its contents to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Install and activate the `wp e-Commerce` plugin
1. The tools can be used via the Menu item that appears in the WP e-Commerce menu section

== Frequently Asked Questions ==

= Do I need to have wp e-Commerce plugin to use this? =

Yes, of course.

= What file format can be used? =

The file should be in comma-seperated form with one product per line. Each line should be in the form;
    prod_name,sku,price


== Screenshots ==

1. The WPEC Bulk Tools menu item.

== Changelog ==

= 0.0.1 = 

1st release - supports price updates by category

= 0.0.2 = 

Update for newer versions of wordpress and the *wp ecommerce* plugin which used different database structures

= 0.0.3 = 

Includes support for bulk file upload to change properties based on SKU