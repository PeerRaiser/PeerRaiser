<?php
echo $args['before_widget'];
printf( '<a href="%1$s%2$s">%3$s</a>', $peerraiser['registration_page'], $campaign->campaign_slug, $peerraiser['button_label'] );
echo $args['after_widget'];