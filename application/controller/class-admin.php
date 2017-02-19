<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser admin controller.
 */
class Admin extends Base {

    /**
     * @see PeerRaiser_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_admin_head' => array(
                array( 'add_html5shiv_to_admin_head' ),
            ),
            'peerraiser_admin_menu' => array(
                array( 'add_to_admin_panel' ),
            ),
            'peerraiser_admin_footer_scripts' => array(
                array( 'modify_footer' ),
            ),
            'peerraiser_admin_enqueue_scripts' => array(
                array( 'add_plugin_admin_assets' ),
                array( 'add_admin_pointers_script' ),
                array( 'register_admin_scripts' ),
                array( 'register_admin_styles' ),
            ),
            'peerraiser_admin_head' => array(
                array( 'on_campaigns_view' ),
            ),
            'peerraiser_enter_title_here' => array(
                array( 'customize_title' ),
            ),
            'wp_ajax_peerraiser_get_posts' => array(
                array( 'ajax_get_posts' ),
                array( 'peerraiser_on_ajax_send_json', 300 ),
            ),
            'wp_ajax_peerraiser_get_users' => array(
                array( 'ajax_get_users' ),
                array( 'peerraiser_on_ajax_send_json', 300 ),
            ),
        );
    }


    /**
     * Show plugin in administrator panel.
     *
     * @return void
     */
    public function add_to_admin_panel() {
        $plugin_page = \PeerRaiser\Helper\View::$pluginPage;
        add_menu_page(
            __( 'PeerRaiser', 'peerraiser' ),
            'PeerRaiser',
            'moderate_comments', // allow Super Admin, Admin, and Editor to view the settings page
            $plugin_page,
            array( $this, 'run' ),
            'dashicons-peerraiser-logo',
            81
        );

        $model = new \PeerRaiser\Model\Admin();
        $menu_items = $model->get_menu_items();

        foreach ( $menu_items as $name => $page ) {
            $slug = $page['url'];

            if ( strpos($slug, 'post_type') === false ) {
                $page_id = add_submenu_page(
                    $plugin_page,
                    $page['title'] . ' | ' . __( 'PeerRaiser', 'peerraiser' ),
                    $page['title'],
                    $page['cap'],
                    $slug,
                    isset( $page['run'] ) ? $page['run'] : array( $this, 'run_' . $name )
                );
            } else {
                $page_id = add_submenu_page(
                    $plugin_page,
                    $page['title'] . ' | ' . __( 'PeerRaiser', 'peerraiser' ),
                    $page['title'],
                    $page['cap'],
                    $slug,
                    null
                );
            }
            \PeerRaiser\Hooks::add_wp_action( 'load-' . $page_id, 'peerraiser_load_' . $page_id );
            $help_action = isset( $page['help'] ) ? $page['help'] : array( $this, 'help_' . $name );
            $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
            $dispatcher->add_listener( 'peerraiser_load_' . $page_id, $help_action );
        }
    }


    /**
     *
     * @param string $name
     * @param mixed  $args
     *
     * @return void
     */
    public function __call( $name, $args ) {
        if ( substr( $name, 0, 4 ) == 'run_' ) {
            return $this->run( strtolower( substr( $name, 4 ) ) );
        } elseif ( substr( $name, 0, 5 ) == 'help_' ) {
            return $this->help( strtolower( substr( $name, 5 ) ) );
        }
    }


    /**
     * @see \PeerRaiser\Core\View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load PeerRaiser-specific CSS
        wp_register_style(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'css_url' ) . 'peerraiser-admin.css',
            array(),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' )
        );
        wp_register_style(
            'open-sans',
            '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext'
        );
        wp_register_style(
            'peerraiser-select2',
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'css_url' ) . 'vendor/select2.min.css',
            array(),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' )
        );
        wp_enqueue_style( 'open-sans' );
        wp_enqueue_style( 'peerraiser-select2' );
        wp_enqueue_style( 'peerraiser-admin' );

        // load PeerRaiser-specific JS
        wp_enqueue_script( 'peerraiser-admin' );

    }


    /**
     * Add html5shim to the admin_head() for Internet Explorer < 9.
     *
     * @wp-hook admin_head
     * @param PeerRaiser_Core_Event $event
     * @return void
     */
    public function add_html5shiv_to_admin_head( \PeerRaiser\Core\Event $event ) {
        $event->set_echo( true );
        $view_args = array(
            'scripts' => array(
                '//html5shim.googlecode.com/svn/trunk/html5.js',
            ),
        );
        $this->assign( 'peerraiser', $view_args );

        $event->set_result( $this->get_text_view( 'backend/partials/html5shiv' ) );
    }

