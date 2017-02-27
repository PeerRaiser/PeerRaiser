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

    <form id="peerraiser-add-campaign" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables">
                        <div id="submitdiv" class="postbox">
                            <h2><span>Publish</span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section">
                                            <input type="checkbox" id="is-public" value="is_public" checked> <label for="is-public">Display publically</label>
                                        </div>
                                    </div>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a class="submitdelete deletion" href="http://localhost/wordpress/wp-admin/post.php?post=1080&amp;action=trash&amp;_wpnonce=f77a7b0df6">Move to Trash</a>
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
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label class="" id="title-prompt-text" for="title"><?php _e( 'Enter campaign name here', 'peerraiser' ); ?></label>
                            <input type="text" name="campaign_title" size="30" value="" id="title" spellcheck="true" autocomplete="off">
                        </div>
                        <div class="inside">
                            <div id="edit-slug-box" class="hide-if-no-js"></div>
                        </div>
                    </div>

                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                        <div id="campaign-options" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span>Campaign Options</span></h2>
                            <div class="inside">
                                <?php echo cmb2_get_metabox_form( 'peerraiser-campaign', 0, array( 'form_format' => '', ) ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'peerraiser_add_campaign_nonce' ); ?>
        <input type="hidden" name="action" value="peerraiser_add_campaign">
    </form>
</div>