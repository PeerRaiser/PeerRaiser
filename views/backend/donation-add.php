<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>
<div class="wrap">
    <div id="peerraiser-js-message" class="pr_flash-message" style="display:none;">
        <p></p>
    </div>
    <?php \PeerRaiser\Controller\Admin\Admin_Notices::display_notices(); ?>

    <h1 class="wp-heading-inline"><?php _e( 'Add Offline Donation', 'peerraiser' ); ?></h1>
    <hr class="wp-header-end">

    <form id="peerraiser-add-campaign" action="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables">
                        <div id="submitdiv" class="postbox">
                            <h2><span>Add Donation</span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section">
                                            <div class="misc-pub-section">
                                                <label for="is-public"><?php _e( 'Donation Status', 'peerraiser' ); ?></label>
                                                <select name="donation_status" id="donation-status">
                                                    <option value="Completed">Completed</option>
                                                    <option value="Pending">Pending</option>
                                                </select>
                                            </div>

                                            <div class="misc-pub-section">
                                                <label for="is-public"><?php _e( 'Donation Type', 'peerraiser' ); ?></label>
                                                <select name="donation_status" id="donation-status">
                                                    <option value="cc">Credit Card</option>
                                                    <option value="check">Check</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" href="http://localhost/wordpress/wp-admin/post.php?post=1080&amp;action=trash&amp;_wpnonce=f77a7b0df6">Cancel</a>
                                        </div>
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input name="original_publish" type="hidden" id="original_publish" value="Publish">
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Publish">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="postbox-container-2" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <div id="campaign-options" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span>Donation Options</span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-offline-donation', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_add_campaign_nonce' ); ?>
        <input type="hidden" name="peerraiser_action" value="add_campaign">
    </form>
</div>