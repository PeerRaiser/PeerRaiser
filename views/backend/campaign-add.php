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

    <h1 class="wp-heading-inline"><?php _e( 'Add New Campaign', 'peerraiser' ); ?></h1>
    <hr class="wp-header-end">

    <form id="peerraiser-add-campaign" class="peerraiser-form" action="" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables">
                        <div id="submitdiv" class="postbox">
                            <h2><span>Publish</span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div class="misc-pub-section campaign-status active">
		                                <?php _e( 'Status:', 'peerraiser' ); ?> <strong><?php _e( 'Active', 'peerraiser' ); ?></strong>
                                        <a href="#campaign_status" class="edit-campaign-status hide-if-no-js" role="button"><span aria-hidden="true"><?php _e( 'Edit', 'peerraiser') ?></span> <span class="screen-reader-text"><?php _e( 'Edit status', 'peerraiser' ); ?></span></a>
                                        <div id="campaign-status-select" class="hide-if-js">
                                            <input type="hidden" name="_peerraiser_campaign_status_hidden" value="active">
                                            <select name="_peerraiser_campaign_status" id="campaign-status">
		                                        <?php foreach ( $peerraiser['campaign_admin']->get_campaign_statuses() as $key => $value ) : ?>
                                                    <option value="<?php echo $key ?>"><?php echo $value ?></option>
		                                        <?php endforeach; ?>
                                            </select>
                                            <a href="#campaign_status" class="save hide-if-no-js button"><?php _e( 'OK', 'peerraiser' ); ?></a>
                                            <a href="#campaign_status" class="cancel hide-if-no-js button-cancel"><?php _e( 'Cancel', 'peerraiser' ); ?></a>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
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
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" name="_peerraiser_campaign_name" size="30" value="" id="title" spellcheck="true" autocomplete="off" placeholder="<?php _e( 'Enter campaign name here', 'peerraiser' ); ?>" data-rule-required="true" data-msg-required="<?php _e( 'Campaign Name is required', 'peerraiser' ); ?>">
                        </div>
                        <div class="inside">
                            <div id="edit-slug-box" class="hide-if-no-js"></div>
                        </div>
                    </div>

                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <div id="campaign-options" class="postbox cmb2-postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e( 'Campaign Options', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-campaign', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>
                    </div>

                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <div id="fundraising-options" class="postbox cmb2-postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e( 'Fundraising Page Options', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
				                <?php echo cmb2_get_metabox_form( 'peerraiser-campaign-fundraiser-options', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>
                    </div>

                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <div id="team-options" class="postbox cmb2-postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e( 'Team Options', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
				                <?php echo cmb2_get_metabox_form( 'peerraiser-campaign-team-options', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>
                    </div>

					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<div id="donation-form-options" class="postbox cmb2-postbox">
							<h2 class="hndle ui-sortable-handle"><span><?php _e( 'Donation Form', 'peerraiser' ); ?></span></h2>
							<div class="inside">
								<?php echo cmb2_get_metabox_form( 'peerraiser-campaign-donation-form', 0, array( 'form_format' => '', ) ); ?>
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
