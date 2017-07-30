<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>"><?php esc_attr_e( 'Button Title:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_label' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['button_label'] ); ?>" placeholder="<?php _e( 'i.e. Donate to this fundraiser', 'peerraiser'); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'fundraiser' ) ); ?>"><?php esc_attr_e( 'Campaign:', 'peerraiser' ); ?></label>
    <select name="<?php echo esc_attr( $this->get_field_name( 'fundraiser' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'fundraiser' ) ); ?>">
        <option value="auto" <?php selected( $peerraiser['fundraiser'], 'auto' ); ?>><?php _e( 'Current Fundraiser (auto detect)', 'peerraiser' ); ?></option>
        <?php foreach ( $peerraiser['fundraisers'] as $fundraiser ) :?>
            <option value="<?php echo esc_attr( $fundraiser->ID ); ?>" <?php selected( absint( $peerraiser['fundraiser'] ), $fundraiser->ID ) ?>><?php echo $fundraiser->fundraiser_name; ?></option>
        <?php endforeach; ?>
    </select>
</p>