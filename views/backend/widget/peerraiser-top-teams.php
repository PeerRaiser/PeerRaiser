<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['title'] ); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'list_size' ) ); ?>"><?php esc_attr_e( 'List Size:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'list_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'list_size' ) ); ?>" type="number" value="<?php echo esc_attr( $peerraiser['list_size'] ); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'campaign' ) ); ?>"><?php esc_attr_e( 'Campaign:', 'peerraiser' ); ?></label>
    <select name="<?php echo esc_attr( $this->get_field_name( 'campaign' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'campaign' ) ); ?>">
        <option value="auto" <?php selected( $peerraiser['campaign'], 'auto' ); ?>><?php _e( 'Current Campaign (auto detect)', 'peerraiser' ); ?></option>
        <option value="all" <?php selected( $peerraiser['campaign'], 'all' ); ?>><?php _e( 'All Campaigns', 'peerraiser' ); ?></option>
        <?php foreach ( $peerraiser['campaigns'] as $campaign ) :?>
            <option value="<?php echo esc_attr( $campaign->ID ); ?>" <?php selected( absint( $peerraiser['campaign'] ), $campaign->ID ) ?>><?php echo $campaign->campaign_name; ?></option>
        <?php endforeach; ?>
    </select>
</p>