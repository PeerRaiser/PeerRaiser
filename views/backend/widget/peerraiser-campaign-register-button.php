<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>"><?php esc_attr_e( 'Button Title:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_label' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['button_label'] ); ?>" placeholder="<?php _e( 'i.e. Register Now', 'peerraiser'); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'campaign' ) ); ?>"><?php esc_attr_e( 'Campaign:', 'peerraiser' ); ?></label>
    <select name="<?php echo esc_attr( $this->get_field_name( 'campaign' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'campaign' ) ); ?>">
        <option value="auto" <?php selected( $peerraiser['campaign'], 'auto' ); ?>><?php _e( 'Current Campaign (auto detect)', 'peerraiser' ); ?></option>
        <?php foreach ( $peerraiser['campaigns'] as $campaign ) :?>
            <option value="<?php echo esc_attr( $campaign->ID ); ?>" <?php selected( absint( $peerraiser['campaign'] ), $campaign->ID ) ?>><?php echo $campaign->campaign_name; ?></option>
        <?php endforeach; ?>
    </select>
</p>