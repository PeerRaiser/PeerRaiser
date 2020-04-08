=== PeerRaiser - Peer-to-Peer Fundraising Donation Plugin ===
Contributors: nateallen
Tags: peer to peer, fundraising, donation, donation plugin, nonprofit, donate, crowdfunding, social fundraising, charity, nate allen, fundraising plugin, giving, stripe, donations, non-profit, church, gifts, campaigns, donation plugins, teams, peer raiser
Requires at least: 4.4.0
Tested up to: 5.4
Stable tag: 1.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PeerRaiser is a donation plugin that makes it easy to create powerful peer-to-peer fundraising campaigns on your own WordPress site.

== Description ==

**[PeerRaiser](https://peerraiser.com/?utm_source=readme&utm_medium=description_tab&utm_content=home&utm_campaign=readme)** is a donation plugin that makes it easy to create powerful peer-to-peer fundraising campaigns on your own WordPress site.

Empower individuals and teams to recruit new constituents and fundraise in support of your organization.

= Powerful =

PeerRaiser is fully featured. You can create unlimited campaigns and fundraisers. There are no "add-ons" or extensions to pay for. New features are added on a regular basis to make it even more powerful.

= Customizable =

This plugin takes full advantage of WordPress' awesome "hooks" system. You can easily customize PeerRaiser by using the built-in actions and filters throughout the plugin.

In addition, the plugin separates the HTML from the PHP into templates that can be overwritten.

To overwrite a template, create a "peerraiser" folder in your theme folder, and copy the template from the "views" folder into it. Make sure to keep the same folder structure.

For example, to customize the donation form, you could copy `views/frontend/donation-form.php` and place it in `your-theme/peerraiser/frontend/donation-form.php`, and then make your changes.

Note: We suggest using hooks instead of using this method, whenever possible.

= Easy =

PeerRaiser is so easy to use, you can get setup and accepting donations in minutes! There's no need to mess with SSL certificates or setup complicated merchant accounts.

== Installation ==

1. Upload the folder `peerraiser` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on the PeerRaiser icon in the left sidebar to access the dashboard.
4. Follow the steps on the dashboard to get started

== Frequently Asked Questions ==

This plugin is new, so there aren't any "Frequently Asked Questions" yet! Use the Support link if you need help.

== Screenshots ==

1. Fully customizable campaign page
2. Your dashboard, where you can track the progress of your campaigns
3. Created an unlimited number of campaigns
4. Donor information

== Changelog ==

= 1.3.2 =
* Renamed CMB2_hookup.php to CMB2_Hookup.php to resolve errors on some servers that are case sensitive.

= 1.3.1 =
* Updated CMB2 library to 2.7.0
* Fixed issue with comparing version during install

= 1.2.1 =
* Fixed error causing the "Add Team" admin page to not load correctly
* Fixed error "Can't use function return value in write context" caused by older PHP versions (prior to PHP 5.5)

= 1.2.0 =
* Donation notification email fixed
* Donation receipt email fixed
* Test mode will now only display test donations
* Statistics and donation totals are now based on test mode status
* Fixed issue with Team Name not displaying correctly on donation view
* Will now check if campaign has reached its limit and if the status isn't active
* Fixed team URL in "Top Teams" widget
* Participants no longer allowed to create multiple fundraisers for the same campaign
* When creating a team, if participant already has a fundraiser, automatically add that fundraiser to the team

= 1.1.6 =
* Added the ability to change the donation minimum

= 1.1.5 =
* Use get_option to get the peerraiser_slug value

= 1.1.4 =
* Fixed issue with variable in empty() function

= 1.1.3 =
* Added support for more currencies
* Fixed donation widget so it doesn't show test donations

= 1.1.2 =
* Deleted donor meta when donor is deleted
* Deleted donation meta when donation is deleted

= 1.1.1 =
* Added default widgets to sidebars when plugin is installed

= 1.1.0 =
* Added 'participant_id' column to the donations database

= 1.0.7 =
* If only one campaign is available, that campaign is now pre-selected on donation page
* Excluding test donations when getting total donations
* Fixed filter peerraiser_donor_updated_{$key} not firing properly

= 1.0.6 =
* Fixed issue with Step 1 not showing check mark when account connected
* Fixed link to add a new campaign in Step 2

= 1.0.5 =
* Fixed issue with connection controller using staging server instead of live server

= 1.0.4 =
* Converted some custom post types to custom tables

= 1.0.4 =
* Initial release
