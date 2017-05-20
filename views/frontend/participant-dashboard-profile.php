<div class="peerraiser-dashboard">
    <nav class="peerraiser-nav">
        <ul>
            <?php foreach ( $peerraiser['navigation'] as $key => $value ) : ?>
                <li class="<?php echo sanitize_title( $key ) ?>"><a href="?page=<?php echo $key ?>"><?php echo $value ?></a></li>
            <?php endforeach; ?>

        </ul>
    </nav>

    <h2><?php _e( 'My Fundraisers', 'peerraiser' ); ?></h2>
    <?php if ( $peerraiser['fundraisers']->have_posts() ) : ?>
        <div class="fundraisers">
            <?php while( $peerraiser['fundraisers']->have_posts() ) : $peerraiser['fundraisers']->the_post() ?>
                <div class="fundraiser">
                    <div class="thumbnail">
                        <?php $campaign_id = get_post_meta( get_the_ID(), '_fundraiser_campaign', true ) ?>
                        <?php $thumbnail = get_post_meta($campaign_id, '_peerraiser_campaign_thumbnail', true); ?>
                        <?php $image = wp_get_attachment_image_src( get_post_meta( $campaign_id, '_peerraiser_campaign_thumbnail_id', 1 ), 'peerraiser_campaign_thumbnail' ); ?>
                        <?php $thumbnail = ( !empty($image) ) ? $image[0] : $peerraiser['default_campaign_thumbnail'] ?>
                        <img src="<?php echo $thumbnail ?>">
                    </div>
                    <div class="content">
                        <h3><?php the_title() ?></h3>
                        <p><?php echo get_the_title( $campaign_id ) ?></p>
                    </div>
                    <div class="controls">
                        <a href="<?php echo add_query_arg( 'edit', 'fundraiser', get_the_permalink() ) ?>"><?php _e( 'Manage', 'peerraiser' ); ?></a>
                        <a href="<?php echo get_the_permalink() ?>"><?php _e( 'View', 'peerraiser' ); ?></a>
                    </div>
                </div>
            <?php endwhile; ?>
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