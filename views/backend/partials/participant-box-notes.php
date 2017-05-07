<div id="participant-options" class="postbox">
    <h2><span><?php _e( 'Participant Notes', 'peerraiser' ); ?></span></h2>
    <div class="inside">
        <textarea name="participant_note" id="participant-note" class="large-text" rows="5"></textarea>

        <?php if ( ! empty( $peerraiser['participant']->notes ) ) : ?>
            <div id="participant-notes">
                <?php $date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ?>
                <?php foreach ( $peerraiser['participant']->notes as $participant_note ) : ?>
                    <p><strong><?php printf( esc_html__( '%1$s on %2$s', 'peerraiser' ), $participant_note['who'], mysql2date( $date_format, $participant_note['when'] ) ); ?></strong>:</p>

                    <p><?php echo wpautop( $participant_note['what'] ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>