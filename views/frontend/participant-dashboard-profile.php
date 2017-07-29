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
                <?php
                $campaign = new \PeerRaiser\Model\Campaign( $fundraiser->campaign_id );
                ?>

                <div class="fundraiser">
                    <div class="thumbnail">
                        <?php error_log( 'image: ' . $campaign->thumbnail_image ); ?>
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

    <?php if ( $peerraiser['teams']->have_posts() ) : ?>
        <h2><?php _e( 'My Teams', 'peerraiser' ); ?></h2>
        <div class="teams">
        <?php while ( $peerraiser['teams']->have_posts() ) : $peerraiser['teams']->the_post() ?>
            <div class="team">
                <div class="thumbnail">
                    <?php $thumbnail = get_post_meta( get_the_ID(), '_peerraiser_team_thumbnail', true); ?>
                    <?php $image = wp_get_attachment_image_src( get_post_meta( get_the_ID(), '_peerraiser_team_thumbnail_id', 1 ), 'peerraiser_team_thumbnail' ); ?>
                    <?php $thumbnail = ( !empty($image) ) ? $image[0] : $peerraiser['default_team_thumbnail'] ?>
                    <img src="<?php echo $thumbnail ?>">
                </div>
                <div class="content">
                    <?php $campaign_id = get_post_meta( get_the_ID(), '_team_campaign', true ) ?>
                    <h3><?php the_title(); ?></h3>
                    <p><?php echo get_the_title( $campaign_id ) ?></p>
                </div>
                <div class="controls">
                    <a href="<?php echo add_query_arg( 'edit', 'team', get_the_permalink() ) ?>"><?php _e( 'Manage', 'peerraiser' ); ?></a>
                    <a href="<?php echo get_the_permalink() ?>"><?php _e( 'View', 'peerraiser' ); ?></a>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>

</div>