<div class="submitbox" id="submitpost">
    <div id="donor-stats">
        <ul class="donor-info">
            <li><strong><?php _e('Lifetime Donations', 'peerraiser') ?></strong> <br><span class="badge"><?= $peerraiser['lifetime_donations'] ?></span></li>
            <li><strong><?php _e('Largest Donation', 'peerraiser') ?></strong> <br><span class="badge"><?= $peerraiser['largest_donation'] ?></span></li>
            <li><strong><?php _e('Latest Donation', 'peerraiser') ?></strong> <br><span class="badge"><?= $peerraiser['latest_donation'] ?></span></li>
            <li><strong><?php _e('First Donation', 'peerraiser') ?></strong> <br><span class="badge"><?= $peerraiser['first_donation'] ?></span></li>
        </ul>
        <?php if ( $peerraiser['can_publish'] ) : ?>
            <div id="major-publishing-actions">
                <div id="delete-action">
                    <a href="<?= get_delete_post_link( $peerraiser['object']->ID ) ?>" class="submitdelete pr-delete-donor pr-delete"><?php _e('Delete Donor', 'peerraiser') ?></a>
                </div>
                <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update Donor'); ?>" />
                <?php submit_button( __( 'Update' ), 'pr-update-donor primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
                <div class="clear"></div>
            </div>
        <?php endif; ?>
    </div>
</div>