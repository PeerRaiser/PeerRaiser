<?php
if ( ! defined( 'ABSPATH' ) ) {
    // Prevent direct access to this file
    exit;
}
?>

<?php
    $active_tab = $peerraiser['active_tab'];
    $active_section = $peerraiser['active_section'];
    $sections = $peerraiser['sections'][$active_tab];
    $number_of_sections = count($sections);
?>

<div class="wrap peerraiser peerraiser-settings">

    <h2><?php _e('PeerRaiser Settings', 'peerraiser') ?></h2>

    <h2 class="nav-tab-wrapper">
        <?php foreach( $peerraiser['tabs'] as $id => $value ) : ?>
        <a href="<?php echo esc_url( add_query_arg( array( 'tab' => $id ), admin_url( 'admin.php?page=peerraiser-settings' ) ) ) ?>" class="nav-tab<?php echo $active_tab == $id ? ' nav-tab-active' : '' ?>"><?= $value ?></a>
        <?php endforeach; ?>
    </h2>

    <?php if ( $number_of_sections > 1 ) : $count = 1; ?>
        <div class="group">
            <ul class="subsubsub">
                <?php foreach ($sections as $id => $value) : ?>
                    <li>
                        <?php $class = ( $active_section == $id ) ? 'current' : '' ?>
                        <a href="<?php echo esc_url( add_query_arg( array( 'tab' => $active_tab, 'section' => $id ), admin_url( 'admin.php?page=peerraiser-settings' ) ) ) ?>" class="<?= $class ?>"><?= $sections[$id]['name'] ?></a>
                        <?php if ( $count != $number_of_sections ) echo " | " ?>
                    </li>
                <?php $count++; endforeach; ?>
            </ul>
        </div>
    <?php endif;?>

    <h3><?= $peerraiser['content']['title'] ?></h3>

    <?= $peerraiser['content']['html'] ?>

</div>