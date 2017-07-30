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

    <div id="peerraiser-js-message" class="notice" style="display:none;">
        <p></p>
    </div>

    <?php do_action( 'peerraiser_before_settings_tabs', $peerraiser ); ?>

    <h2 class="nav-tab-wrapper">
        <?php foreach( $peerraiser['tabs'] as $id => $value ) : ?>
        <a href="<?php echo esc_url( add_query_arg( array( 'tab' => $id ), admin_url( 'admin.php?page=peerraiser-settings' ) ) ) ?>" class="nav-tab<?php echo $active_tab == $id ? ' nav-tab-active' : '' ?>"><?= $value ?></a>
        <?php endforeach; ?>
    </h2>

    <?php do_action( 'peerraiser_after_settings_tabs', $peerraiser ); ?>

    <?php if ( $number_of_sections > 1 ) : $count = 1; ?>
        <?php do_action( 'peerraiser_before_settings_sub_tabs', $peerraiser ); ?>

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

        <?php do_action( 'peerraiser_after_settings_sub_tabs', $peerraiser ); ?>
    <?php endif;?>

    <?php do_action( 'peerraiser_before_settings_title', $peerraiser ); ?>

    <h3><?= $peerraiser['content']['title'] ?></h3>

    <?php do_action( 'peerraiser_after_settings_title', $peerraiser ); ?>

    <?php do_action( 'peerraiser_before_settings_fields', $peerraiser ); ?>

    <?= $peerraiser['content']['html'] ?>

    <?php do_action( 'peerraiser_after_settings_fields', $peerraiser ); ?>

</div>