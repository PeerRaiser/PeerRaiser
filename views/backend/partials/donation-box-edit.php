<div class="submitbox" id="submitpost">
    <div id="donation-actions">
        <div class="donation-info">
            <div class="donation-date">
                <span class="label">Date/Time</span>
                <strong>April 22, 2016</strong>
            </div>
            <div class="donation-method">
                <span class="label">Payment Method</span>
                <strong>Credit Card</strong>
            </div>
            <div class="donation-key">
                <span class="label">Transaction Key</span>
                <strong>4212b56b0cc17b6fdfe936da2b125638</strong>
            </div>
            <div class="donor-ip">
                <span class="label">IP Address</span>
                <strong>53.61.173.12</strong>
            </div>
            <div class="is-test-mode">
                <span class="label">Test mode?</span>
                <strong>No</strong>
            </div>
        </div>
        <?php if ( $peerraiser['can_publish'] ) : ?>
            <div id="major-publishing-actions">
                <div id="delete-action">
                    <a href="<?= get_delete_post_link( $peerraiser['object']->ID ) ?>" class="submitdelete pr-delete-donation pr-delete">Delete Donation</a>
                </div>
                <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update Donation'); ?>" />
                <?php submit_button( __( 'Update Donation' ), 'pr-update-donation primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
                <div class="clear"></div>
            </div>
        <?php endif; ?>
    </div>
</div>