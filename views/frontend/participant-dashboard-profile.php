<div class="peerraiser-dashboard">
    <nav class="peerraiser-nav">
        <ul>
            <?php foreach ( $peerraiser['navigation'] as $key => $value ) : ?>
                <li class="<?php echo sanitize_title( $key ) ?>"><a href="?page=<?php echo $key ?>"><?php echo $value ?></a></li>
            <?php endforeach; ?>

        </ul>
    </nav>

    <h2><?php _e( 'My Fundraisers', 'peerraiser' ); ?></h2>
    <?php if ( ! empty( $peerraiser['fundraisers'] ) ) : ?>
        <div class="fundraisers">
            <?php foreach( $peerraiser['fundraisers'] as $fundraiser ) : ?>
                <?php $campaign = new \PeerRaiser\Model\Campaign( $fundraiser->campaign_id ); ?>

                <div class="fundraiser">
                    <div class="thumbnail">
                        <img src="<?php echo $campaign->thumbnail_image; ?>">
                    </div>
                    <div class="content">
                        <h3><?php echo esc_attr( $fundraiser->fundraiser_name ); ?></h3>
                        <p><?php echo esc_attr( $campaign->campaign_name ); ?></p>
                    </div>
                    <div class="controls">
                        <!--<a href="--><?php //echo add_query_arg( 'edit', 'fundraiser', get_the_permalink() ) ?><!--">--><?php //_e( 'Manage', 'peerraiser' ); ?><!--</a>-->
                        <a href="<?php echo $fundraiser->get_fundraiser_url(); ?>"><?php _e( 'View', 'peerraiser' ); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p><?php _e( 'You do not currently have any fundraisers', 'peerraiser' ); ?></p>
    <?php endif; ?>

    <?php if ( ! empty( $peerraiser['teams'] ) ) : ?>
        <h2><?php _e( 'My Teams', 'peerraiser' ); ?></h2>
        <div class="teams">
        <?php foreach ( $peerraiser['teams'] as $team ) : ?>
            <?php $campaign = new \PeerRaiser\Model\Campaign( $team->campaign_id ); ?>

            <div class="team">
                <div class="thumbnail">
                    <img src="<?php echo $team->thumbnail_image; ?>">
                </div>
                <div class="content">
                    <h3><?php echo esc_attr( $team->team_name ); ?></h3>
                    <p><?php echo esc_attr( $campaign->campaign_name ); ?></p>
                </div>
                <div class="controls">
                    <!--<a href="--><?php //echo add_query_arg( 'edit', 'team', get_the_permalink() ) ?><!--">--><?php //_e( 'Manage', 'peerraiser' ); ?><!--</a>-->
                    <a href="<?php echo $team->get_permalink() ?>"><?php _e( 'View', 'peerraiser' ); ?></a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>