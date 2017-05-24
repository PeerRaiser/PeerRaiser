<?php
echo $args['before_widget'];
printf( '<a href="%1$s">%2$s</a>', $campaign->get_permalink(), $peerraiser['button_label'] );
echo $args['after_widget'];