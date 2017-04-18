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

	<h1 class="wp-heading-inline"><?php _e( 'Edit Team', 'peerraiser' ); ?></h1>
	<a href="<?php echo admin_url( 'admin.php?page=peerraiser-teams&view=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'peerraiser' ); ?></a>
	<hr class="wp-header-end">

	<form id="peerraiser-add-team" class="peerraiser-form" action="" method="post">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables">
						<div id="submitdiv" class="postbox">
							<h2><span><?php _e( 'Publish', 'peerraiser' ); ?></span></h2>
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
										<div id="delete-action">
											<?php // todo: Make this work: ?>
											<a class="submitdelete deletion" href="http://localhost/wordpress/wp-admin/post.php?post=1080&amp;action=trash&amp;_wpnonce=f77a7b0df6"><?php _e( 'Delete', 'peerraiser' ); ?></a>
										</div>
										<div id="publishing-action">
											<span class="spinner"></span>
											<input name="original_publish" type="hidden" id="original_publish" value="<?php _e( 'Publish', 'peerraiser'); ?>">
											<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Publish', 'peerraiser'); ?>">
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
							<input type="text" name="_peerraiser_team_name" size="30" value="<?php echo $peerraiser['team']->team_name; ?>" id="title" spellcheck="true" autocomplete="off" placeholder="<?php _e( 'Enter team name here', 'peerraiser' ); ?>" data-rule-required="true" data-msg-required="<?php _e( 'Team Name is required', 'peerraiser' ); ?>">
						</div>
						<div class="inside">
							<div id="edit-slug-box" class="hide-if-no-js"></div>
						</div>
					</div>

					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<div id="team-options" class="postbox cmb2-postbox">
							<h2 class="hndle ui-sortable-handle"><span><?php _e( 'Team Options', 'peerraiser' ); ?></span></h2>
							<div class="inside">
								<?php echo cmb2_get_metabox_form( 'peerraiser-team', 0, array( 'form_format' => '', ) ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php wp_nonce_field( 'peerraiser_update_team_' . $peerraiser['team']->ID ); ?>
		<input type="hidden" name="team_id" value="<?php echo $peerraiser['team']->ID; ?>">
		<input type="hidden" name="peerraiser_action" value="update_team">
	</form>
</div>
