<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'before_amount' ) ); ?>"><?php esc_attr_e( 'Before Amount:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'before_amount' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'before_amount' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['before_amount'] ); ?>" placeholder="<?php _e( '', 'peerraiser'); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'after_amount' ) ); ?>"><?php esc_attr_e( 'After Amount:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'after_amount' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'after_amount' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['after_amount'] ); ?>" placeholder="<?php _e( '', 'peerraiser'); ?>">
</p>
<p>
    <input class="checkbox" type="checkbox" <?php checked( $peerraiser[ 'hide_if_zero' ], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_if_zero' ); ?>" name="<?php echo $this->get_field_name( 'hide_if_zero' ); ?>" />
    <label for="<?php echo $this->get_field_id( 'hide_if_zero' ); ?>"><?php _e( 'Hide this widget if total raised is 0', 'peerraiser' ); ?></label>
</p>