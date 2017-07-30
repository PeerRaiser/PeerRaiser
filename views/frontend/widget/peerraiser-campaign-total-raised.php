<?php echo $args['before_widget']; ?>

<?php if ( ! empty( $peerraiser['before_amount'] ) ) : ?>
    <?php echo wp_kses_post( $peerraiser['before_amount'] ); ?>
<?php endif; ?>

<h3><?php echo peerraiser_money_format( $campaign->donation_value ) ?></h3>

<?php if ( ! empty( $peerraiser['after_amount'] ) ) : ?>
    <?php echo wp_kses_post( $peerraiser['after_amount'] ); ?>
<?php endif; ?>

<?php echo $args['after_widget']; ?>