<?php
	/**
	* Plugin Name: Simple Constant Contact
	* Plugin URI: http://www.ashtonpaul.com/project/simple-constant-contact/
	* Description: Simple Wordpress Newsletter Plugin to take name and email.
	* Version: 1.1
	* Author: Ashton Paul
	* Author URI: http://ashtonpaul.com/
	* Contributors: elementsweb
	* Tags: plugin, constant, contact, newsletter, constant contact, wordpress, wordpress.org, form, signup, sign-up, sign up
	* License: GPL2
	*
	* Copyright (C) 2016  Ashton Paul (email: ashton@ashtonpaul.com)
	* 
	* This program is free software; you can redistribute it and/or
	* modify it under the terms of the GNU General Public License
	* as published by the Free Software Foundation; either version 2
	* of the License, or (at your option) any later version.
	
	* This program is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	* GNU General Public License for more details.
	
	* You should have received a copy of the GNU General Public License
	* along with this program; if not, write to the Free Software
	* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	*/
			
	// block direct access to plug-in
	defined('ABSPATH') or die("No script kiddies please!");
	
	// require the autoloader to load Constant Contact API routines
	require_once 'src/Ctct/autoload.php';

	// core constant contact components
	use Ctct\ConstantContact;
	use Ctct\Components\Contacts\Contact;
	use Ctct\Components\Contacts\ContactList;
	use Ctct\Components\Contacts\EmailAddress;
	use Ctct\Exceptions\CtctException;
	
	// to retreive access token
	use Ctct\Auth\CtctOAuth2;
	use Ctct\Exceptions\OAuth2Exception;
	
	class simple_constant_contact {
		// main object to access the API			
		private $cc;
		
		// list id of the website's contact/email list to be used in constant contact
		private $list;
		
		// debug variable to trigger html responses
		public $enabled = false;
		public $error = false;	
		
		// instance variable to help with memory management
		private static $instance = false;
		
		// adopted a singleton approach to reserve only on instance of the class in memory (http://jumping-duck.com/tutorial/wordpress-plugin-structure/)
		public static function get_instance() {
    		if ( ! self::$instance ) {
      			self::$instance = new self();
    		}
    		return self::$instance;
  		}
		
		//strip non alpha characters from first and last name
		private function strip_nonalpha($string) {
			return preg_replace("/[^A-Za-z]/", '', $string);
		}
		
		// get all the available lists for that account
		private function lists() {
			return $this->cc->getLists(ACCESS_TOKEN);
		}
		
		// get the id of the list by name, if not exists then create one		
		private function getListId($name) {
			$id = null;
			// loop through all the available contact/email lists
			foreach ($this->lists() as $list) 
				if (strcmp ($name,$list->name) == 0)
					//if a match is found, then save id number of that list
					$id = $list->id;	
			
			//if no id number is found with that list name
			if (is_null($id)) {
				//Make a new list
				$list = new ContactList();
				$list->name = WEBSITE_LIST;
				$list->status = "ACTIVE";	
				$list = $this->cc->addList(ACCESS_TOKEN, $list);
				
				//get newly generated id number for the list
				$id = $list->id;
			}
			
			// return only the id number
			return $id;
		}
		
		// create a new connection to the constant contact by API and see if every thing is ready to go
		private function ready(){
			// define constant contact APIKEY and ACCESS_TOKEN and the contact list to be used
			if (!defined("APIKEY")) define("APIKEY", get_option('simpleCC_apikey'));			
			if (!defined("ACCESS_TOKEN")) define("ACCESS_TOKEN", get_option('simpleCC_accesstoken'));
			if (!defined("WEBSITE_LIST")) {
				if (get_option('simpleCC_listname')) {
					define("WEBSITE_LIST", get_option('simpleCC_listname'));
				}
				else {
					define("WEBSITE_LIST", 'Simple Constant Contact');
				}	
			}

			if (!defined("SUCCESS_MESSAGE")) {
				if (get_option('simpleCC_message_successful')) { 
					define("SUCCESS_MESSAGE", get_option('simpleCC_message_successful'));
				}
				else {
					define("SUCCESS_MESSAGE", 'Thanks for subscribing!');
				}	
			} 

			
			try {
				// instantiate new API reference variable
				$this->cc = new ConstantContact(APIKEY);
				
				// authenticate API and access information
				try {
		    		$lists = $this->cc->getLists(ACCESS_TOKEN);
					$this->enabled = true;
				}
					catch (CtctException $ex) {
					echo 'Simple Constant Contact is not authenticated. Get a new Access Code.';
					$this->enabled = false;
				}						
					
				// save the list id for further reference 
				$this->list = $this->getListId(WEBSITE_LIST);
				return $this->enabled;
			}
			catch (CtctException $ex) {
				echo 'Simple Constant Contact is not configured properly.';
				$this->enabled = false;
				return $this->enabled;
			}	
		}
		
		// remove all locally stored options data upon deactivation
		function simpleCC_deactivate(){				
			// erase the data	
			delete_option('simpleCC_apikey');
			delete_option('simpleCC_secret');
			delete_option('simpleCC_accesstoken');
			delete_option('simpleCC_listname');
			delete_option('simpleCC_message_successful');
			delete_option('simpleCC_redirect');
			
			// unregister it from options and options group
			unregister_setting('simpleCC_options','simpleCC_apikey');
			unregister_setting('simpleCC_options','simpleCC_secret');
			unregister_setting('simpleCC_options','simpleCC_accesstoken');
			unregister_setting('simpleCC_options','simpleCC_listname');
			unregister_setting('simpleCC_options','simpleCC_message_successful');
			unregister_setting('simpleCC_options','simpleCC_redirect');
		}
		
		// plugin initiatilzation and set up all options variables for use
		function simpleCC_init() {
			// make place ready for options stored in plugin	
			register_setting('simpleCC_options','simpleCC_apikey');
			register_setting('simpleCC_options','simpleCC_secret');
			register_setting('simpleCC_options','simpleCC_accesstoken');
			register_setting('simpleCC_options','simpleCC_listname');
			register_setting('simpleCC_options','simpleCC_message_successful');
			register_setting('simpleCC_options','simpleCC_redirect');

			// initalize default values			
			update_option('simpleCC_apikey','f7mpubkv2na59zpu5juuyfq3');
			update_option('simpleCC_secret','GcgS2p6TDvwRJnxZtfag2tFp');
			update_option('simpleCC_redirect','http://simplecc.ashtonpaul.com/');
			
			// hook to remove all settings made on deactivation
			register_deactivation_hook( __FILE__, array($this,'simpleCC_deactivate'));
		}

		// get and process post data submitted by the form
		function simpleCC_get_data() {
			// set post for use within the function
			global $_POST;
			
			// only if the form was successfully submitted
			if (($_POST) && ($_POST['simpleCC_submit'] == 'simpleCC_submit_successful')) {
					
				// sanitize the form data before entry into constant contact
				$first_name = sanitize_text_field($this->strip_nonalpha($_POST['simpleCC_fname']));
				$last_name = sanitize_text_field($this->strip_nonalpha($_POST['simpleCC_lname']));
				$email = sanitize_email($_POST['simpleCC_email']);
				
				// only if data is valid to enter into constant contact
				if (is_email($email) && !empty($first_name) && !empty($last_name)) {
					$result = $this->contactEntry($first_name, $last_name, $email);
					$submitted = true;
				}
			} 
		}		
		
		// set up options page	
		function simpleCC_option_page()
		{
			?>
				<!-- display form in admin menu -->
				<div class="wrap">
					<h2>Simple Constant Contact Settings</h2>
					<form action="options.php" method="post" id="simpleCC-options-form">
						<?php settings_fields('simpleCC_options'); ?>				
						<div>
							<h3 class="title">To Get Access Token:</h3>
								<p> 
									&bull;Click <strong>"Get Access Token"</strong> Button.<br />
									&bull;Login into your Constant Contact then authorize the <strong>"Simple Constant Contact"</strong> plugin. <br />
									&bull;Copy the access token and paste it in the input box below labeled <strong>"Constant Contact Access Token"</strong>.<br />
								</p>
							<h3 class="title">Usage</h3>
								<p>Put shortcode <strong>[simpleCC]</strong> wherever you want the form displayed.
								</p>
		        		</div> 	
		        		<hr>		
		        		<table class="form-table">
		        			<tbody>
		        				<tr>
		        					<th scope="row">
		        						<label for="simpleCC_accesstoken">Constant Contact Access Token: </label> 
		        					</th>
		        					<td>
		        						<input type="text" size="35" id="simpleCC_accesstoken" name="simpleCC_accesstoken" value="<?php echo esc_attr( get_option('simpleCC_accesstoken') ); ?>" />
										<button type="button" id="simpleCC_accesstoken" 
											<?php 
												$oauth = new CtctOAuth2(get_option('simpleCC_apikey'), get_option('simpleCC_secret') , get_option('simpleCC_redirect'));
												$url = $oauth->getAuthorizationUrl();
											?>
											onclick="window.open('<?php echo $url; ?>');">Get Access Token </button>
		        					</td>
		        				</tr>
		        				<tr>
		        					<th scope="row">
		        						<label for="simpleCC_listname">Constant Contact List Name: </label> 	
		        					</th>
		        					<td>
		        						<input type="text" size="35" id="simpleCC_listname" name="simpleCC_listname" value="<?php echo esc_attr( get_option('simpleCC_listname') ); ?>" />
		        					</td>
		        				</tr>
		        				<tr>
		        					<th scope="row">
		        						<label for="simpleCC_listname">Success Message: </label> 	
		        					</th>

		        					<td>
		        						<input type="text" size="35" id="simpleCC_message_successful" name="simpleCC_message_successful" value="<?php echo esc_attr( get_option('simpleCC_message_successful') ); ?>" />
		        					</td>
		        				</tr>
		        			</tbody>
		        		</table>
		        		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"  /></p>
					</form>
				</div>		
			<?php
		}
		
		// create the settings page in the wordpress menu
		function simpleCC_plugin_menu()
		{
			add_options_page('Simple Constant Contact', 'Simple Constant Contact', 9, 'simple-cc-plugin', array($this, 'simpleCC_option_page'));
		}
	
		// display the entry form for the plugin
		function simpleCC_display_form() {
			// test to see if all the settings are correct	
			if ($this->ready()) {
				if ($_POST['simpleCC_submitted'] == 'success') {
					$_POST['simpleCC_submitted'] = '';

					if (get_option('simpleCC_message_successful') == '')
						$message = 'Thanks for subscribing!';
					else 
						$message = get_option('simpleCC_message_successful');

					return '<div class="simpleCC_plugin"><p id="simpleCC_error_message">'.$message.'</p></div>';
				}
				else {
					return '<div class="simpleCC_plugin">
								<p id="simpleCC_success_message"></p>
								<p id="simpleCC_error_message"></p>
								<form name="simpleCC_form" class="simpleCC_form" id="simpleCC_form" method="post" onsubmit="return simpleCC_message()">
									<label for="simpleCC_fname">First Name<input type="text" id="simpleCC_fname" name="simpleCC_fname" /></label>
									<label for="simpleCC_lname">Last Name<input type="text" id="simpleCC_lname" name="simpleCC_lname" /></label>
									<label for="simpleCC_email">Email<input type="email" id="simpleCC_email" name="simpleCC_email" /></label>
									<input type="hidden" name="simpleCC_submitted" id="simpleCC_submitted" value="" />
									<button id="simpleCC_submit" type="submit" name="simpleCC_submit" value="simpleCC_submit_successful">Submit</button>
								</form>
							</div>';
				}
			}
		}

		// Custom javascript 
		function simpleCC_scripts_basic(){
			wp_register_script('simpleeCC_script', plugins_url('/js/simpleCC.js', __FILE__));
			wp_enqueue_script('simpleeCC_script');
		}
		
		// class constructor when instance is called
		public function __construct(){
			// set up action hooks and short codes for functions
			add_action('admin_init', array($this, 'simpleCC_init'));
			add_action('init', array($this, 'simpleCC_get_data'));
			add_shortcode('simpleCC', array($this, 'simpleCC_display_form'));
			add_action('admin_menu', array($this, 'simpleCC_plugin_menu'));

			// javascript
			add_action( 'wp_enqueue_scripts', array($this, 'simpleCC_scripts_basic' ));
		}

		// add or update a contact when information is passed to this function by a form
		public function contactEntry($first_name, $last_name, $email) {
			try {
				// test to see if all the settings are correct
				if ($this->ready()) {
					// check to see if a contact with the email address already exists in the account
					$response = $this->cc->getContactByEmail(ACCESS_TOKEN, $email);
					
					// create a new contact if one does not exist
					if (empty($response->results)) {
						$contact = new Contact();
						$contact->addEmail($email);
						$contact->addList($this->list);
						$contact->first_name = $first_name;
						$contact->last_name = $last_name;
			            $returnContact = $this->cc->addContact(ACCESS_TOKEN, $contact, false);
			        } 
					// update the existing contact if address already existed
			        else {
			            $contact = $response->results[0];
			            $contact->addList($this->list);
			            $contact->first_name = $first_name;
			            $contact->last_name = $last_name;
			            $returnContact = $this->cc->updateContact(ACCESS_TOKEN, $contact, false);
					}		      
					$this->error = false;
				}
		    } 

			// catch any exceptions thrown during the process and indicate an error has occured 
		    catch (CtctException $ex) {
				$this->error = true;
			} 
			return $this->error;	
		} 
	}
	
	// start up by creating a new instance of the class
	function start_simple_constant_contact() {
		$simple_constant_contact = simple_constant_contact::get_instance();
	}
	start_simple_constant_contact();
?>
