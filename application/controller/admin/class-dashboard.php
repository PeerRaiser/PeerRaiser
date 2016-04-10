<?php

namespace PeerRaiser\Controller\Admin;

class Dashboard  extends Base {

    /**
     * @see \PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'wp_ajax_peerraiser_dashboard' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_ajax_send_json', 300 ),
                array( 'process_ajax_requests' ),
                array( 'peerraiser_on_ajax_user_can_activate_plugins', 200 ),
            ),
            'wp_ajax_peerraiser_get_category_prices' => array(
                array( 'peerraiser_on_admin_view', 200 ),
                array( 'peerraiser_on_ajax_send_json', 300 ),
                array( 'process_ajax_requests' ),
                array( 'peerraiser_on_ajax_user_can_activate_plugins', 200 ),
            ),
        );
    }


    /**
     * @see \PeerRaiser\Core\View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_register_script(
            'peerraiser-select2',
            $this->config->get( 'js_url' ) . 'vendor/select2.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'peerraiser-backend-dashboard',
            $this->config->get( 'js_url' ) . 'peerraiser-backend-dashboard.js',
            array( 'jquery', 'peerraiser-select2' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script( 'peerraiser-select2' );
        wp_enqueue_script( 'peerraiser-backend-dashboard' );

        // translations
        $i18n = array(
            'total'                      => __( 'Total', 'peerraiser' ),
            'sales'                      => __( 'sales', 'peerraiser' ),
            'signups'                        => __( 'signups', 'peerraiser' ),
            'delete'                    => __( 'Delete', 'peerraiser' ),
        );

        // pass localized strings and variables to script
        // $time_passes_model  = new \PeerRaiser\Model\TimePass();

        $plugin_options = get_option( 'peerraiser_options', array() );

        wp_localize_script(
            'peerraiser-backend-dashboard',
            'lpVars',
            array(
                'locale'                => get_locale(),
                'i18n'                  => $i18n,
                'globalDefaultPrice'    => \PeerRaiser\Helper\View::format_number( get_option( 'peerraiser_global_price' ) ),
                'defaultCurrency'       => $plugin_options['currency'],
                'inCategoryLabel'       => __( 'All posts in category', 'peerraiser' ),
            )
        );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        // bulk price editor data
        $bulk_actions = array(
            'set'      => __( 'Set price of', 'peerraiser' ),
            'increase' => __( 'Increase price of', 'peerraiser' ),
            'reduce'   => __( 'Reduce price of', 'peerraiser' ),
            'free'     => __( 'Make free', 'peerraiser' ),
            'reset'    => __( 'Reset', 'peerraiser' ),
        );

        $bulk_selectors = array(
            'all'      => __( 'All posts', 'peerraiser' ),
        );

        $bulk_categories = get_categories();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $view_args = array(
            'top_nav'                               => $this->get_menu(),
            'admin_menu'                            => \PeerRaiser\Helper\View::get_admin_menu(),
            'standard_currency'                     => $plugin_options['currency'],
            'plugin_is_in_live_mode'                => $this->config->get( 'is_in_live_mode' ),
            'landing_page'                          => ( isset($plugin_options['landing_page']) ) ? $plugin_options['landing_page'] : '',
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/dashboard' );
    }


    /**
     * Process Ajax requests from dashboard tab.
     *
     * @param PeerRaiser\Core\Event $event
     * @throws PeerRaiser\Core\Exception\Invalid_Incoming_Data
     *
     * @return void
     */
    public function process_ajax_requests( \PeerRaiser\Core\Event $event ) {
        $event->set_result(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'peerraiser' ),
            )
        );

        if ( ! isset( $_POST['form'] ) ) {
            // invalid request
            throw new \PeerRaiser\Core\Exception\InvalidIncomingData( 'form' );
        }

        // save changes in submitted form
        switch ( sanitize_text_field( $_POST['form'] ) ) {
            case 'global_price_form':
                $this->update_global_default_price( $event );
                break;

            case 'price_category_form':
                $this->set_category_default_price( $event );
                break;

            case 'price_category_form_delete':
                $this->delete_category_default_price( $event );
                break;

            case 'peerraiser_get_category_prices':
                if ( ! isset( $_POST['category_ids'] ) || ! is_array( $_POST['category_ids'] ) ) {
                    $_POST['category_ids'] = array();
                }
                $categories = array_map( 'sanitize_text_field', $_POST['category_ids'] );
                $event->set_result( array(
                    'success' => true,
                    'prices'  => $this->get_category_prices( $categories ),
                ));
                break;

            case 'bulk_price_form':
                $this->change_posts_price( $event );
                break;

            case 'bulk_price_form_save':
                $this->save_bulk_operation( $event );
                break;

            case 'bulk_price_form_delete':
                $this->delete_bulk_operation( $event );
                break;

            case 'time_pass_form_save':
                $this->time_pass_save( $event );
                break;

            case 'time_pass_delete':
                $this->time_pass_delete( $event );
                break;

            case 'generate_voucher_code':
                $this->generate_voucher_code( $event );
                break;

            case 'save_landing_page':
                $this->save_landing_page( $event );
                break;

            case 'peerraiser_get_categories_with_price':
                if ( ! isset( $_POST['term'] ) ) {
                    throw new PeerRaiser\Core\Exception\Invalid_Incoming_Data( 'term' );
                }

                // return categories that match a given search term
                $category_price_model = new \PeerRaiser\Model\Category_Price();
                $args = array();

                if ( ! empty( $_POST['term'] ) ) {
                    $args['name__like'] = sanitize_text_field( $_POST['term'] );
                }

                $event->set_result( array(
                    'success'    => true,
                    'categories' => $category_price_model->get_categories_without_price_by_term( $args ),
                ));
                break;

            case 'peerraiser_get_categories':
                // return categories
                $args = array(
                    'hide_empty' => false,
                );

                if ( isset( $_POST['term'] ) && ! empty( $_POST['term'] ) ) {
                    $args['name__like'] = sanitize_text_field( $_POST['term'] );
                }

                $event->set_result( array(
                    'success'    => true,
                    'categories' => get_categories( $args ),
                ));
                break;

            case 'change_purchase_mode_form':
                $this->change_purchase_mode( $event );
                break;

            default:
                break;
        }
    }

    /**
     * Save landing page URL the user is forwarded to after redeeming a gift card voucher.
     *
     * @param \PeerRaiser\Core\Event $event
     * @throws \PeerRaiser\Core\Exception\Form_Validation
     *
     * @return void
     */
    private function save_landing_page( PeerRaiser\Core_Event $event ) {
        $landing_page_form  = new \PeerRaiser\Form_LandingPage( $_POST );

        if ( ! $landing_page_form->is_valid() ) {
            throw new \PeerRaiser\Core\Exception\Form_Validation( get_class( $landing_page_form ), $landing_page_form->get_errors() );
        }

        // save URL and confirm with flash message, if the URL is valid
        $plugin_options = get_option( 'peerraiser_options', array() );
        $plugin_options['landing_page'] = $landing_page_form->get_field_value( 'landing_url' );
        update_option( 'peerraiser_options', $plugin_options );

        $event->set_result(
            array(
                'success' => true,
                'message' => __( 'Landing page saved.', 'peerraiser' ),
            )
        );
    }

}