    /**
     * Constructor for class PeerRaiserController, processes the pages in the plugin backend.
     *
     * @param string $page
     *
     * @return void
     */
    public function run( $page = '' ) {
        $this->load_assets();

        // return default page, if no specific page is requested
        if ( empty( $page ) ) {
            $page = 'dashboard';
        }

        switch ( $page ) {
            default:
            case 'dashboard' :
                $dashboard_controller = new \PeerRaiser\Controller\Admin\Dashboard( \PeerRaiser\Core\Setup::get_plugin_config() );
                $dashboard_controller->render_page();
                break;
            case 'campaigns' :
                $campaigns_controller = new \PeerRaiser\Controller\Admin\Campaigns( \PeerRaiser\Core\Setup::get_plugin_config() );
                $campaigns_controller->render_page();
                break;
            case 'teams' :
                $teams_controller = new \PeerRaiser\Controller\Admin\Teams( \PeerRaiser\Core\Setup::get_plugin_config() );
                $teams_controller->render_page();
                break;
            case 'donations' :
                $donations_controller = new \PeerRaiser\Controller\Admin\Donations( \PeerRaiser\Core\Setup::get_plugin_config() );
                $donations_controller->render_page();
                break;
            case 'donors' :
                $donors_controller = new \PeerRaiser\Controller\Admin\Donors( \PeerRaiser\Core\Setup::get_plugin_config() );
                $donors_controller->render_page();
                break;
            case 'settings' :
                $settings_controller = new \PeerRaiser\Controller\Admin\Settings( \PeerRaiser\Core\Setup::get_plugin_config() );
                $settings_controller->render_page();
                break;
        }
    }

    /**
     * Render contextual help, depending on the current page.
     *
     * @param string $tab
     *
     * @return void
     */
    public function help( $tab = '' ) {
        switch ( $tab ) {
            case 'wp_edit_post':
            case 'wp_add_post':
                $this->render_add_edit_post_page_help();
                break;

            case 'dashboard':
                $this->render_dashboard_tab_help();
                break;

            // case 'appearance':
            //     $this->render_appearance_tab_help();
            //     break;

            default:
                break;
        }
    }

