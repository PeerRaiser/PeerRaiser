<?php
// Useful global constants
define( 'PW_SELECT2_URL', plugin_dir_url( __FILE__ ) );
define( 'PW_SELECT2_VERSION', '2.0.4' );

/**
 * Render multi-value select input field
 */
function pr_multiselect_render( $field, $value, $object_id, $object_type, $field_type_object ) {

	$select_options = (array) $field->args['options'];

	if ( is_callable( $field->args['options_cb'] ) ) {
		$options = call_user_func( $field->args['options_cb'], $field );

		if ( $options && is_array( $options ) ) {
			$select_options += $options;
		}
	}

	$values = ( !empty($value) ) ? $value : array();

	$html = "<select name=\"{$field->args['_name']}[]\" id=\"{$field->args['_id']}\"";
	if ( isset($field->args['attributes']) ) {
		foreach ($field->args['attributes'] as $key => $value) {
			$html .= " " . $key . "=\"" . $value . "\"";
		}
	}
	$html .= " multiple>";
	$html .= "<option></option>";

	foreach ($select_options as $k => $v) {
		$html .= ( in_array($k, $values)  ) ? '<option selected="selected" ' : '<option ';
		$html .= 'value="'.$k.'">';
		$html .= $v;
		$html .="</option>";
	}

	$html .= "</select>";

	echo $html;

}
add_filter( 'cmb2_render_pr_multiselect', 'pr_multiselect_render', 10, 5 );
