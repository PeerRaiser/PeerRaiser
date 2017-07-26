<?php echo $args['before_widget']; ?>

<?php if ( ! empty( $instance['title'] ) ) : ?>
    <?php echo $args['before_title']; ?>
        <?php echo $instance['title']; ?>
    <?php echo $args['after_title']; ?>
<?php endif; ?>
<?php if ( ! empty( $donations ) ):  ?>
    <ol>
        <?php foreach ( $donations as $donation ) : ?>
            <li><?php echo ( ! $donation->is_anonymous ) ? $donation->donor_name : _e( 'Anonymous', 'peerraiser' ); ?> (<?php echo peerraiser_money_format( $donation->total ); ?>)</li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>

<?php echo $args['after_widget']; ?>