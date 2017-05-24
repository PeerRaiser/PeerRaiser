<?php

namespace PeerRaiser\Controller\Frontend;

class Widget extends \PeerRaiser\Controller\Base {

	public function register_actions() {
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	public function register_sidebars() {
		register_sidebar( array(
			'name' => __( 'PeerRaiser Campaign Sidebar', 'peerraiser' ),
			'id' => 'peerraiser-campaign-sidebar',
			'description' => __( 'Widgets in this area will be shown on side of campaign pages.', 'peerraiser' ),
			'before_widget' => '<div id="%1$s" class="peerraiser-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="peerraiser-widget-title">',
			'after_title'   => '</h2>',
		) );
	}

	public function register_widgets() {
		register_widget( 'PeerRaiser\Model\Frontend\Campaign_Donate_Button_Widget' );
	}

}