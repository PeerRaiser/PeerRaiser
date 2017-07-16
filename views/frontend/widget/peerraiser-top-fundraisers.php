<?php echo $args['before_widget']; ?>

<?php if ( ! empty( $instance['title'] ) ) : ?>
    <?php echo $args['before_title']; ?>
        <?php echo $instance['title']; ?>
    <?php echo $args['after_title']; ?>
<?php endif; ?>
<?php if ( ! empty( $top_fundraisers ) ):  ?>
    <ol>
        <?php foreach ( $top_fundraisers as $fundraiser ) : ?>
            <li><a href="<?php echo get_the_permalink( $fundraiser->ID ); ?>"><?php echo $fundraiser->fundraiser_name; ?></a> (<?php echo peerraiser_money_format( $fundraiser->donation_value ); ?>)</li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php echo $args['after_widget']; ?>