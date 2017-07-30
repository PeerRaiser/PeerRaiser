<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Controller\Base;
use \PeerRaiser\Model\Admin\Admin_Notices as Admin_Notices_Model;
use \PeerRaiser\Model\Campaign;
use \PeerRaiser\Model\Currency;
use \PeerRaiser\Model\Admin\Campaign_List_Table;
use \PeerRaiser\Core\Setup;
use \PeerRaiser\Helper\Stats;
use \PeerRaiser\Helper\View;
use \PeerRaiser\Helper\Text;
use \DateTime;
use \DateTimeZone;

class Campaigns extends Base {

    public function register_actions() {
        add_action( 'cmb2_admin_init',                           array( $this, 'register_meta_boxes' ) );
        add_action( 'peerraiser_page_peerraiser-campaigns',      array( $this, 'load_assets' ) );
        add_action( 'peerraiser_add_campaign',	                 array( $this, 'handle_add_campaign' ) );
        add_action( 'peerraiser_update_campaign',                array( $this, 'handle_update_campaign' ) );
        add_action( 'peerraiser_delete_campaign',                array( $this, 'delete_campaign' ) );
        add_action( 'peerraiser_updated_campaign_meta',          array( $this, 'maybe_schedule_cron' ), 10, 3 );
        add_action( 'peerraiser_updated_campaign_meta',          array( $this, 'maybe_update_date_utc' ), 10, 3 );
        add_action( 'peerraiser_deleted_campaign_meta',          array( $this, 'maybe_clear_cron' ), 10, 2 );
        add_action( 'peerraiser_deleted_campaign_meta',          array( $this, 'maybe_delete_end_date_utc' ), 10, 2 );
        add_action( 'peerraiser_end_campaign',                   array( $this, 'end_campaign' ) );
        add_action( 'wp_ajax_peerraiser_get_fundraisers',        array( $this, 'ajax_get_fundraisers' ) );
        add_action( 'wp_ajax_nopriv_peerraiser_get_fundraisers', array( $this, 'ajax_get_fundraisers' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $default_views = array( 'list', 'add', 'summary' );

        // Get the correct view
        $view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'list';
        $view = in_array( $view, $default_views ) ? $view : apply_filters( 'peerraiser_campaign_admin_view', 'list', $view );

        // Assign data to the view
        $view_args = array(
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'admin_url'            => get_admin_url(),
            'list_table'           => new Campaign_List_Table(),
            'campaign_admin'       => new \PeerRaiser\Model\Admin\Campaigns_Admin()
        );

        if ( $view === 'summary' ) {
            $view_args['campaign'] = new \PeerRaiser\Model\Campaign( $_REQUEST['campaign'] );
        }

        $this->assign( 'peerraiser', $view_args );

        // Render the view
        $this->render( 'backend/campaign-' . $view );
    }

    public function load_assets() {
        parent::load_assets();

        // Register and enqueue styles
        wp_register_style(
            'peerraiser-admin',
            Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin.css',
            array('peerraiser-font-awesome'),
            Setup::get_plugin_config()->get('version')
        );
        wp_register_style(
            'peerraiser-admin-campaigns',
            Setup::get_plugin_config()->get('css_url') . 'peerraiser-admin-campaigns.css',
            array('peerraiser-font-awesome', 'peerraiser-admin'),
            Setup::get_plugin_config()->get('version')
        );
        wp_enqueue_style( 'peerraiser-admin' );
        wp_enqueue_style( 'peerraiser-admin-campaigns' );
        wp_enqueue_style( 'peerraiser-font-awesome' );
        wp_enqueue_style( 'peerraiser-select2' );

        // Register and enqueue scripts
        wp_register_script(
            'peerraiser-admin-campaigns',
            Setup::get_plugin_config()->get('js_url') . 'peerraiser-admin-campaigns.js',
            array( 'jquery', 'peerraiser-admin' ),
            Setup::get_plugin_config()->get('version'),
            true
        );
        wp_enqueue_script( 'peerraiser-admin' ); // Already registered in Admin class
        wp_enqueue_script( 'peerraiser-admin-campaigns' );
        wp_enqueue_script( 'peerraiser-select2' );

        // Localize scripts
        wp_localize_script(
            'peerraiser-admin-campaigns',
            'peerraiser_object',
            array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri(),
                'timezone_string'    => get_option( 'timezone_string' ),
                'i18n'               => array(
                    'date'            => __( 'Date', 'peerraiser' ),
                    'time'            => __( 'Time', 'peerraiser' ),
                    'select_timezone' => __( 'Select a Timezone', 'peerraiser' ),
                )
            )
        );

    }

    public function register_meta_boxes() {
        $campaigns_model = new \PeerRaiser\Model\Admin\Campaigns_Admin();
        $campaign_field_groups = $campaigns_model->get_fields();
        foreach ($campaign_field_groups as $field_group) {
            $cmb = new_cmb2_box( array(
                'id'           => $field_group['id'],
                'title'        => $field_group['title'],
                'object_types' => array( 'post' ),
                'hookup'       => false,
                'save_fields'  => false,
            ) );
            foreach ($field_group['fields'] as $key => $value) {
                $cmb->add_field($value);
            }
         }
    }

    public function display_fundraisers_list() {
        global $post;
        $paged = isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1;

        $campaigns    = new \PeerRaiser\Model\Admin\Campaigns_Admin();
        $campaign_fundraisers = $campaigns->get_fundraisers( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_fundraisers,
            'paged'        => isset($_GET['fundraisers_page']) ? $_GET['fundraisers_page'] : 1,
            'paged_name'   => 'fundraisers_page'
        );
        $pagination = View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_fundraisers' => $campaign_fundraisers->found_posts,
            'pagination'            => $pagination,
            'currency_symbol'       => $currency_symbol,
            'fundraisers'           => $campaign_fundraisers->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-fundraisers' );
    }

    public function display_donations_list() {
        global $post;
        $paged = isset($_GET['donations_page']) ? $_GET['donations_page'] : 1;

        $campaigns          = new \PeerRaiser\Model\Admin\Campaigns_Admin();
        $campaign_donations = $campaigns->get_donations( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_donations,
            'paged'        => isset($_GET['donations_page']) ? $_GET['donations_page'] : 1,
            'paged_name'   => 'donations_page'
        );
        $pagination = View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_donations' => $campaign_donations->found_posts,
            'pagination'          => $pagination,
            'currency_symbol'     => $currency_symbol,
            'donations'           => $campaign_donations->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-donations' );
    }

    public function display_teams_list() {
        global $post;
        $paged = isset($_GET['teams_page']) ? $_GET['teams_page'] : 1;

        $campaigns      = new \PeerRaiser\Model\Admin\Campaigns_Admin();
        $campaign_teams = $campaigns->get_teams( $post->ID, $paged );

        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $args = array(
            'custom_query' => $campaign_teams,
            'paged'        => isset($_GET['teams_page']) ? $_GET['teams_page'] : 1,
            'paged_name'   => 'teams_page'
        );
        $pagination = View::get_admin_pagination( $args );

        $view_args = array(
            'number_of_teams' => $campaign_teams->found_posts,
            'pagination'      => $pagination,
            'currency_symbol' => $currency_symbol,
            'teams'           => $campaign_teams->get_posts(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-teams' );
    }

    public function display_campaign_stats( $post ) {
        $plugin_options  = get_option( 'peerraiser_options', array() );
        $currency        = new Currency();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);

        $end_date = get_post_meta( $post->ID, '_peerraiser_campaign_end_date', true );
        $goal = get_post_meta( $post->ID, '_peerraiser_campaign_goal', true );
        $days_left = 0;

        if ( !empty( $end_date ) ) {
            $today = time();
            $difference = $end_date - $today;
            $days_left = floor($difference/60/60/24);
        }

        $total_donations = Stats::get_total_donations_by_campaign( $post->ID );

        $view_args = array(
            'currency_symbol' => $currency_symbol,
            'has_goal' => ( $goal !== '0.00' ),
            'has_end_date' => !empty( $end_date ),
            'total_donations' => number_format_i18n( $total_donations, 2),
            'goal_percent' => ( !empty( $goal ) && $goal !== '0.00' ) ? number_format( ( $total_donations / $goal ) * 100, 2) : 0,
            'days_left' => ( $days_left < 0 ) ? __( 'Campaign Ended', 'peerraiser' ) : $days_left,
            'days_left_class' => ( $days_left < 0 ) ? 'negative' : 'positive',
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/partials/campaign-stats' );
    }

    /**
     * Handle "Add Campaign" form submission
     *
     * @since 1.0.0
     */
    public function handle_add_campaign() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_add_campaign_nonce' ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_campaign();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $campaign = new Campaign();

        $this->add_fields( $campaign );

        $campaign->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-campaigns',
            'view'               => 'summary',
            'campaign'           => $campaign->ID,
            'peerraiser_notice' => 'campaign_added',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donation
        wp_safe_redirect( $location );
    }

    public function handle_update_campaign() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_update_campaign_' . $_REQUEST['campaign_id'] ) ) {
            die( __( 'Security check failed.', 'peerraiser' ) );
        }

        $validation = $this->is_valid_campaign();
        if ( ! $validation['is_valid'] ) {
            return;
        }

        $campaign = new Campaign( $_REQUEST['campaign_id'] );

        $this->update_fields( $campaign );
        $campaign->save();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-campaigns',
            'view'               => 'summary',
            'campaign'           => $campaign->ID,
            'peerraiser_notice' => 'campaign_updated',
        ), admin_url( 'admin.php' ) );

        // Redirect to the edit screen for this new donation
        wp_safe_redirect( $location );
    }

    /**
     * Handle "delete campaign" action
     *
     * @since 1.0.0
     */
    public function delete_campaign() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'peerraiser_delete_campaign_' . $_REQUEST['campaign_id'] ) ) {
            die( __('Security check failed.', 'peerraiser' ) );
        }

        // Delete the campaign
        $campaign = new \PeerRaiser\Model\Campaign( $_REQUEST['campaign_id'] );

        $campaign->delete();

        // Create redirect URL
        $location = add_query_arg( array(
            'page'               => 'peerraiser-campaigns',
            'peerraiser_notice' => 'campaign_deleted',
        ), admin_url( 'admin.php' ) );

        wp_safe_redirect( $location );
    }

    /**
     * Maybe set cron for campaign end date
     *
     * Checks if the updated field is the end date, and handles setting the cron
     *
     * @param \PeerRaiser\Model\Campaign $campaign   Campaign object
     * @param string                     $meta_key   Meta key
     * @param string                     $meta_value Meta value
     */
    public function maybe_schedule_cron( $campaign, $meta_key, $meta_value ) {
        if ( $meta_key !== '_peerraiser_end_date_utc' || empty( $meta_value ) ) {
            return;
        }

        // Clear existing cron, if there is one
        wp_clear_scheduled_hook( 'peerraiser_end_campaign', array( 'campaign_id' =>  $campaign->ID ) );

        wp_schedule_single_event( $meta_value, 'peerraiser_end_campaign', array( 'campaign_id' =>  $campaign->ID ) );
    }

    /**
     * Maybe unset the cron for campaign end date
     *
     * Checks if the deleted field is the end date, and handles removing the cron
     *
     * @param \PeerRaiser\Model\Campaign $campaign Campaign object
     * @param string                     $meta_key Meta key
     */
    public function maybe_clear_cron( $campaign, $meta_key ) {
        if ( $meta_key !== '_peerraiser_end_date_utc') {
            return;
        }

        wp_clear_scheduled_hook( 'peerraiser_end_campaign', array( 'campaign_id' =>  $campaign->ID ) );
    }

    /**
     * Maybe Update the Date UTC
     *
     * @param \PeerRaiser\Model\Campaign $campaign   Campaign object
     * @param string                     $meta_key   Meta key
     * @param string                     $meta_value Meta value
     */
    public function maybe_update_date_utc( $campaign, $meta_key, $meta_value ) {
        $date_fields = array(
            '_peerraiser_start_date',
            '_peerraiser_start_time',
            '_peerraiser_end_date',
            '_peerraiser_end_time',
            '_peerraiser_timezone',
        );

        // If the updated field isn't one of the date fields, return
        if ( ! in_array( $meta_key, $date_fields ) ) {
            return;
        }

        // Update start_date_utc if the timezone or start date/time fields changed
        if ( $meta_key === '_peerraiser_timezone' || strpos( $meta_key, 'start') !== false ) {
            if ( empty( $campaign->start_date ) || empty( $campaign->start_time ) ) {
                return;
            }

            $timezone   = new DateTimeZone( $campaign->get_timezone_string() );
            $time       = new DateTime( $campaign->start_date . ' ' . $campaign->start_time, $timezone );
            $timestamp  = (int) $time->format('U');

            $campaign->start_date_utc = $timestamp;
        }

        // Update end_date_utc if the timezone or end date/time fields changed
        if ( $meta_key === '_peerraiser_timezone' || strpos( $meta_key, 'end') !== false ) {
            if ( empty( $campaign->end_date ) || empty( $campaign->end_time ) ) {
                return;
            }

            $timezone   = new DateTimeZone( $campaign->get_timezone_string() );
            $time       = new DateTime( $campaign->end_date . ' ' . $campaign->end_time, $timezone );
            $timestamp  = (int) $time->format('U');

            $campaign->end_date_utc = $timestamp;
        }

        $campaign->save();
    }

    /**
     * Maybe delete the End Date UTC meta if the deleted field is the end date
     *
     * @param \PeerRaiser\Model\Campaign $campaign Campaign object
     * @param string                     $meta_key Meta key
     */
    public function maybe_delete_end_date_utc( $campaign, $meta_key ) {
        if ( $meta_key !== '_peerraiser_end_date' ) {
            return;
        }

        $campaign->delete_meta( '_peerraiser_end_date_utc' );
    }

    /**
     * Set campaign status to 'ended' when the campaign ends
     *
     * @param $campaign_id
     */
    public function end_campaign( $campaign_id ) {
        $campaign = new Campaign( $campaign_id );

        if ( $campaign->campaign_status !== 'active' ) {
            return;
        }

        $campaign->campaign_status = 'ended';
        $campaign->save();
    }

    /**
     * Get fundraisers for a campaign via ajax
     */
    public function ajax_get_fundraisers() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax_get_fundraisers' ) ) {
            $data = array(
                'success'     => false,
                'fundraisers' => array(),
                'message'     => 'Security check failed'
            );

            echo Text::peerraiser_json_encode( $data );
            wp_die();
        }

        $campaign = peerraiser_get_campaign_by_slug( $_POST['campaign_slug'] );
        $fundraisers = $campaign->get_fundraisers();

        $fundraiser_info = array();

        foreach( $fundraisers as $fundraiser ) {
            $fundraiser_info[] = array(
                'name' => $fundraiser->fundraiser_name,
                'slug' => $fundraiser->fundraiser_slug,
            );
        }

        $data = array(
            'success'     => true,
            'fundraisers' => $fundraiser_info,
        );

        echo Text::peerraiser_json_encode( $data );

        wp_die();
    }

    /**
     * Checks if the fields are valid
     *
     * @since     1.0.0
     * @return    array    Array with 'is_valid' of TRUE or FALSE and 'field_errors' with any error messages
     */
    private function is_valid_campaign() {
        $campaigns_model = new \PeerRaiser\Model\Admin\Campaigns_Admin();
        $required_fields = $campaigns_model->get_required_field_ids();

        $data = array(
            'is_valid'     => true,
            'field_errors' => array(),
        );

        // If this is a new campaign, make sure campaign name isn't already taken
        if ( isset( $_REQUEST['peerraiser_action'] ) && 'add_campaign' === $_REQUEST['peerraiser_action'] ) {
            $campaign_exists = term_exists( $_REQUEST['_peerraiser_campaign_name'], 'peerraiser_campaign' );

            if ( $campaign_exists !== 0 && $campaign_exists !== null ) {
                $data['field_errors'][ '_peerraiser_campaign_name' ] = __( 'This campaign name already exists', 'peerraiser' );
            }
        }

        // Check required fields
        foreach ( $required_fields as $field ) {
            if ( ! isset( $_REQUEST[ $field ] ) || empty( $_REQUEST[ $field ] ) ) {
                $data['field_errors'][ $field ] = __( 'This field is required.', 'peerraiser' );
            }
        }

        // Check currency format
        $currency_fields = array(
            '_peerraiser_campaign_goal',
            '_peerraiser_suggested_individual_goal',
            '_peerraiser_suggested_team_goal',
        );

        foreach ( $currency_fields as $currency_field ) {
            if ( ! isset( $_REQUEST[ $currency_field ] ) ) {
                continue;
            }

            if ( ! \PeerRaiser\Helper\Text::is_currency( $_REQUEST[ $currency_field ] ) ) {
                $data['field_errors'][ $currency_field ] = __( 'Please use the valid currency format', 'peerraiser' );
            }
        }

        if ( ! empty( $data['field_errors'] ) ) {
            $message = __( 'There was an issue creating this campaign. Please fix the errors below.', 'peerraiser' );
            Admin_Notices_Model::add_notice( $message, 'notice-error', true );

            wp_localize_script(
                'jquery',
                'peerraiser_field_errors',
                $data['field_errors']
            );

            $data['is_valid'] = false;
        }

        return $data;
    }

    private function add_fields( $campaign) {
        $campaigns_model = new \PeerRaiser\Model\Admin\Campaigns_Admin();

        $field_ids   = $campaigns_model->get_field_ids();

        // Add campaign name and status to field list, since they're not a CMB2 fields
        $field_ids['campaign_name']   = '_peerraiser_campaign_name';
        $field_ids['campaign_status'] = '_peerraiser_campaign_status';

        // If the start date is empty, set it to today's date
        if ( ! isset( $_REQUEST['_peerraiser_start_date'] ) || empty( $_REQUEST['_peerraiser_start_date'] ) ) {
            $timezone = new DateTimeZone( $campaign->get_timezone_string() );
            $time     = new DateTime( '', $timezone );
            $_REQUEST['_peerraiser_start_date'] = $time->format( apply_filters( 'peerraiser_date_field_format', 'm/d/Y' ) );
        }

        // If the start time is empty, set it to the current time
        if ( ! isset( $_REQUEST['_peerraiser_start_time'] ) || empty( $_REQUEST['_peerraiser_start_time'] ) ) {
            $timezone = new DateTimeZone( $campaign->get_timezone_string() );
            $time     = new DateTime( '', $timezone );
            $_REQUEST['_peerraiser_start_time'] = $time->format( apply_filters( 'peerraiser_time_field_format', 'g:i a' ) );
        }

        // If the end date is empty, make sure the end time is also empty
        if ( empty( $_REQUEST['_peerraiser_end_date'] ) ) {
            $_REQUEST['_peerraiser_end_time'] = '';
            // If it's not empty, but the time is empty, set it to the current time
        } elseif ( empty( $_REQUEST['_peerraiser_end_time'] ) ) {
            $time = new DateTime();
            $_REQUEST['_peerraiser_end_time'] = $time->format( apply_filters( 'peerraiser_time_field_format', 'g:i a' ) );
        }

        foreach ( $field_ids as $key => $value ) {
            if ( isset( $_REQUEST[$value] ) ) {
                $campaign->$key = $_REQUEST[$value];
            }
        }
    }

    private function update_fields( $campaign ) {
        $campaigns_model = new \PeerRaiser\Model\Admin\Campaigns_Admin();

        $field_ids   = $campaigns_model->get_field_ids();

        // Add campaign status to field list, since its not a CMB2 field
        $field_ids['campaign_status'] = '_peerraiser_campaign_status';

        if ( ! empty( $_REQUEST['_peerraiser_campaign_name'] ) && $campaign->campaign_name !== $_REQUEST['_peerraiser_campaign_name'] ) {
            $slug = ( isset( $_REQUEST['slug'] ) && ! empty( $_REQUEST['slug'] ) ) ? $_REQUEST['slug'] : $campaign->campaign_slug;
            $campaign->update_campaign_name( $_REQUEST['_peerraiser_campaign_name'], $slug );
        } elseif ( isset( $_REQUEST['slug'] ) && ! empty( $_REQUEST['slug'] ) && $_REQUEST['slug'] !== $campaign->campaign_slug ) {
            $campaign->update_campaign_name( $campaign->campaign_name, $_REQUEST['slug'] );
        }

        // If the start date is empty, set it to today's date
        if ( ! isset( $_REQUEST['_peerraiser_start_date'] ) || empty( $_REQUEST['_peerraiser_start_date'] ) ) {
            $timezone = new DateTimeZone( $campaign->get_timezone_string() );
            $time     = new DateTime( '', $timezone );
            $_REQUEST['_peerraiser_start_date'] = $time->format( apply_filters( 'peerraiser_date_field_format', 'm/d/Y' ) );
        }

        // If the start time is empty, set it to the current time
        if ( ! isset( $_REQUEST['_peerraiser_start_time'] ) || empty( $_REQUEST['_peerraiser_start_time'] ) ) {
            $timezone = new DateTimeZone( $campaign->get_timezone_string() );
            $time     = new DateTime( '', $timezone );
            $_REQUEST['_peerraiser_start_time'] = $time->format( apply_filters( 'peerraiser_time_field_format', 'g:i a' ) );
        }

        // If the end date is empty, make sure the end time is also empty
        if ( empty( $_REQUEST['_peerraiser_end_date'] ) ) {
            $_REQUEST['_peerraiser_end_time'] = '';
        // If it's not empty, but the time is empty, set it to the current time
        } elseif ( empty( $_REQUEST['_peerraiser_end_time'] ) ) {
            $timezone = new DateTimeZone( $campaign->get_timezone_string() );
            $time     = new DateTime( '', $timezone );
            $_REQUEST['_peerraiser_end_time'] = $time->format( apply_filters( 'peerraiser_time_field_format', 'g:i a' ) );
        }

        // Get the current values from the database to see if things changes
        $current = $campaign->get_meta();

        foreach ( $field_ids as $key => $value ) {
            // Skip field if it isn't set
            if ( ! isset( $_REQUEST[$value] ) ) {
                continue;
            }

            // Delete field from database if its empty
            if ( trim( $_REQUEST[$value] ) === '' ) {
                if ( ! isset( $current[$value][0] ) ) {
                    continue;
                }

                $campaign->delete_meta($value);
                continue;
            }

            // Skip field if data didn't change
            if ( isset( $current[$value][0] ) && $_REQUEST[$value] === $current[$value][0] ) {
                continue;
            }

            // Update the data. It changed and isn't empty
            $campaign->$key = $_REQUEST[$value];
        }
    }

}
