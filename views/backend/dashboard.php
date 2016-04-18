<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<div class="pr_page wp-core-ui">

    <div id="pr_js_flashMessage" class="pr_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="wrap peerraiser-wrap dashboard-wrap">

        <h1><strong>PeerRaiser</strong><sup class="version"><?= $peerraiser['plugin_version'] ?></sup></h1>

        <div class="column column-left">

            <div class="welcome-message">
                <h2>Welcome to your dashboard, <?= $peerraiser['display_name'] ?></h2>
                <p>The dashboard provides an overview of your peer-to-peer campaigns, fundraising tips, and the latest news about this plugin.</p>

                <h3>Let's get you started...</h3>

                <ul>
                    <li class="status-complete"><i class="fa fa-fw <?= $peerraiser['font_awesome_class']['step_1'] ?>" aria-hidden="true"></i><a href="https://peerraiser.com/join">Create a free PeerRaiser account</a></li>
                    <li class="status-incomplete"><i class="fa fa-fw <?= $peerraiser['font_awesome_class']['step_2'] ?>" aria-hidden="true"></i><a href="<?= $peerraiser['admin_url'] ?>admin.php?page=peerraiser-settings">Configure your settings</a></li>
                    <li class="status-incomplete"><i class="fa fa-fw <?= $peerraiser['font_awesome_class']['step_3'] ?>" aria-hidden="true"></i><a href="<?= $peerraiser['admin_url'] ?>post-new.php?post_type=pr_campaign">Create your first campaign</a></li>
                </ul>

                <a href="#" class="close"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
            </div>

        </div>
        <div class="column column-right">
            <h2>Latest News</h2>
            <p>Something cool happened today</p>
        </div>

    </div>
</div>
