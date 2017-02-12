<?php

namespace PeerRaiser\Controller\Frontend;

class Listener extends \PeerRaiser\Controller\Base {

    private $listener = 'peerraiserListener';

    private $listener_var = 'notification';

    private $allowed_actions = array( 'new_donation' );

    /**
     * @see \PeerRaiser\Core\Event\SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'peerraiser_query_vars' => array(
                array( 'add_peerraiser_listener' ),
            ),
            'peerraiser_template_redirect' => array(
                array( 'listen_for_notification' ),
            ),
        );
    }


    public function add_peerraiser_listener( \PeerRaiser\Core\Event $event ) {
        list( $qvars ) = $event->get_arguments();
        $qvars[] = $this->listener;
        // TODO: Delete this:
        $qvars[] = 'testListener';
        $event->set_result( $qvars );
    }


    /**
     * Listen for notification from PeerRaiser.com and handle the notifcation if
     * it's received.
     */
    public function listen_for_notification( \PeerRaiser\Core\Event $event ) {
        if ( $this->listener_var == get_query_var( $this->listener ) ) {
            $this->handle_notification();
        } elseif ( $this->listener_var == get_query_var( 'testListener' ) ) {
            $_GET = stripslashes_deep($_GET);
            $this->process_message( 'new_donation', $_GET );
            exit;
        }
    }


    /**
     * Try to validate the message and then process it
     */
    public function handle_notification() {
        $_POST = stripslashes_deep($_POST);
        if ($this->validate_message()) {
            $this->process_message();
        }
        exit;
    }


    /**
     * Validate the message by checking with PeerRaiser.com to make that's where it
     * actually came from
     */
    private function validate_message() {
        // Set the command that is used to validate the message
        $_POST['cmd'] = "notify-validate";

        // We need to send the message back to PeerRaiser just as we received it
        $params = array(
            'body'    => $_POST,
            'timeout' => 30,
        );

        $config = \PeerRaiser\Core\Setup::get_plugin_config();
        $peerraiser_url = ( $config->get('in_live_mode') ) ? $config->get( 'peerraiser_url.live' ) : $config->get( 'peerraiser_url.sandbox' );

        // Send the request
        $response = wp_remote_post( $peerraiser_url, $params );

        // Put the $_POST data back to how it was
        unset( $_POST['cmd'] );

        // Setup debug message
        $message = __( 'URL:', 'peerraiser' );
        $message .= "\r\n".print_r($peerraiser_url, true)."\r\n\r\n";
        $message .= __( 'Response:', 'peerraiser' );
        $message .= "\r\n".print_r($resp, true)."\r\n\r\n";
        $message .= __( 'Post:', 'peerraiser' );
        $message .= "\r\n".print_r($_POST, true);

        // Check to see if the request was valid
        if ( !is_wp_error($response) && (strcmp( $response['body'], "VERIFIED") == 0) ) {
            \PeerRaiser\Helper\Debug::debug_email( __( 'Notification Listener Test Succeeded', 'peerraiser' ), $message );
            return true;
        } else {
            \PeerRaiser\Helper\Debug::debug_email( __( 'Notification Listener Test Failed', 'peerraiser' ), $message );
            return false;
        }

    }


    private function process_message( $action, $data ){

        if ( !in_array($action, $this->allowed_actions) )
            return false;

        $event = new \PeerRaiser\Core\Event();
        $event->set_echo( false );
        $event->set_arguments( $data );

        $dispatcher = \PeerRaiser\Core\Event\Dispatcher::get_dispatcher();
        $dispatcher->dispatch( 'peerraiser_'.$action, $event );

        exit;

    }

}