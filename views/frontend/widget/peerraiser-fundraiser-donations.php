<?php echo $args['before_widget']; ?>

<?php if ( ! empty( $instance['title'] ) ) : ?>
    <?php echo $args['before_title']; ?>
        <?php echo $instance['title']; ?>
    <?php echo $args['after_title']; ?>
<?php endif; ?>

<?php if ( ! empty( $top_donors ) ):  ?>
    <ol>
        <?php foreach ( $top_donors as $donor ) : ?>
            <li><?php echo $donor->full_name; ?> (<?php echo peerraiser_money_format( $donor->total ); ?>)</li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php echo $args['after_widget']; ?>