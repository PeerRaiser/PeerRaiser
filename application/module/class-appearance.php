<?php

namespace PeerRaiser\Module;

class Appearance extends \PeerRaiser\Core\View {

    public function register_actions() {
        add_action( 'peerraiser_on_ajax_send_json', array( $this, 'on_ajax_send_json' ) );
    }

    /**
     * Set type as JSON
     *
     * @param PeerRaiser_Core_Event $event
     */
    public function on_ajax_send_json() {
        $event->set_type( \PeerRaiser\Core\Event::TYPE_JSON );
    }

}