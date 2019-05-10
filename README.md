# MailChimp Integration

**INACTIVE NOTICE: This plugin is unsupported by WPMUDEV, we've published it here for those technical types who might want to fork and maintain it for their needs.**

## Translations

Translation files can be found at https://github.com/wpmudev/translations

## Mailchimp Integration ties WordPress to MailChimp for seriously powerful email marketing, growing a subscriber base and list syncing.

Get far more than just easy-embed signup forms. Tap core features of MailChimp that allow you to not only build your email subscriber list – but integrates to manage your site's user database.

### Build a Massive Following

MailChimp Integration helps you effectively capture new subscribers from anywhere on your site using simple customizable signup forms. The included MailChimp widget and shortcode generator make it possible to add subscription forms anywhere on your site in under 60 seconds.

### Smart Subscription Management

Eliminate the frustrations associated with managing users across multiple lists. Quickly establish a fluid communication stream with MailChimp and make sure your site user list and email list are always a perfect match. Automatically subscribe new users to your mailing list, add subscribers to your site user list and trigger user removal when unsubscribing from your mailing list.

### Merge and Grow Your User Base

Merge existing Subscriber and User lists with a single click. Import makes it easy to sync existing lists and works to complete missing user information, eliminate duplicates, and remove spammed and deleted users from your selected MailChimp list.

### Make Room to Expand

From our simple subscription form generator to managing user lists of hundreds or even thousands, MailChimp Integration is the right fit for sites both large and small. If you’re serious about email and building a huge following, create a powerful marketing machine by expanding WordPress with MailChimp.

## Usage

For help with installing plugins please refer to our [Plugin installation guide](https://premium.wpmudev.org/wpmu-manual/installing-regular-plugins-on-wpmu/). Once installed, login to your admin panel for WordPress or Multisite and activate the plugin:

*   On regular WordPress installs – visit **Plugins** and **Activate** the plugin. The plugin options will appear in your Settings menu. [

![Plugin options in single site installs of WordPress.](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-single-menu.png)

](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-single-menu.png)

 Plugin options in single site installs of WordPress.

*   For WordPress Multisite installs – Visit **Network Admin -> Plugins** and **Network Activate** the plugin. The plugin options will appear in your Network Settings menu. **Note:** When used on a multisite install, MailChimp Integration _**must**_ be network activated to work. 

![Plugin options on multisite installs of WordPress.](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-network-menu.png)

 Plugin options on multisite installs of WordPress.

### To use:

First, set up an account with [MailChimp](http://www.mailchimp.com/) (unless you already have one of course). Then you can either create a new MailChimp list for your users, e.g. Edublogs.org users, or use an existing one. 

![mailchimp-integration-1300-create-list](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-create-list.png)

 Login into your WordPress dashboard, and go to the MailChimp settings. Once you enter your MailChimp API key and click Save Changes, additional settings will become available. 

![1\. Allow sub-sites to use the widget. 2\. Your MailChimp API key. 3\. Select whether users should be opted in automatically. 4\. Ignore duplicate email accounts. 5\. Select your mailing list.](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-settings-1.png)

 1\. Allow sub-sites to use the widget.  
2\. Your MailChimp API key.  
3\. Select whether users should be opted in automatically.  
4\. Ignore duplicate email accounts.  
5\. Select your mailing list.

 1\. The _Allow widget in all subsites_ checkbox is only available when network-activated. Checking it will allow sub-site admins to place the MailChimp signup widget on their sites too. 2\. _MailChimp API Key_ is, well, your key. :) 3\. If you set _Auto Opt-in_ to "Yes", your users will not receive an email confirmation. Be careful with this: some locales have anti-spam regulations that require the double-optin. 4\. You can set _Ignore email addresses including + signs_ to "Yes" if some of your subscribers have duplicate accounts using the "+" sign. Don't want to annoy them with duplicate emails. 5\. _Mailing List_ is where you select the MailChimp list you want to sync. You can also sync all your existing WordPress users to your MailChimp list. 

![1\. Select your MailChimp mailing list. 2\. Select whether users should be opted in automatically.](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-settings-2.png)

 1\. Select your MailChimp mailing list.  
2\. Select whether users should be opted in automatically.

 1\. Select your Mailing List. 2\. Select whether to _Auto Opt-in_ your users or not. 3\. Click _Import_ to synchronize your WordPress users to your MailChimp list. The bottom section of your MailChimp settings page is a very handy excerpt of the error log. It will display the most recent 100 lines right in your admin to help you with troubleshooting. It even tells you if email addresses have been banned. 

![mailchimp-integration-1300-errorlog](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-errorlog.png)

 Finally, if you want your users to be able to subscribe directly to your MailChimp list, you can customize how the widget form should appear under Appearance > Widgets. 

![mailchimp-integration-1300-widget](https://premium.wpmudev.org/wp-content/uploads/2009/04/mailchimp-integration-1300-widget.png)

### Using MailChimp Webhooks

With MailChimp webhooks, you can synchronize your MailChimp lists and WordPress users. Yes, you read that right, whenever someone subscribes to your MailChimp list, they can be automatically added as a user on your WordPress site. Booya! The settings to get this done on your WordPress site are quite simple, and appear once you have configured & saved the previous settings (eg - you have entered your MailChimp API key, selected the list you want use & saved changes): 

![mailchimp-integration-1711-webhooks](https://premium.wpmudev.org/wp-content/uploads/2014/12/mailchimp-integration-1711-webhooks.png)

 1\. First, enter any name you would like your webhook to have in _Specify a unique webhook key_ field. Then click _Save Changes_ at the bottom of your screen. 2\. _Your Webhook URL_ will then appear right beneath the field where you just entered the key name. That is what you must enter in your MailChimp account. 3\. Select the _Action to take when user unsubscribes from list_. You can select to either mark them as unsubscribed, or to delete them from your WordPress install. _Save Changes_ again. Now, head on over to your MailChimp account page and follow the simple directions you'll find [on this page](http://kb.mailchimp.com/integrations/other-integrations/what-are-webhooks-and-how-can-i-set-them-up) to set up your webhook. The URL you'll be entering in the Callback URL field is the one you just generated with your unique key name.
