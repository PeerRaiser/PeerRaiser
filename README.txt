=== PeerRaiser - Peer-to-Peer Fundraising Donation Plugin ===
Contributors: nateallen
Tags: peer to peer, fundraising, donation, donation plugin, nonprofit, donate, crowdfunding, social fundraising, charity, nate allen, fundraising plugin, giving, stripe, donations, non-profit, church, gifts, campaigns, donation plugins, teams
Requires at least: 4.4.0
Tested up to: 4.8.1
Stable tag: 1.1.0
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

1. A user's fundraising page

== Changelog ==

= 1.1.0 =
* Add 'participant_id' column to the donations database

= 1.0.7 =
* If only one campaign is available, that campaign is now pre-selected on donation page
* Exclude test donations when getting total donations
* Fix filter peerraiser_donor_updated_{$key} not firing properly

= 1.0.6 =
* Fix issue with Step 1 not showing check mark when account connected
* Fix link to add a new campaign in Step 2

= 1.0.5 =
* Fix issue with connection controller using staging server instead of live server

= 1.0.4 =
* Convert some custom post types to custom tables

= 1.0.4 =
* Initial release