    /**
     * Add contextual help for add / edit post page.
     *
     * @return void
     */
    protected function render_add_edit_post_page_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_add_edit_post_page_help',
            'title'   => __( 'PeerRaiser', 'peerraiser' ),
            'content' => __( '
            <p>
                <strong>Setting Prices</strong><br>
                You can set an individual price for each post.<br>
                Possible prices are either 0 Euro (free) or any value between 0.05 Euro (inclusive) and 149.99 Euro (inclusive).<br>
                If you set an individual price, category default prices you might have set for the post\'s category(s)
                won\'t apply anymore, unless you make the post use a category default price.
            </p>
            <p>
                <strong>Dynamic Pricing Options</strong><br>
                You can define dynamic price settings for each post to adjust prices automatically over time.<br>
                <br>
                For example, you could sell a "breaking news" post for 0.49 Euro (high interest within the first 24 hours)
                and automatically reduce the price to 0.05 Euro on the second day.
            </p>
            <p>
                <strong>Teaser</strong><br>
                The teaser should give your visitors a first impression of the content you want to sell.<br>
                You don\'t have to provide a teaser for every single post on your site:<br>
                by default, the PeerRaiser plugin uses the first 60 words of each post as teaser content.
                <br>
                Nevertheless, we highly recommend manually creating the teaser for each post, to increase your sales.
            </p>
            <p>
                <strong>PPU (Pay-per-Use)</strong><br>
                If you choose to sell your content as <strong>Pay-per-Use</strong>, a user pays the purchased content <strong>later</strong>. The purchase is added to his PeerRaiser invoice and he has to log in to PeerRaiser and pay, once his invoice has reached 5.00 Euro.<br>
                PeerRaiser <strong>recommends</strong> Pay-per-Use for all prices up to 5.00 Euro as they deliver the <strong>best purchase experience</strong> for your users.<br>
                PPU is possible for prices between (including) <strong>0.05 Euro</strong> and (including) <strong>5.00 Euro</strong>.
            </p>
            <p>
                <strong>SIS (Single Sale)</strong><br>
                If you sell your content as <strong>Single Sale</strong>, a user has to <strong>log in</strong> to PeerRaiser and <strong>pay</strong> for your content <strong>immediately</strong>.<br>
                Single Sales are especially suitable for higher-value content and / or content that immediately occasions costs (e. g. license fees for a video stream).<br>
                Single Sales are possible for prices between (including) <strong>1.49 Euro</strong> and (including) <strong>149.99 Euro</strong>.
            </p>', 'peerraiser'
            ),
        ) );
    }

    /**
     * Add contextual help for dashboard tab.
     *
     * @return  void
     */
    protected function render_dashboard_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_dashboard_tab_help_conversion',
            'title'   => __( 'Conversion', 'peerraiser' ),
            'content' => __( '
            <p>
                The <strong>Conversion</strong> (short for Conversion Rate) is the share of visitors of a specific post, who actually <strong>bought</strong> the post.<br>
                A conversion of 100% would mean that every user who has visited a post page and has read the teaser content had bought the post with PeerRaiser.<br>
                The conversion rate is one of the most important metrics for selling your content successfully: It indicates, if the price is perceived as adequate and if your content fits your audience\'s interests.
            </p>
            <p>
                The metric <strong>New Customers</strong> indicates the share of your customers who bought with PeerRaiser for the first time in the reporting period.<br>
                Please note that this is only an approximate value.
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_dashboard_tab_help_items_sold',
            'title'   => __( 'Items Sold', 'peerraiser' ),
            'content' => __( '
            <p>
                The column <strong>Items Sold</strong> provides an overview of all your sales in the reporting period.
            </p>
            <p>
                <strong>AVG Items Sold</strong> (short for Average Items Sold) indicates how many posts you sold on average per day in the reporting period.
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_dashboard_tab_help_gross_revenue',
            'title'   => __( 'Committed Revenue', 'peerraiser' ),
            'content' => __( '
            <p>
                <strong>Committed Revenue</strong> is the value of all purchases, for which your users have committed themselves to pay later (or paid immediately in case of a Single Sale purchase).
            </p>
            <p>
                <strong>AVG Revenue</strong> (short for Average Revenue) indicates the average revenue per day in the reporting period.
            </p>
            <p>
                Please note that this <strong>is not the amount of money you will receive with your next PeerRaiser payout</strong>, as a user will have to pay his invoice only once it reaches 5.00 Euro and PeerRaiser will deduct a fee of 15% for each purchase that was actually paid.
            </p>', 'peerraiser'
            ),
        ) );
    }

    /**
     * Add contextual help for pricing tab.
     *
     * @return  void
     */
    protected function render_pricing_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_pricing_tab_help_global_default_price',
            'title'   => __( 'Global Default Price', 'peerraiser' ),
            'content' => __( '
            <p>
                The global default price is used for all posts, for which no
                category default price or individual price has been set.<br>
                Accordingly, setting the global default price to 0.00 Euro makes
                all articles free, for which no category default price or
                individual price has been set.
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_pricing_tab_help_category_default_price',
            'title'   => __( 'Category Default Prices', 'peerraiser' ),
            'content' => __( '
            <p>
                A category default price is applied to all posts in a given
                category that don\'t have an individual price.<br>
                A category default price overwrites the global default price.<br>
                If a post belongs to multiple categories, you can choose on
                the add / edit post page, which category default price should
                be effective.<br>
                For example, if you have set a global default price of 0.15 Euro,
                but a post belongs to a category with a category default price
                of 0.30 Euro, that post will sell for 0.30 Euro.
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_pricing_tab_help_currency',
            'title'   => __( 'Currency', 'peerraiser' ),
            'content' => __( '
            <p>
                Currently, the plugin only supports Euro as default currency, but
                you will soon be able to choose between different currencies for your blog.<br>
                Changing the standard currency will not convert the prices you
                have set.
                Only the currency code next to the price is changed.<br>
                For example, if your global default price is 0.10 Euro and you
                change the default currency to U.S. dollar, the global default
                price will be 0.10 USD.
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_pricing_tab_help_time_passes',
            'title'   => __( 'Time Passes', 'peerraiser' ),
            'content' => __( '
            <p>
                <strong>Validity of Time Passes</strong><br>
                With time passes, you can offer your users <strong>time-limited</strong> access to your content. You can define, which content a time pass should cover and for which period of time it should be valid. A time pass can be valid for <strong>all PeerRaiser content</strong>
                <ul>
                    <li>on your <strong>entire website</strong>,</li>
                    <li>in one <strong>specific category</strong>, or</li>
                    <li>on your entire website <strong>except from a specific category</strong>.</li>
                </ul>
                The <strong>validity period</strong> of a time pass starts with the <strong>purchase</strong> and is defined for a <strong>continuous</strong> use – i.e. it doesn\'t matter, if a user is on your website during the entire validity period. After a time pass has expired, the access to the covered content is automatically refused. Please note: Access to pages which are <strong>still open</strong> when a pass expires will be refused only after <strong>reloading</strong> the respective page. <strong>Any files</strong> (images, documents, presentations...), that were downloaded during the validity period, can still be used after the access has expired – but the user will <strong>not</strong> be able to <strong>download them </strong> without purchasing again.
            </p>
            <p>
                <strong>Deleting Time Passes</strong><br>
                Please be aware, that after <strong>deleting</strong> a time pass, users who have bought this time pass <strong>will lose</strong> their access to the covered content. <strong>Time Passes cannot be restored.</strong>
            </p>
            <p>
                <strong>Time Passes and Individual Sales</strong><br>
                When a user purchases a time pass, he has access to all the content covered by this pass during the validity period. Of course, you can still sell your content individually.<br>
                Example: A user has already purchased the post "New York – a Travel Report" for 0.29 Euro. Now he purchases a Week Pass for the category "Travel Reports" for 0.99 Euro. The category also contains the "New York" post. For one week, he can now read all posts in the category "Travel Reports" for a fixed price of 0.99 Euro. After this week, the access expires automatically. During the validity period, the user will not see any PeerRaiser purchase buttons for posts in the category "Travel Reports". After the pass has expired, the user will still have access to the post he had previously purchased individually.
            </p>
            <p>
                <strong>Action</strong><br>
                You can display time passes by implementing the <a href="admin.php?page=peerraiser-appearance-tab#lp_timePassAppearance" target="_blank">action \'peerraiser_time_passes\'</a> into your theme.<br>
                This action will display all time passes which are relevant for the user in the current context and sorts them accordingly.<br>
                Example: You offer a <strong>Week Pass "Sport"</strong> for the category sport, a <strong>Week Pass "News"</strong> for the category "News" and a <strong>Month Pass Entire Website</strong> for all the content on your website.<br>
                Depending on the page he is currently visiting, a user will see different time passes:
                <ul>
                    <li>On the post page of a post in the category <strong>"Sport"</strong>, the <strong>Week Pass "Sport"</strong> will be listed first, followed by the "Month Pass Entire Website". The <strong>Week Pass "News"</strong> is <strong>not relevant</strong> is this context and will not be displayed.</li>
                    <li>On the post page of a post in the category <strong>"News"</strong>, the <strong>Week Pass "News"</strong> will be listed first, followed by the "Month Pass Entire Website". The <strong>Week Pass "Sport"</strong> is <strong>not relevant</strong> is this context and will not be displayed.</li>
                </ul>
            </p>
            <p>
                <strong>Vouchers</strong><br>
                You can create any number of voucher codes for each time pass. A voucher code allows one (or multiple) user(s) to purchase a time pass for a reduced price. A user can enter a voucher code below the available time passes by clicking <strong>\'Redeem Voucher\'</strong>. If the entered code is a valid voucher code, the price of the time pass, the code is valid for, will be reduced.<br>
                A voucher code can be used <strong>any number of times</strong> and is <strong>not linked</strong> to a specific user.<br>
                If you <strong>delete</strong> a voucher code, this will <strong>not affect</strong> the validity of time passes which have already been purchased using this voucher code.
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_pricing_tab_help_time_passes',
            'title'   => __( 'Time Passes', 'peerraiser' ),
            'content' => __( '
            <p>
                <strong>Validity of Time Passes</strong><br>
                With time passes, you can offer your users <strong>time-limited</strong> access to your content. You can define, which content a time pass should cover and for which period of time it should be valid. A time pass can be valid for <strong>all PeerRaiser content</strong>
            </p>
            <ul>
                <li>on your <strong>entire website</strong>,</li>
                <li>in one <strong>specific category</strong>, or</li>
                <li>on your entire website <strong>except from a specific category</strong>.</li>
            </ul>
            <p>
                The <strong>validity period</strong> of a time pass starts with the <strong>purchase</strong> and is defined for a <strong>continuous</strong> use – i.e. it doesn\'t matter, if a user is on your website during the entire validity period. After a time pass has expired, the access to the covered content is automatically refused. Please note: Access to pages which are <strong>still open</strong> when a pass expires will be refused only after <strong>reloading</strong> the respective page. <strong>Any files</strong> (images, documents, presentations...), that were downloaded during the validity period, can still be used after the access has expired – but the user will <strong>not</strong> be able to <strong>download them </strong> without purchasing again.
            </p>
            <p>
                <strong>Deleting Time Passes</strong><br>
                If you <strong>delete</strong> a time pass, users who have bought this time pass <strong>will still have access</strong> to the covered content. Deleted time passes <strong>can\'t be restored</strong>.
            </p>
            <p>
                <strong>Time Passes and Individual Sales</strong><br>
                When a user purchases a time pass, he has access to all the content covered by this pass during the validity period. Of course, you can still sell your content individually.<br>
                Example: A user has already purchased the post "New York – a Travel Report" for 0.29 Euro. Now he purchases a Week Pass for the category "Travel Reports" for 0.99 Euro. The category also contains the "New York" post. For one week, he can now read all posts in the category "Travel Reports" for a fixed price of 0.99 Euro. After this week, the access expires automatically. During the validity period, the user will not see any PeerRaiser purchase buttons for posts in the category "Travel Reports". After the pass has expired, the user will still have access to the post he had previously purchased individually.
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_pricing_tab_help_time_pass_vouchers',
            'title'   => __( 'Time Pass Vouchers', 'peerraiser' ),
            'content' => __( '
            <p>
                You can create any number of voucher codes for each time pass. A voucher code allows one (or multiple) user(s) to purchase a time pass for a reduced price. A user can enter a voucher code right <strong>below the time passes</strong> by clicking <strong>"I have a voucher"</strong>. If the entered code is a valid voucher code, the price of the respective time pass will be reduced.<br>
                A voucher code can be used <strong>any number of times</strong> and is <strong>not linked</strong> to a specific user. If you want to invalidate a time pass voucher code, you can simply delete it.<br>
                <strong>Deleting</strong> a voucher code will <strong>not affect</strong> the validity of time passes which have already been purchased using this voucher code.
            </p>
            <p>
            Follow these steps to create a voucher code:
            </p>
            <ul>
                <li>Click the "Edit" icon next to the time pass for which you want to create a voucher code.</strong>,</li>
                <li>Enter a price next to \'Offer this time pass at a reduced price of\'. If you enter a price of \'0.00 Euro\', anyone with this voucher code can purchase the respective time pass for 0.00 Euro.<br>
                    If you enter a price of e.g. \'0.20 Euro\', entering this voucher code will change the price of the respective time pass to 0.20 Euro.</li>
                <li>Click the \'Save\' button.</li>
            </ul>', 'peerraiser'
            ),
        ) );
    }

    /**
     * Add contextual help for appearance tab.
     *
     * @return  void
     */
    protected function render_appearance_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_appearance_tab_help_preview_mode',
            'title'   => __( 'Preview Mode', 'peerraiser' ),
            'content' => __( '
            <p>
                The preview mode defines, how teaser content is shown to your
                visitors.<br>
                You can choose between two preview modes:
            </p>
            <ul>
                <li>
                    <strong>Teaser only</strong> &ndash; This mode shows only
                    the teaser with an unobtrusive purchase link below.
                </li>
                <li>
                    <strong>Teaser + overlay</strong> &ndash; This mode shows
                    the teaser and an excerpt of the full content under a
                    semi-transparent overlay that briefly explains PeerRaiser.<br>
                    The plugin never loads the entire content before a user has
                    purchased it.
                </li>
            </ul>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_appearance_tab_help_purchase_button_position',
            'title'   => __( 'Purchase Button Position', 'peerraiser' ),
            'content' => __( '
            <p>
                You can choose, if the PeerRaiser purchase button is positioned at its default or a custom position:
            </p>
            <ul>
                <li>
                    <strong>Default position</strong> &ndash; The PeerRaiser purchase button is displayed at the top on the right below the title.
                </li>
                <li>
                    <strong>Custom position</strong> &ndash; You can position the PeerRaiser purchase button yourself by using the stated WordPress action.
                </li>
            </ul>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_appearance_tab_help_time_pass_position',
            'title'   => __( 'Time Pass Position', 'peerraiser' ),
            'content' => __( '
            <p>
                You can choose, if time passes are positioned at their default or a custom position:
            </p>
            <ul>
                <li>
                    <strong>Default position</strong> &ndash; Time passes are displayed right below each paid article.<br>
                    If you want to display time passes also for free posts, you can choose \'I want to display the time passes widget on free and paid posts\' in the plugin\'s advanced settings (Settings > PeerRaiser).
                </li>
                <li>
                    <strong>Custom position</strong> &ndash; You can position time passes yourself by using the stated WordPress action.
                </li>
            </ul>', 'peerraiser'
            ),
        ) );
    }

    /**
     * Add contextual help for account tab.
     *
     * @return void
     */
    protected function render_account_tab_help() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_account_tab_help_api_credentials',
            'title'   => __( 'API Credentials', 'peerraiser' ),
            'content' => __( '
            <p>
                To access the PeerRaiser API, you need PeerRaiser API credentials,
                consisting of
            </p>
            <ul>
                <li><strong>Merchant ID</strong> (a 22-character string) and</li>
                <li><strong>API Key</strong> (a 32-character string).</li>
            </ul>
            <p>
                PeerRaiser runs two completely separated API environments that
                need <strong>different API credentials:</strong>
            </p>
            <ul>
                <li>
                    The <strong>Sandbox</strong> environment for testing and
                    development use.<br>
                    In this environment you can play around with PeerRaiser
                    without fear, as your transactions will only be simulated
                    and not actually be processed.<br>
                    PeerRaiser guarantees no particular service level of
                    availability for this environment.
                </li>
                <li>
                    The <strong>Live</strong> environment for production use.<br>
                    In this environment all transactions will be actually
                    processed and credited to your PeerRaiser merchant account.<br>
                    The PeerRaiser SLA for availability and response time apply.
                </li>
            </ul>
            <p>
                The PeerRaiser plugin comes with a set of <strong>public Sandbox
                credentials</strong> to allow immediate testing use.
            </p>
            <p>
                If you want to switch to <strong>Live mode</strong> and sell
                content, you need your individual <strong>Live API credentials.
                </strong><br>
                Due to legal reasons, we can email you those credentials only
                once we have received a <strong>signed merchant contract</strong>
                including <strong>all necessary identification documents</strong>.<br>
                <a href="https://www.peerraiser.net/how-to-become-a-content-provider" target="blank">Visit our website to read more about how to become a content provider.</a>
            </p>', 'peerraiser'
            ),
        ) );
        $screen->add_help_tab( array(
            'id'      => 'peerraiser_account_tab_help_plugin_mode',
            'title'   => __( 'Plugin Mode', 'peerraiser' ),
            'content' => __( '
            <p>You can run the PeerRaiser plugin in three modes:</p>
            <ul>
                <li>
                    <strong>Invisible Test Mode</strong> &ndash; This test mode lets you
                    test your plugin configuration.<br>
                    While providing the full plugin functionality, payments are
                    only simulated and not actually processed.<br>
                    The plugin will <em>only</em> be visible to admin users,
                    not to visitors.<br>
                    This is the <strong>default</strong> setting after activating the plugin for the first time.
                </li>
                <li>
                    <strong>Visible Test Mode</strong> &ndash; The plugin will be <strong>visible</strong> to regular visitors and users,<br>
                    but payments will still only be simulated and not actually processed.
                </li>
                <li>
                    <strong>Live Mode</strong> &ndash; In live mode, the plugin
                    is publicly visible and manages access to paid content.<br>
                    All payments are actually processed.
                </li>
            </ul>
            <p>
                Using the PeerRaiser plugin usually requires some adjustments of
                your theme.<br>
                Therefore, we recommend installing, configuring, and testing
                the PeerRaiser plugin on a test system before activating it on
                your production system.
            </p>', 'peerraiser'
            ),
        ) );
    }


    /**
     * Add WordPress pointers to pages.
     *
     * @param \PeerRaiser\Core\Event $event
     * @return void
     */
    public function modify_footer( \PeerRaiser\Core\Event $event ) {
        $pointers = \PeerRaiser\Controller\Admin::get_pointers_to_be_shown();

        // don't render the partial, if there are no pointers to be shown
        if ( empty( $pointers ) ) {
            return;
        }

        // assign pointers
        $view_args = array(
            'pointers' => $pointers,
        );

        $this->assign( 'peerraiser', $view_args );
        $result = $event->get_result();
        $result .= $this->get_text_view( 'backend/partials/pointer-scripts' );
        $event->set_result( $result );
    }


    /**
     * Load PeerRaiser stylesheet with PeerRaiser vector logo on all pages where the admin menu is visible.
     *
     * @return void
     */
    public function add_plugin_admin_assets() {
        wp_register_style(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->css_url . 'peerraiser-admin.css',
            array(),
            \PeerRaiser\Core\Setup::get_plugin_config()->version
        );
        wp_enqueue_style( 'peerraiser-admin' );
    }


    /**
     * Hint at the newly installed plugin using WordPress pointers.
     *
     * @return void
     */
    public function add_admin_pointers_script() {
        $pointers = \PeerRaiser\Controller\Admin::get_pointers_to_be_shown();

        // don't enqueue the assets, if there are no pointers to be shown
        if ( empty( $pointers ) ) {
            return;
        }

        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );
    }


    /**
     * Return all pointer constants from current class.
     *
     * @return array $pointers
     */
    public static function get_all_pointers() {
        $reflection         = new \ReflectionClass( __CLASS__ );
        $class_constants    = $reflection->getConstants();
        $pointers           = array();

        if ( $class_constants ) {
            foreach ( array_keys( $class_constants ) as $key_value ) {
                if ( strpos( $key_value, 'POINTER' ) !== false ) {
                    $pointers[] = $class_constants[ $key_value ];
                }
            }
        }

        return $pointers;
    }


    /**
     * Registers the main admin script so it can be enqueued on the other PeerRaiser pages
     *
     * @since     1.0.0
     * @return    null
     */
    public function register_admin_scripts() {
        wp_register_script(
            'peerraiser-admin',
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'js_url' ) . 'peerraiser-admin.js',
            array( 'jquery', 'peerraiser-select2' ),
            \PeerRaiser\Core\Setup::get_plugin_config()->get( 'version' ),
            true
        );
        wp_register_script(
            'peerraiser-select2',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('js_url') . 'vendor/select2.min.js',
            array( 'jquery' ),
            '4.0.2',
            true
        );
    }


    public function register_admin_styles() {
        wp_register_style(
            'peerraiser-select2',
            \PeerRaiser\Core\Setup::get_plugin_config()->get('css_url') . 'vendor/select2.min.css',
            array(),
            '4.0.2'
        );
        wp_register_style(
            'peerraiser-font-awesome',
            'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css',
            array(),
            '4.5.0'
        );
    }


    public function on_campaigns_view( \PeerRaiser\Core\Event $event ) {
        $current_screen = get_current_screen();
        $campaigns_count = wp_count_posts( 'pr_campaign' );
        if ( $current_screen->id == 'edit-pr_campaign' && $campaigns_count->publish == 0) {
            $admin_notices = \PeerRaiser\Controller\Admin\Admin_Notices::get_instance();
            $message = __( 'Create your first campaign to get started. <a href="post-new.php?post_type=pr_campaign">Create Campaign</a>' , 'peerraiser' );
            $admin_notices::add_notice( $message );
        }
    }


    /**
     * Customize the "Enter title here" placeholder in the Title field based on post type
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     */
    public function customize_title( \PeerRaiser\Core\Event $event ) {
        $current_screen = get_current_screen();

        switch ($current_screen->post_type) {
            case 'fundraiser':
                if ( $event->has_argument( 'fundraiser' ) ) {
                    $title = $event->get_argument( 'fundraiser' );
                } else {
                    $title = __( 'Enter the fundraiser name here', 'peerraiser' );
                }
                break;

            default:
                $title = $event->get_result();
                break;
        }

        $event->set_result( $title );
    }


    /**
     * Retreive posts and creates <option>for select lists
     *
     * @since     1.0.0
     * @param     \PeerRaiser\Core\Event    $event
     *
     * @return    array                              Data formatted for select2
     */
    public function ajax_get_posts( \PeerRaiser\Core\Event $event ) {
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
            )
        );

        $choices = \PeerRaiser\Helper\Field::get_choices( $_POST );

        $event->set_result( $choices );

    }


    public function ajax_get_users( \PeerRaiser\Core\Event $event ) {
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
            )
        );

        $count_args  = array(
            'number'    => 999999
        );
        if ( isset($_POST['q'] ) ){
            $count_args['search'] = '*'.sanitize_text_field($_POST['q']).'*';
            $count_args['search_columns'] = array( 'display_name', 'user_email' );
        }

        $user_count_query = new \WP_User_Query($count_args);
        $user_count = $user_count_query->get_results();

        // count the number of users found in the query
        $total_users = $user_count ? count($user_count) : 1;

        // grab the current page number and set to 1 if no page number is set
        $page = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : 1;

        // how many users to show per page
        $users_per_page = 10;

        // calculate the total number of pages.
        $total_pages = 1;
        $offset = $users_per_page * ($page - 1);
        $total_pages = ceil($total_users / $users_per_page);

        // main user query
        $args  = array(
            // order results by display_name
            'orderby'   => 'display_name',
            'fields'    => 'all_with_meta',
            'number'    => $users_per_page,
            'offset'    => $offset
        );

        if ( isset($_POST['q'] ) ){
            $args['search'] = '*'.sanitize_text_field($_POST['q']).'*';
            $args['search_columns'] = array( 'display_name', 'user_email' );
        }

        $user_query = new \WP_User_Query( $args );

        // empty array to fill with data
        $data = array();

        // User Loop
        if ( ! empty( $user_query->results ) ) {
            foreach ( $user_query->results as $user ) {
                $line = array(
                    'id'   => $user->ID,
                    'text' => $user->display_name
                );
                array_push($data, $line);
            }
        }

        $event->set_result(
            array(
                'items' => $data ,
                'total_count' => $total_users
            )
        );
    }

}
