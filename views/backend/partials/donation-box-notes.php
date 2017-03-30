<div id="donation-options" class="postbox">
    <h2><span><?php _e( 'Donation Notes', 'peerraiser' ); ?></span></h2>
    <div class="inside">
        <textarea name="_peerraiser_donation_note" id="donation-note" class="large-text" rows="5"></textarea>

        <?php if ( ! empty( $peerraiser['donation']->notes ) ) : ?>
            <div id="donation-notes">
                <?php foreach ( $peerraiser['donation']->notes as $donation_note ) : ?>
                    <p>On <strong><?php echo $donation_note['time']; ?></strong>:</p>
                    <p><?php echo wpautop( $donation_note['note'] ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>