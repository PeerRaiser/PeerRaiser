<div class="submitbox" id="submitpost">
    <div id="campaign-stats">
        <ul class="campaign-info">
            <li><strong><?php _e('Total Donations', 'peerraiser') ?></strong> <br><span class="badge"><?= $peerraiser['currency_symbol'] . $peerraiser['total_donations'] ?></span></li>
            <?php if ( $peerraiser['has_goal'] ) : ?>
                <li><strong><?php _e('Goal Percent', 'peerraiser') ?></strong> <br><span class="badge"><?= $peerraiser['goal_percent'] ?>%</span></li>
            <?php endif; ?>
            <?php if ( $peerraiser['has_end_date'] ) : ?>
                <li><strong><?php _e('Days Left', 'peerraiser') ?></strong> <br><span class="badge <?= $peerraiser['days_left_class'] ?>"><?= $peerraiser['days_left'] ?></span></li>
            <?php endif; ?>
        </ul>
    </div>
</div>