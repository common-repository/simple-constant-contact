=== Simple Constant Contact ===
Contributors: ashtonpaul, elementsweb
Link: http://simplecc.ashtonpaul.com/
Donate link: http://simplecc.ashtonpaul.com/
Tags: plugin, constant, contact, newsletter, constant contact, wordpress, wordpress.org, form, signup, sign-up
Requires at least: 3.8
Tested up to: 4.4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple Wordpress Constant Contact Plugin to take name and email and allow to send that information straight to Constant Contact
 
== Description ==
The Simple Constant Contact Plugin is a quick and easy plugin to interface with your constant contact account. It takes the visitor's name and email and allows it to send that information directly to your Constant Contact account. Set up is easy. Just follow the instructions in the admin menu and you're on your way. The UI was left raw with class and id tags so you can apply your own styling to match your current theme. 
 
== Installation ==
1. Install Simple Constant Contact either via the WordPress.org plugin directory, or by uploading the files to your server
2. After activating Simple Constant Contact, navigate to the options menu item 'Simple Constant Contact'.
3. Click the 'Get Access Token' Button. Then login to your Constant Contact account and authorize the plugin.
4. Copy the Access Token code after authorizing the plugin and paste it back into the field label 'Constant Contact Access Token' on the plugin's WordPress options page.
5. Enter a List name you want the email addresses to group under in your Constant Contact account (default is Simple Constant Contact).
6. Use the short-code [simpleCC] to display the form.
7. If you like you can leave the formatting as is or create your own stylesheets to match your theme.

== Frequently Asked Questions ==

= What is the HTML output so I can match my theme style? =

`
<div class="simpleCC_plugin">
	<p id="simpleCC_error_message"></p>
	<form name="simpleCC_form" class="simpleCC_form" id="simpleCC_form" method="post" onsubmit="return simpleCC_message()">
		<label for="simpleCC_fname">
			First Name
			<input type="text" id="simpleCC_fname" name="simpleCC_fname" />
		</label>
		<label for="simpleCC_lname">
			Last Name
			<input type="text" id="simpleCC_lname" name="simpleCC_lname" />
		</label>
		<label for="simpleCC_email">
			Email
			<input type="email" id="simpleCC_email" name="simpleCC_email" />
		</label>
		<input type="hidden" name="simpleCC_submitted" id="simpleCC_submitted" value="" />
		<button id="simpleCC_submit" type="submit" name="simpleCC_submit" value="simpleCC_submit_successful">Submit</button>
	</form>
</div>
`
== Screenshots ==
1. This is how it looks with a little bit of formatting via CSS
2. Settings page


== Changelog ==
= 1.0 =
* Initial release
= 1.1 =
* Update documentation to include usage on settings page
* Update redirect URI to subdomain
* Move settings page under options instead of main menu
* Add capability for custom success message
* Error handling on forms
* Success message on submit
* Fixed return bug for short-code (Thanks to elementsweb)
* Update readme file
* Added logo
* Added screenshot
