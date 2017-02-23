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

    <form id="peerraiser-add-campaign" action="post">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    sidebar
                </div>
                <div id="postbox-container-2" class="postbox-container">
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
    </form>
</div>