<?php echo $args['before_widget']; ?>

<?php if ( ! empty( $instance['title'] ) ) : ?>
    <?php echo $args['before_title']; ?>
        <?php echo $instance['title']; ?>
    <?php echo $args['after_title']; ?>
<?php endif; ?>
<?php if ( ! empty( $top_donors ) ):  ?>
    <ol>
        <?php foreach ( $top_donors as $donor ) : ?>
            <li><a href="<?php echo get_the_permalink( $donor->ID ); ?>"><?php echo $donor->full_name; ?></a> (<?php echo peerraiser_money_format( $donor->donation_value ); ?>)</li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php echo $args['after_widget']; ?>