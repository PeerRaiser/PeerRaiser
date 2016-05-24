<div class="submitbox" id="submitpost">
    <div id="donation-actions">
        <div class="donation-info">
            <div class="donation-date">
                <span class="label">Date/Time</span>
                <strong>April 22, 2016</strong>
            </div>
            <div class="donation-method">
                <span class="label">Payment Method</span>
                <select name="_payment_method">
                    <?php $option_values = array('Offline', 'Check', 'Cash', 'Other'); ?>
                        <?php foreach ($option_values as $key => $value) : ?>
                            <option><?php echo $value; ?></option>
                        <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php if ( $peerraiser['can_publish'] ) : ?>
            <div id="major-publishing-actions">
                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Add Tab') ?>" />
                    <?php submit_button( __( 'Add Donation' ), 'pr-add-donation primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
                <div class="clear"></div>
            </div>
        <?php endif; ?>
    </div>
</div>