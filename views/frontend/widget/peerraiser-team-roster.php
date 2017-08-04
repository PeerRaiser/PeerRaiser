<?php echo $args['before_widget']; ?>

<?php if ( ! empty( $instance['title'] ) ) : ?>
    <?php echo $args['before_title']; ?>
        <?php echo $instance['title']; ?>
    <?php echo $args['after_title']; ?>
<?php endif; ?>
<?php if ( ! empty( $fundraisers ) ):  ?>
    <ol>
        <?php foreach ( $fundraisers as $fundraiser ) : ?>
            <li>
                <div class="fundraiser-name">
                    <span><a href="<?php echo get_the_permalink( $fundraiser->ID ); ?>"><?php echo $fundraiser->fundraiser_name; ?></a><span>
                </div>
                <div class="fundraiser-total">
                    <span><?php echo peerraiser_money_format( $fundraiser->donation_value ); ?></span>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php echo $args['after_widget']; ?>