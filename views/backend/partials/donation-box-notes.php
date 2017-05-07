<div id="donation-options" class="postbox">
    <h2><span><?php _e( 'Donation Notes', 'peerraiser' ); ?></span></h2>
    <div class="inside">
        <textarea name="donation_note" id="donation-note" class="large-text" rows="5"></textarea>

        <?php if ( ! empty( $peerraiser['donation']->notes ) ) : ?>
            <div id="donation-notes">
                <?php $date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ?>
                <?php foreach ( $peerraiser['donation']->notes as $donation_note ) : ?>
                    <p><strong><?php printf( esc_html__( '%1$s on %2$s', 'peerraiser' ), $donation_note['who'], mysql2date( $date_format, $donation_note['when'] ) ); ?></strong>:</p>

                    <p><?php echo wpautop( $donation_note['what'] ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>