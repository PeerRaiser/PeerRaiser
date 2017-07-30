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

    <form id="peerraiser-add-team" class="peerraiser-form" action="" method="post" data-object-id="<?php echo $peerraiser['team']->ID; ?>" data-object-type="team">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables">
                        <div id="submitdiv" class="postbox">
                            <h2><span><?php _e( 'Team Details', 'peerraiser' ); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions" class="team-info">
                                        <div class="misc-pub-section team-date">
                                            <span class="timestamp">
                                                <span class="label"><?php _e( 'Team Since', 'peerraiser' ); ?>:</span>
                                                <strong><?php echo mysql2date( "M j, Y", $peerraiser['team']->created, true ); ?></strong>
                                            </span>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <?php // todo: Check if user can delete teams ?>
                                        <?php //if ( current_user_can( 'delete_team', $peerraiser['team']->ID ) ) : ?>
                                            <div id="delete-action">
                                                <a class="submitdelete deletion" href="<?php echo add_query_arg( array( 'peerraiser_action' => 'delete_team', 'team_id' => $peerraiser['team']->ID, '_wpnonce' => wp_create_nonce( 'peerraiser_delete_team_' . $peerraiser['team']->ID ) ), admin_url( sprintf( 'admin.php?page=peerraiser-teams' ) ) ) ?>"><?php _e( 'Delete', 'peerraiser' ); ?></a>
                                            </div>
                                        <?php //endif; ?>
                                        <div id="publishing-action">
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Update', 'peerraiser'); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="postbox-container-2" class="postbox-container">
                    <div id="titlediv" class="peerraiser-title-div">
                        <div id="titlewrap">
                            <input type="text" name="_peerraiser_team_name" size="30" value="<?php echo $peerraiser['team']->team_name; ?>" id="title" spellcheck="true" autocomplete="off" placeholder="<?php _e( 'Enter team name here', 'peerraiser' ); ?>" data-rule-required="true" data-msg-required="<?php _e( 'Team Name is required', 'peerraiser' ); ?>">
                        </div>
                        <div class="inside">
                            <div id="edit-slug-box" class="hide-if-no-js" data-edit-slug-nonce="<?php echo wp_create_nonce( 'edit-slug-' . $peerraiser['team']->ID ); ?>">
                                <strong><?php _e('Permalink:'); ?></strong>

                                <span id="sample-permalink"><a href="<?php echo $peerraiser['team']->get_permalink; ?>"><?php echo $peerraiser['team']->get_display_link(); ?></a></span>
                                ‎<span id="edit-slug-buttons"><button type="button" class="edit-slug button button-small hide-if-no-js" aria-label="Edit permalink"><?php _e( 'Edit' ); ?></button></span>
                                <span id="editable-post-name-full"><?php echo $peerraiser['team']->team_slug; ?></span>
                                <input name="slug" type="hidden" id="slug" value="">
                            </div>
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
