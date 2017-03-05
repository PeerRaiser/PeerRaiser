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

    <h1 class="wp-heading-inline"><?php _e( 'Add Offline Donation', 'peerraiser' ); ?></h1>
    <hr class="wp-header-end">

    <form id="peerraiser-add-campaign" class="peerraiser-form" action="" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables peerraiser-metabox">

                        <?php do_action( 'peerraiser_before_donation_side_metaboxes' ); ?>

                        <div id="submitdiv" class="postbox">
                            <h2><span><?php _e( 'Add Donation', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section donation-status completed">
                                            <?php _e( 'Status:', 'peerraiser' ); ?> <strong>Completed</strong>
                                            <a href="#donation_status" class="edit-donation-status hide-if-no-js" role="button"><span aria-hidden="true">Edit</span> <span class="screen-reader-text"><?php _e( 'Edit status', 'peerraiser' ); ?></span></a>
                                            <div id="donation-status-select" class="hide-if-js">
                                                <input type="hidden" name="_donation_status_hidden" value="completed">
                                                <select name="_donation_status" id="donation-status">
                                                    <option value="completed">Completed</option>
                                                    <option value="pending">Pending</option>
                                                </select>
                                                <a href="#donation_status" class="save hide-if-no-js button"><?php _e( 'OK', 'peerraiser' ); ?></a>
                                                <a href="#donation_status" class="cancel hide-if-no-js button-cancel"><?php _e( 'Cancel', 'peerraiser' ); ?></a>
                                            </div>
                                        </div>

                                        <div class="misc-pub-section donation-type">
                                            <?php _e( 'Type:', 'peerraiser' ); ?> <strong>Check</strong>
                                            <a href="#donation_type" class="edit-donation-type hide-if-no-js" role="button"><span aria-hidden="true">Edit</span> <span class="screen-reader-text"><?php _e( 'Edit type', 'peerraiser' ); ?></span></a>
                                            <div id="donation-type-select" class="hide-if-js">
                                                <input type="hidden" name="_donation_type_hidden" value="check">
                                                <select name="_donation_type" id="donation-type">
                                                    <option value="check">Check</option>
                                                    <option value="cc">Credit Card</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="other">Other</option>
                                                </select>
                                                <a href="#donation_type" class="save hide-if-no-js button"><?php _e( 'OK', 'peerraiser' ); ?></a>
                                                <a href="#donation_type" class="cancel hide-if-no-js button-cancel"><?php _e( 'Cancel', 'peerraiser' ); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" href="http://localhost/wordpress/wp-admin/post.php?post=1080&amp;action=trash&amp;_wpnonce=f77a7b0df6"><?php _e( 'Cancel', 'peerraiser' ); ?></a>
                                        </div>
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Submit', 'peerraiser' ); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_donation_side_metaboxes' ); ?>
                    </div> <!-- / #side-sortables -->
                </div>
                <?php do_action( 'peerraiser_before_donation_metaboxes' ); ?>
                <div id="postbox-container-2" class="postbox-container peerraiser-metabox">
                    <div id="normal-sortables">
                        <div id="campaign-options" class="postbox">
                            <h2><span><?php _e( 'Donation Options', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-offline-donation', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php do_action( 'peerraiser_after_donation_metaboxes' ); ?>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_add_donation_nonce' ); ?>
        <input type="hidden" name="peerraiser_action" value="add_donation">
    </form>
</div>