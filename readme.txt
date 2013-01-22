=== Easy Instagram ===
Contributors: VeloMedia
Tags: Instagram, photos, gallery, images, widget, shortcode, hashtag
Requires at least: 3.0.1
Tested up to: 3.5.0
Stable tag: 1.2.5


Simply, quickly and easily, displays one or more Instagram images from a User ID
or a from a Tag, using shortcodes or widgets - without using a third-party API.


== Description ==

The “Easy Instagram” plugin can display an Instagram image from a single Instagram user's
photo collection or from images throughout Instagram hashtagged with a specified Tag.
It can also create a gallery of up to ten images fed either from the Instagram User ID or the Tag.

**“Easy Instagram” contains:**

* Streamlined code for optimized plugin performance
* Simple instructions
* Widget & shortcode options
* A bare-minimum feature set
* Flexibility - ready for custom css


**“Easy Instagram” supports:**

* A single photo feed or up to ten fed from a single User ID
* A single photo feed or up to ten fed from a single Tag
* Multiple photo feeds, of up to ten images, each from a different Tag


**“Easy Instagram” features:**

* Option to display the image author's username, and change lead text
* Image author's username links to their Instagram profile page
* Option to limit image description character display
* Option to display time since image was posted
* Option to display date and time image was posted
* Option to hide/display the images hashtags
* Option to, on click, open image in Instagram on a new tab
* Option to, on click, open image in a Thickbox popup


== Installation ==

The following instructions assume the user’s possession of an Instagram account and its login credentials, and that the account has been loaded with a minimum of one photo.

To Install "Easy Instagram"...

* Steps 1-2: Load the plugin
* Steps 3-7: Register for your Instagram API client
* Steps 8-9: Authorize your Instagram API client in WordPress

**Load the plugin**

 1. Either - Download Velomedia's "Easy Instagram" Plugin from our website; unzip the file and drop the unzipped 'easy-instagram' folder into the .../wp-content/plugins/ directory. Or, on the Plugins panel in the WordPress CMS, press the 'Add New' button; then press 'Upload'; browse for the downloaded zip file, select it, press 'Open'; then press 'Install Now'.
	Or, search for and download "Easy Instagram" from the WordPress repository found when pressing 'Add New' on the Plugins panel in WordPress.
 2. In the WordPress CMS, select the "Plugins" or "Installed Plugins" menu option. Under the "Easy Instagram" plugin entry, press 'Activate' to turn on the plugin.

