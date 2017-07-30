<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['title'] ); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'fundraiser' ) ); ?>"><?php esc_attr_e( 'Fundraiser:', 'peerraiser' ); ?></label>
    <select name="<?php echo esc_attr( $this->get_field_name( 'fundraiser' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'fundraiser' ) ); ?>">
        <option value="auto" <?php selected( $peerraiser['fundraiser'], 'auto' ); ?>><?php _e( 'Current Fundraiser (auto detect)', 'peerraiser' ); ?></option>
        <?php foreach ( $peerraiser['fundraisers'] as $fundraiser ) :?>
            <option value="<?php echo esc_attr( $fundraiser->ID ); ?>" <?php selected( absint( $peerraiser['fundraiser'] ), $fundraiser->ID ) ?>><?php echo $fundraiser->fundraiser_name; ?></option>
        <?php endforeach; ?>
    </select>
</p>