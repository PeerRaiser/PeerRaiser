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

    <h1 class="wp-heading-inline"><?php _e( 'Add Participant', 'peerraiser' ); ?></h1>
    <hr class="wp-header-end">

    <form id="peerraiser-add-participant" class="peerraiser-form" action="" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables peerraiser-metabox">

                        <?php do_action( 'peerraiser_before_participant_side_metaboxes' ); ?>

                        <div id="submitdiv" class="postbox">
                            <h2><span><?php _e( 'Add Participant', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section participant-date">
                                            <?php _e( 'Participant Since', 'peerraiser' ); ?>: <strong><?php echo current_time(get_option('date_format')); ?></strong>
                                            <!-- <a href="#donation_status" class="edit-donation-status hide-if-no-js" role="button"><span aria-hidden="true">Edit</span> <span class="screen-reader-text">Edit status</span></a> -->
                                            <div id="donation-status-select" class="hide-if-js">
                                                <input type="hidden" name="_donation_status_hidden" value="completed">
                                                <select name="_donation_status" id="donation-status">
                                                    <option value="completed">Completed</option>
                                                    <option value="pending">Pending</option>
                                                </select>
                                                <a href="#donation_status" class="save hide-if-no-js button">OK</a>
                                                <a href="#donation_status" class="cancel hide-if-no-js button-cancel">Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Submit', 'peerraiser' ); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_participant_side_metaboxes' ); ?>
                    </div> <!-- / #side-sortables -->
                </div>
                <div id="postbox-container-2" class="postbox-container peerraiser-metabox">
                    <div id="normal-sortables">
                        <?php do_action( 'peerraiser_before_participant_metaboxes' ); ?>

                        <div id="participant-options" class="postbox cmb2-postbox">
                            <h2><span><?php _e( 'Participant Options', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-participant-info', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>

                        <div id="account-info" class="postbox cmb2-postbox">
                            <h2><span><?php _e( 'Account Info', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-account-info', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_participant_metaboxes' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_add_participant_nonce' ); ?>
        <input type="hidden" name="peerraiser_action" value="add_participant">
    </form>
</div>
