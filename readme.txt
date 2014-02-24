=== Advanced Custom Fields: Advanced Taxonomy Selector Field ===
Contributors: {{wp_user_name}}
Tags:
Requires at least: 3.4
Tested up to: 3.3.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to create a field where users can select from multiple taxonomies

== Description ==

A more advanced taxonomy selector for ACF. It allows you to create a field where users can select terms spanning multiple taxonomies. An autocomplete field is coming soon too!

= Compatibility =

This add-on will work with:

* version 4 and up
* version 3 and bellow

== Installation ==

This add-on can be treated as both a WP plugin and a theme include.

= Plugin =
1. Copy the 'acf-advanced_taxonomy_selector' folder into your plugins folder
2. Activate the plugin via the Plugins admin page

= Include =
1.	Copy the 'acf-advanced_taxonomy_selector' folder into your theme folder (can use sub folders). You can place the folder anywhere inside the 'wp-content' directory
2.	Edit your functions.php file and add the code below (Make sure the path is correct to include the acf-advanced_taxonomy_selector.php file)

`
add_action('acf/register_fields', 'my_register_fields');

function my_register_fields()
{
	include_once('acf-advanced_taxonomy_selector/acf-advanced_taxonomy_selector.php');
}
`

== Changelog ==

= 0.0.1 =
* Initial Release.
