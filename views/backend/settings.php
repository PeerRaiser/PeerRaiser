<?php
if ( ! defined( 'ABSPATH' ) ) {
    // Prevent direct access to this file
    exit;
}
?>

<div class="wrap peerraiser peerraiser-settings">

    <h2><?php _e('PeerRaiser Settings', 'peerraiser') ?></h2>

    <h2 class="nav-tab-wrapper">
        <?php foreach( $peerraiser['tabs'] as $id => $name ) : ?>
        <a href="<?php echo esc_url( add_query_arg( array( 'tab' => $id ), admin_url( 'admin.php?page=peerraiser-settings' ) ) ) ?>" class="nav-tab<?php echo $peerraiser['active_tab'] == $id ? ' nav-tab-active' : '' ?>"><?= $name ?></a>
        <?php endforeach; ?>
    </h2>

    <?= $peerraiser['fields'] ?>

</div>