=== Easy Instagram ===
Contributors: VeloMedia
Tags: Instagram, photos, gallery, images, widget, shortcode, hashtag
Requires at least: 3.0.1
Tested up to: 3.4.2
Stable tag: 1.0


Simply, quickly and easily, displays one or more Instagram images from a User ID 
or a from a hashtag, using shortcodes or widgets.


== Description ==


The “Easy Instagram” plugin displays an Instagram image from an Instagram user's 
photo collection or from images throughout Instagram hashtagged with a specified tag. 
It can also create a gallery of up to ten images fed either from 
the Instagram user ID or the tag.  


**“Easy Instagram” contains:**
* Streamlined code for optimized plugin performance
* Simple instructions
* Widget & shortcode options
* A bare-minimum feature set
* Flexibility - ready for custom css


**“Easy Instagram” supports:**
* A single photo feed or multiple feeds from a single User ID
* A single photo feed or multiple feeds from a single hashtag
* Multiple photo feeds, each from a different hashtag


== Installation ==

The following instructions assume the user’s possession of an Instagram account and its login
credentials, and that the account has been loaded with a minimum of one photo.

To Install "Easy Instagram"...
Steps 1-2: Load the plugin
Steps 3-7: Register for your Instagram API client
Steps 8-9: Authorize your Instagram API client in WordPress
To Use "Easy Instagram": Enter an "Easy Instagram" shortcode on a Post or Page, 
	or use the "Easy Instagram" widget (shown below)

 1. Either - Download Velomedia's "Easy Instagram" Plugin from our website; 
	unzip the file and drop the unzipped 'easy-instagram' folder into the 
	.../wp-content/plugins/ directory. Or, on the Plugins panel in the WordPress CMS, 
	press the 'Add New' button; then press 'Upload'; browse for the downloaded zip file, select it, 
	press 'Open'; then press 'Install Now'.  
	Or, search for and download "Easy Instagram" from the WordPress repository found when pressing 
	'Add New' on the Plugins panel in WordPress.
 2. In the WordPress CMS, select the "Plugins" or "Installed Plugins" menu option. Under the 
    "Easy Instagram" plugin entry, press 'Activate' to turn on the plugin.
 3. Open up a new browser window or tab and point it to http://instagram.com/developer/ ; then                 
	click the 'Login' link in the upper right corner.
 4. After you enter your Instagram login username and password, you will be taken to the 'Edit 
	Profile' page, click the 'API' link on the page's footer to take you back to the .../developer/ page.
 5. Once you are back to the developers page, click the 'Register' link located in the 
    'Get Started' diagram.
 6. Click the 'Register a New Client' button located in the upper right corner.
 7. This should bring you to the "Register new OAuth Client" page, enter the following 
        information in the text fields:
        * Application Name: [title of the website for this client registry]
        * Description: [short description of your website]
        * Website: [your website's URL(example: http://www.domain_name.com)]
        * OAuth redirect_uri: [this is the URL for WordPress' Settings page for the 
          Easy Instagram plugin. Should look like this: 
          http://www.domain_name.com/wp-admin/options-general.php?page=easy-instagram ]
          Then click 'Register'. You should be taken to the “Manage Clients” panel which will list:
                * Client ID
                * Client Secret
                * Redirect URI           
 8. Back in your WordPress CMS, navigate to the new "Easy Instagram" panel, in the CMS menu 
	under "Settings". Copy and paste the three fields from the Instagram “Manage Clients” panel 
	over to the appropriate fields on the plugin Settings page.  Enter the desired cache
    expire time in minutes.  Then click the 'Save Settings' button.
 9. Under the "Instagram Account" heading, click the 'Instagram Login' link and authorize Easy
	Instagram to access your account by entering your Instagram username and password. 
	This process should return you back to the Easy Instagram Settings page in your WordPress CMS, 
	where you will see your Instagram User ID, the numerical ID you will use to create feeds of 
	Instagram photos specifically from your Instagram photo account.
        
The Easy Instagram plugin is now set up and ready to use on your website!

*Usage* 

	To create one or more Instagram photo feeds, use the Easy Instagram widget, 
    or use the following shortcodes to generate an Instagram feed in the content area 
	of a Post or Page. By default the shortcode will display a single image. 

Examples: 
[easy-instagram user_id='123456789']
[easy-instagram user_id='keyboard' limit=2]
[easy-instagram user_id='123456789' limit=4]


* user_id = The numerical ID number for your Instagram account 
  shown on the Easy Instagram Settings page
* tag = hashtags to search for
* limit = amount of pictures to display (10 is max)


For multiple photo feeds each using different hashtags or user ids, 
repeat the shortcode with different options set, or add the widget 
a second time to the same widget space, and enter different settings.


== Frequently Asked Questions ==


= Can I use multiple shortcodes in the same page? =


Yes, but please notice that in some cases a small penalty in the page loading time can occur.

== Screenshots ==

1. Your website when you’ve used either the “Easy Instagram” shortcode in 
	the content area or the widget to place an Instagram photo feed.
2. The Instagram client registration form.
3. The Instagram client registration response.
4. The WordPress plugin Settings page for “Easy Instagram”.
5. An example of two “Easy Instagram” widgets being used to set up an Instagram photo feed.

== Changelog ==

= 1.0 = First version