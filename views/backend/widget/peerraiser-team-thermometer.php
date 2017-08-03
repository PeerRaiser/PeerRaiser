<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'peerraiser' ); ?></label>
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $peerraiser['title'] ); ?>">
</p>
<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'team' ) ); ?>"><?php esc_attr_e( 'Team:', 'peerraiser' ); ?></label>
    <select name="<?php echo esc_attr( $this->get_field_name( 'team' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'team' ) ); ?>">
        <option value="auto" <?php selected( $peerraiser['team'], 'auto' ); ?>><?php _e( 'Current Team (auto detect)', 'peerraiser' ); ?></option>
        <?php foreach ( $peerraiser['teams'] as $team ) :?>
            <option value="<?php echo esc_attr( $team->ID ); ?>" <?php selected( absint( $peerraiser['team'] ), $team->ID ) ?>><?php echo $team->team_name; ?></option>
        <?php endforeach; ?>
    </select>
</p>