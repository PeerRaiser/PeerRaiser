<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>"><?php esc_attr_e( 'Button Title:', 'peerraiser' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_label' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['button_label'] ); ?>" placeholder="<?php _e( 'i.e. Register Now', 'peerraiser'); ?>">
</p>