**Register for your Instagram API client** *(see why you need to register an API client in this plugins FAQs)*

 3. Open up a new browser window or tab and point it to http://instagram.com/developer/ ; then click the 'Login' link in the upper right corner.
 4. After you enter your Instagram login username and password, you will be taken to the 'Edit Profile' page, click the 'API' link on the page's footer to take you back to the .../developer/ page.
 5. Once you are back to the developers page, click the 'Register' link located in the 'Get Started' diagram.
 6. Click the 'Register a New Client' button located in the upper right corner.
 7. This should bring you to the "Register new OAuth Client" page, enter the following information in the text fields:
        * Application Name: [title of the website for this client registry]
        * Description: [short description of your website]
        * Website: [your website's URL(example: http://www.domain_name.com)]
        * OAuth redirect_uri: [this is the URL for WordPress' Settings page for the Easy Instagram plugin. Should look like this:
          http://www.domain_name.com/wp-admin/options-general.php?page=easy-instagram ]
          Then click 'Register'. You should be taken to the “Manage Clients” panel which will list:
                * Client ID
                * Client Secret
                * Redirect URI

**Authorize your Instagram API client in WordPress**

 8. Back in your WordPress CMS, navigate to the new "Easy Instagram" panel, in the CMS menu under "Settings". Copy and paste the three fields from the Instagram “Manage Clients” panel over to the appropriate fields on the plugin Settings page.  Enter the desired cache expire time in minutes.  Then click the 'Save Settings' button.
 9. Under the "Instagram Account" heading, click the 'Instagram Login' link and authorize Easy Instagram to access your account by entering your Instagram username and password.
	This process should return you back to the Easy Instagram Settings page in your WordPress CMS, where you will see your Instagram User ID.

	***This is the numerical ID you will use to create feeds of Instagram photos specifically from your Instagram photo account.***

*The Easy Instagram plugin is now set up and ready to use on your website!*

== Usage ==

To create one or more Instagram photo feeds, use the Easy Instagram widget, or use the following shortcodes to generate an Instagram feed in the content area of a Post or Page.
By default the shortcode will display a single image.

**Examples:**

* [easy-instagram user_id='123456789']
* [easy-instagram tag='puppy' limit=2]
* [easy-instagram user_id='123456789' limit=4]
* [easy-instagram tag='puppy' limit=2 caption_hashtags=false]
* [easy-instagram user_id='123456789' author_format='published by %s']
* [easy-instagram user_id='123456789' limit=4 thumb_click='thickbox']

**Options**

* user_id = The numerical ID number for your Instagram account
  shown on the Easy Instagram Settings page
* tag = hashtags to search for
* limit = amount of pictures to display (10 is max). Default: 1
* caption_hashtags = show or hide hashtags from the end of image caption. Default: true
* caption_char_limit = limit the number of characters output in caption. Set 0 for no caption output. Default: 100
* author_text = allow setting the text format for the image autor. Default: "by %s"
* thumb_click = define the action on thumbnail click. Default: No action.
	1. 'thickbox' -> Open the image in a Thickbox popup
	2. 'original' -> Open the original image from Instagram, in a new browser tab
* time_text = defines the output format for the image time. The value “#T#” is replaced by the time value generated by the “time_format” option.
Default: "posted #T#"
* time_format = defines the time format. Accepts the standard PHP time format used by “strftime” (http://php.net/manual/en/function.strftime.php) or “#R#” for 'relative time'. Default: "#R#"

For multiple photo feeds each using different hashtags or user ids, repeat the shortcode with different options set, or add the widget a second time to the same widget space, and enter different settings.


== Frequently Asked Questions ==

= Why do I have to register for an Instagram API client with this plugin? =

In order to display and feed Instagram photos through a website, someone must have registered an API client with Instagram.

There are many other Instagram plugins that use the same API key for thousands of installations.
Using that kind of plugin, you share the risk with all of the thousands of other plugin users that the plugin developer may disappear, may not keep the API key active, and effectively break your site feature without notice.
You also risk the plugin developer eventually charging for their plugin, especially because they're providing an API registration service on which you've become dependent.

The 'Easy Instagram' advantage is that only you (your website) and Instagram are involved, and that still -- you don't need to know any code whatsoever to complete the installation process.
Basically involving a series of cuts-and-pastes, most people report having registered their API client in just a few minutes.

= Can I use multiple shortcodes in the same page? =

Yes, but please notice that in some cases a small penalty in the page loading time can occur.

= Does the displayed image author user name link to their Instagram profile page? =

Yes it does, as of 'Easy Instagram' release 1.2.3.

= Can I disable the hashtags in the caption? =

Yes.  Add caption_hashtags=false to your shortcode.

= Can I hide the image description, the image author, or the date posted / time since post? =

Yes.  Check out our new widget settings (leave fields blank to disable display), and our new shortcode options on the 'Help' tab on the plugins Settings page in your CMS.

= What is the 'Cache Expire Time'? =

To speed up page loading, the images are downloaded from the Instagram server and stored to your local web server. At the interval specified by the 'Cache Expire Time', these files are deleted and read again from Instagram.
This means that when a new image is uploaded to Instagram, it will not appear on your website until the next cache expiry interval.

= Is it possible to arrange the 10 images horizontally? =

You can solve this using CSS styles.  Each thumbnail has a div container with a CSS class on it so you can customize it the way you want.

= Can I center the pictures? =

Yes.  Picture alignment can be customized directly in the CSS.  To center align the picture, add the following in your style.css:

div.easy-instagram-thumbnail-wrapper {text-align:center;}

This will actually align all the content to the center.  If you want just the image to be center aligned but keep the text aligned to the left, you also need to add:

div.easy-instagram-thumbnail-author, div.easy-instagram-thumbnail-caption, div.easy-instagram-thumbnail-time{text-align:left;}

= Can I change the size of the thumbnail? =

We may be able to include this feature in a future version.  In the meantime, you can change the thumbnail sizes by editing the CSS:

div.easy-instagram-thumbnail-wrapper img.easy-instagram-thumbnail{
width:200px !important;
height:auto !important;
}

If you want to resize the thumbnails only for example in a sidebar you can do something like this:

.sidebar_classname div.easy-instagram-thumbnail-wrapper img.easy-instagram-thumbnail{
width:200px !important;
height:auto !important;
}

= Can I feed photos with a certain tag but only from my own Instagram User ID? =

Not at this time, but it's something we're working on for future versions.  Currently you can choose from User ID, or Tag for each of your Instagram photo feeds.


== Screenshots ==

1. Your website when you’ve used the “Easy Instagram” shortcode in the content area.
2. Your website when you’ve used the “Easy Instagram” widget to place an Instagram photo feed in a sidebar.
3. The Instagram client registration form.
4. The Instagram client registration response.
5. The WordPress plugin settings page for “Easy Instagram”.
6. The WordPress widget settings where two widgets are placed in one sidebar (pre-v1.2.3).

== Changelog ==

= 1.2.5 =
* Fix: Instagram login error

= 1.2.4 =
* Fix: On some servers, in the plugin settings, the admin cannot log in to Instagram

= 1.2.3 =
* New: Link image author display to their Instagram profile page
* New: Add “author_format” option
* New: Add “thumb_click” option
* New: Add “time_text” and “time_format” options

= 1.2.2 =
* Improve “caption_hashtags” option to remove only hashtags from the end of the caption
* Add “caption_char_limit” option
* Fix: Widget title missing
* Fix: “CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set”
* Fix: Image author not showing when the image has no caption
* Fix: Invalid image time when the image has no caption

= 1.2.1 =
* Fix: “Cannot redeclare class InstagramException” for some install configurations.
* Fix: cURL is unable to get Instagram data over https on some installs.

= 1.2 =
* A few graphical edits.

= 1.1 =
* Fix a few cache related bugs.
* Add the “caption_hashtags” attribute to the shortcode.

= 1.0 =
* First version
