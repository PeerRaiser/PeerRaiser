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

    <h1 class="wp-heading-inline"><?php _e( 'Add Team', 'peerraiser' ); ?></h1>
    <hr class="wp-header-end">

    <form id="peerraiser-add-team" class="peerraiser-form" action="" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables peerraiser-metabox">

                        <?php do_action( 'peerraiser_before_team_side_metaboxes' ); ?>

                        <div id="submitdiv" class="postbox">
                            <h2><span><?php _e( 'Add Team', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section team-date completed">
                                            <?php _e( 'Team Since', 'peerraiser' ); ?>: <strong><?php echo date(get_option('date_format')); ?></strong>
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

                        <?php do_action( 'peerraiser_after_team_side_metaboxes' ); ?>
                    </div> <!-- / #side-sortables -->
                </div>
                <div id="postbox-container-2" class="postbox-container peerraiser-metabox">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" name="_peerraiser_team_name" size="30" value="" id="title" spellcheck="true" autocomplete="off" placeholder="<?php _e( 'Enter team name here', 'peerraiser' ); ?>">
                        </div>
                        <div class="inside">
                            <div id="edit-slug-box" class="hide-if-no-js"></div>
                        </div>
                    </div>

                    <div id="normal-sortables">
                        <?php do_action( 'peerraiser_before_team_metaboxes' ); ?>

                        <div id="team-options" class="postbox cmb2-postbox">
                            <h2><span><?php _e( 'Team Options', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-team', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>

                        <?php do_action( 'peerraiser_after_team_metaboxes' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_add_team_nonce' ); ?>
        <input type="hidden" name="peerraiser_action" value="add_team">
    </form>
</div>
