<div id="donor-options" class="postbox">
    <h2><span><?php _e( 'Donor Notes', 'peerraiser' ); ?></span></h2>
    <div class="inside">
        <textarea name="donor_note" id="donor-note" class="large-text" rows="5"></textarea>

        <?php if ( ! empty( $peerraiser['donor']->notes ) ) : ?>
            <div id="donor-notes">
                <?php $date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ?>
                <?php foreach ( $peerraiser['donor']->notes as $donor_note ) : ?>
                    <p><strong><?php printf( esc_html__( '%1$s on %2$s', 'peerraiser' ), $donor_note['who'], mysql2date( $date_format, $donor_note['when'] ) ); ?></strong>:</p>

                    <p><?php echo wpautop( $donor_note['what'] ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>