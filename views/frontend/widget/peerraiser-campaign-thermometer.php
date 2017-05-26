<?php echo $args['before_widget']; ?>

    <?php if ( ! empty( $instance['title'] ) ) : ?>
        <?php echo $args['before_title']; ?>
        <?php echo $instance['title']; ?>
        <?php echo $args['after_title']; ?>
    <?php endif; ?>

    <div class="peerraiser-thermometer-container">
        <div class="peerraiser-thermometer">
            <div class="peerraiser-thermometer-bar" style="width: <?php echo $peerraiser['goal_percentage']; ?>%"></div>
        </div>
        <div class="peerraiser-amount-percentage-info">
		    <?php printf( wp_kses( __( '<span class="peerraiser-amount-percentage-info-value">%1$s%%</span> <span class="peerraiser-amount-percentage-info-label">Raised of %2$s Goal</span>', 'peerraiser' ), array(  'span' => array( 'class' => array() ) ) ), $peerraiser['goal_percentage'], peerraiser_money_format( $campaign->campaign_goal ) ); ?>
        </div>
    </div>

<?php echo $args['after_widget']; ?>