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

    <h1 class="wp-heading-inline"><?php _e( 'Campaigns', 'peerraiser' ); ?></h1>

    <?php
    $peerraiser['list_table']->prepare_items();
    $peerraiser['list_table']->display();
    ?>
</div>