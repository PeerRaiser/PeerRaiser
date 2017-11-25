<p><?php _e( sprintf( '<strong>Date:</strong> %s UTC', date( 'Y-m-d H:i:s' ) ), 'peerraiser' ); ?></p>

<p><?php _e( sprintf( '<strong>Subtotal:</strong> %s', peerraiser_money_format( $donation->subtotal ) ), 'peerraiser' ); ?></p>

<p><?php _e( sprintf( '<strong>Total:</strong> %s', peerraiser_money_format( $donation->total ) ), 'peerraiser' ); ?></p>

<?php if ( $tax_id ) : ?>
	<p><?php _e( sprintf( "<strong>Organization's tax ID:</strong> %s", $tax_id ), 'peerraiser' ); ?></p>
<?php endif; ?>

<p><?php _e( sprintf( '<strong>Transaction ID:</strong> %s', $donation->transaction_id ), 'peerraiser' ); ?></p>