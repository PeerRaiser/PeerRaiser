<?php

namespace PeerRaiser\Controller\Frontend;

class Participant_Dashboard extends \PeerRaiser\Controller\Base {

    public static function get_subscribed_events() {
        return array(
            'wp_ajax_peerraiser_update_avatar' => array(
                array( 'ajax_update_avatar', 100 ),
                array( 'peerraiser_on_plugin_is_working', 200 ),
                array( 'peerraiser_on_ajax_send_json', 300 ),
            ),
        );
    }

    public function ajax_update_avatar( \PeerRaiser\Core\Event $event ) {
        $user_id = get_current_user_id();

        $avatar_id = get_user_meta( $user_id, '_peerraiser_custom_avatar', true );

        /* If the avatar has been set previously, remove it from the media library.
         * This prevents people from spamming the it.
         */
        if ( $avatar_id ==! "" ) {
            wp_delete_attachment( $avatar_id, false );
        }

        $attachment_id = media_handle_sideload( $_FILES['files'], 0 );
        update_user_meta( $user_id, '_peerraiser_custom_avatar', $attachment_id );

        $event->set_result(
            array(
                'avatar_id' => $attachment_id,
                'success'   => true,
                'image_url' => wp_get_attachment_image_url( $attachment_id, 'peerraiser_campaign_thumbnail' )
            )
        );
    }

}