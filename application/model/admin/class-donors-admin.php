<?php

namespace PeerRaiser\Model\Admin;

class Donors_Admin extends Admin {

    protected $fields = array();
    protected $countries = array();
    protected $states = array();

    public function __construct() {
        $this->countries = array(
            'US' =>	__( 'United States', 'peerraiser' ),
            'AF' =>	__( 'Afghanistan', 'peerraiser' ),
            'AX' =>	__( 'Aland Islands', 'peerraiser' ),
            'AL' =>	__( 'Albania', 'peerraiser' ),
            'DZ' =>	__( 'Algeria', 'peerraiser' ),
            'AD' =>	__( 'Andorra', 'peerraiser' ),
            'AO' =>	__( 'Angola', 'peerraiser' ),
            'AI' =>	__( 'Anguilla', 'peerraiser' ),
            'AQ' =>	__( 'Antarctica', 'peerraiser' ),
            'AG' =>	__( 'Antigua and Barbuda', 'peerraiser' ),
            'AR' =>	__( 'Argentina', 'peerraiser' ),
            'AM' =>	__( 'Armenia', 'peerraiser' ),
            'AW' =>	__( 'Aruba', 'peerraiser' ),
            'AU' =>	__( 'Australia', 'peerraiser' ),
            'AT' =>	__( 'Austria', 'peerraiser' ),
            'AZ' =>	__( 'Azerbaijan', 'peerraiser' ),
            'BS' =>	__( 'Bahamas', 'peerraiser' ),
            'BH' =>	__( 'Bahrain', 'peerraiser' ),
            'BD' =>	__( 'Bangladesh', 'peerraiser' ),
            'BB' =>	__( 'Barbados', 'peerraiser' ),
            'BY' =>	__( 'Belarus', 'peerraiser' ),
            'BE' =>	__( 'Belgium', 'peerraiser' ),
            'BZ' =>	__( 'Belize', 'peerraiser' ),
            'BJ' =>	__( 'Benin', 'peerraiser' ),
            'BM' =>	__( 'Bermuda', 'peerraiser' ),
            'BT' =>	__( 'Bhutan', 'peerraiser' ),
            'BO' =>	__( 'Bolivia, Plurinational State of', 'peerraiser' ),
            'BQ' =>	__( 'Bonaire, Sint Eustatius and Saba', 'peerraiser' ),
            'BA' =>	__( 'Bosnia and Herzegovina', 'peerraiser' ),
            'BW' =>	__( 'Botswana', 'peerraiser' ),
            'BV' =>	__( 'Bouvet Island', 'peerraiser' ),
            'BR' =>	__( 'Brazil', 'peerraiser' ),
            'IO' =>	__( 'British Indian Ocean Territory', 'peerraiser' ),
            'BN' =>	__( 'Brunei Darussalam', 'peerraiser' ),
            'BG' =>	__( 'Bulgaria', 'peerraiser' ),
            'BF' =>	__( 'Burkina Faso', 'peerraiser' ),
            'BI' =>	__( 'Burundi', 'peerraiser' ),
            'KH' =>	__( 'Cambodia', 'peerraiser' ),
            'CM' =>	__( 'Cameroon', 'peerraiser' ),
            'CA' =>	__( 'Canada', 'peerraiser' ),
            'CV' =>	__( 'Cape Verde', 'peerraiser' ),
            'KY' =>	__( 'Cayman Islands', 'peerraiser' ),
            'CF' =>	__( 'Central African Republic', 'peerraiser' ),
            'TD' =>	__( 'Chad', 'peerraiser' ),
            'CL' =>	__( 'Chile', 'peerraiser' ),
            'CN' =>	__( 'China', 'peerraiser' ),
            'CX' =>	__( 'Christmas Island', 'peerraiser' ),
            'CC' =>	__( 'Cocos (Keeling) Islands', 'peerraiser' ),
            'CO' =>	__( 'Colombia', 'peerraiser' ),
            'KM' =>	__( 'Comoros', 'peerraiser' ),
            'CG' =>	__( 'Congo', 'peerraiser' ),
            'CD' =>	__( 'Congo, the Democratic Republic of the', 'peerraiser' ),
            'CK' =>	__( 'Cook Islands', 'peerraiser' ),
            'CR' =>	__( 'Costa Rica', 'peerraiser' ),
            'CI' =>	__( 'Cote d’Ivoire', 'peerraiser' ),
            'HR' =>	__( 'Croatia', 'peerraiser' ),
            'CU' =>	__( 'Cuba', 'peerraiser' ),
            'CW' =>	__( 'Curaçao', 'peerraiser' ),
            'CY' =>	__( 'Cyprus', 'peerraiser' ),
            'CZ' =>	__( 'Czech Republic', 'peerraiser' ),
            'DK' =>	__( 'Denmark', 'peerraiser' ),
            'DJ' =>	__( 'Djibouti', 'peerraiser' ),
            'DM' =>	__( 'Dominica', 'peerraiser' ),
            'DO' =>	__( 'Dominican Republic', 'peerraiser' ),
            'EC' =>	__( 'Ecuador', 'peerraiser' ),
            'EG' =>	__( 'Egypt', 'peerraiser' ),
            'SV' =>	__( 'El Salvador', 'peerraiser' ),
            'GQ' =>	__( 'Equatorial Guinea', 'peerraiser' ),
            'ER' =>	__( 'Eritrea', 'peerraiser' ),
            'EE' =>	__( 'Estonia', 'peerraiser' ),
            'ET' =>	__( 'Ethiopia', 'peerraiser' ),
            'FK' =>	__( 'Falkland Islands (Malvinas)', 'peerraiser' ),
            'FO' =>	__( 'Faroe Islands', 'peerraiser' ),
            'FJ' =>	__( 'Fiji', 'peerraiser' ),
            'FI' =>	__( 'Finland', 'peerraiser' ),
            'FR' =>	__( 'France', 'peerraiser' ),
            'GF' =>	__( 'French Guiana', 'peerraiser' ),
            'PF' =>	__( 'French Polynesia', 'peerraiser' ),
            'TF' =>	__( 'French Southern Territories', 'peerraiser' ),
            'GA' =>	__( 'Gabon', 'peerraiser' ),
            'GM' =>	__( 'Gambia', 'peerraiser' ),
            'GE' =>	__( 'Georgia', 'peerraiser' ),
            'DE' =>	__( 'Germany', 'peerraiser' ),
            'GH' =>	__( 'Ghana', 'peerraiser' ),
            'GI' =>	__( 'Gibraltar', 'peerraiser' ),
            'GR' =>	__( 'Greece', 'peerraiser' ),
            'GL' =>	__( 'Greenland', 'peerraiser' ),
            'GD' =>	__( 'Grenada', 'peerraiser' ),
            'GP' =>	__( 'Guadeloupe', 'peerraiser' ),
            'GT' =>	__( 'Guatemala', 'peerraiser' ),
            'GG' =>	__( 'Guernsey', 'peerraiser' ),
            'GN' =>	__( 'Guinea', 'peerraiser' ),
            'GW' =>	__( 'Guinea-Bissau', 'peerraiser' ),
            'GY' =>	__( 'Guyana', 'peerraiser' ),
            'HT' =>	__( 'Haiti', 'peerraiser' ),
            'HM' =>	__( 'Heard Island and McDonald Islands', 'peerraiser' ),
            'VA' =>	__( 'Holy See (Vatican City State)', 'peerraiser' ),
            'HN' =>	__( 'Honduras', 'peerraiser' ),
            'HU' =>	__( 'Hungary', 'peerraiser' ),
            'IS' =>	__( 'Iceland', 'peerraiser' ),
            'IN' =>	__( 'India', 'peerraiser' ),
            'ID' =>	__( 'Indonesia', 'peerraiser' ),
            'IR' =>	__( 'Iran, Islamic Republic of', 'peerraiser' ),
            'IQ' =>	__( 'Iraq', 'peerraiser' ),
            'IE' =>	__( 'Ireland', 'peerraiser' ),
            'IM' =>	__( 'Isle of Man', 'peerraiser' ),
            'IL' =>	__( 'Israel', 'peerraiser' ),
            'IT' =>	__( 'Italy', 'peerraiser' ),
            'JM' =>	__( 'Jamaica', 'peerraiser' ),
            'JP' =>	__( 'Japan', 'peerraiser' ),
            'JE' =>	__( 'Jersey', 'peerraiser' ),
            'JO' =>	__( 'Jordan', 'peerraiser' ),
            'KZ' =>	__( 'Kazakhstan', 'peerraiser' ),
            'KE' =>	__( 'Kenya', 'peerraiser' ),
            'KI' =>	__( 'Kiribati', 'peerraiser' ),
            'KP' =>	__( 'Korea, Democratic People’s Republic of', 'peerraiser' ),
            'KR' =>	__( 'Korea, Republic of', 'peerraiser' ),
            'KW' =>	__( 'Kuwait', 'peerraiser' ),
            'KG' =>	__( 'Kyrgyzstan', 'peerraiser' ),
            'LA' =>	__( 'Lao People’s Democratic Republic', 'peerraiser' ),
            'LV' =>	__( 'Latvia', 'peerraiser' ),
            'LB' =>	__( 'Lebanon', 'peerraiser' ),
            'LS' =>	__( 'Lesotho', 'peerraiser' ),
            'LR' =>	__( 'Liberia', 'peerraiser' ),
            'LY' =>	__( 'Libyan Arab Jamahiriya', 'peerraiser' ),
            'LI' =>	__( 'Liechtenstein', 'peerraiser' ),
            'LT' =>	__( 'Lithuania', 'peerraiser' ),
            'LU' =>	__( 'Luxembourg', 'peerraiser' ),
            'MO' =>	__( 'Macao', 'peerraiser' ),
            'MK' =>	__( 'Macedonia, the former Yugoslav Republic of', 'peerraiser' ),
            'MG' =>	__( 'Madagascar', 'peerraiser' ),
            'MW' =>	__( 'Malawi', 'peerraiser' ),
            'MY' =>	__( 'Malaysia', 'peerraiser' ),
            'MV' =>	__( 'Maldives', 'peerraiser' ),
            'ML' =>	__( 'Mali', 'peerraiser' ),
            'MT' =>	__( 'Malta', 'peerraiser' ),
            'MQ' =>	__( 'Martinique', 'peerraiser' ),
            'MR' =>	__( 'Mauritania', 'peerraiser' ),
            'MU' =>	__( 'Mauritius', 'peerraiser' ),
            'YT' =>	__( 'Mayotte', 'peerraiser' ),
            'MX' =>	__( 'Mexico', 'peerraiser' ),
            'MD' =>	__( 'Moldova, Republic of', 'peerraiser' ),
            'MC' =>	__( 'Monaco', 'peerraiser' ),
            'MN' =>	__( 'Mongolia', 'peerraiser' ),
            'ME' =>	__( 'Montenegro', 'peerraiser' ),
            'MS' =>	__( 'Montserrat', 'peerraiser' ),
            'MA' =>	__( 'Morocco', 'peerraiser' ),
            'MZ' =>	__( 'Mozambique', 'peerraiser' ),
            'MM' =>	__( 'Myanmar', 'peerraiser' ),
            'NA' =>	__( 'Namibia', 'peerraiser' ),
            'NR' =>	__( 'Nauru', 'peerraiser' ),
            'NP' =>	__( 'Nepal', 'peerraiser' ),
            'NL' =>	__( 'Netherlands', 'peerraiser' ),
            'NC' =>	__( 'New Caledonia', 'peerraiser' ),
            'NZ' =>	__( 'New Zealand', 'peerraiser' ),
            'NI' =>	__( 'Nicaragua', 'peerraiser' ),
            'NE' =>	__( 'Niger', 'peerraiser' ),
            'NG' =>	__( 'Nigeria', 'peerraiser' ),
            'NU' =>	__( 'Niue', 'peerraiser' ),
            'NF' =>	__( 'Norfolk Island', 'peerraiser' ),
            'NO' =>	__( 'Norway', 'peerraiser' ),
            'OM' =>	__( 'Oman', 'peerraiser' ),
            'PK' =>	__( 'Pakistan', 'peerraiser' ),
            'PS' =>	__( 'Palestine', 'peerraiser' ),
            'PA' =>	__( 'Panama', 'peerraiser' ),
            'PG' =>	__( 'Papua New Guinea', 'peerraiser' ),
            'PY' =>	__( 'Paraguay', 'peerraiser' ),
            'PE' =>	__( 'Peru', 'peerraiser' ),
            'PH' =>	__( 'Philippines', 'peerraiser' ),
            'PN' =>	__( 'Pitcairn', 'peerraiser' ),
            'PL' =>	__( 'Poland', 'peerraiser' ),
            'PT' =>	__( 'Portugal', 'peerraiser' ),
            'QA' =>	__( 'Qatar', 'peerraiser' ),
            'RE' =>	__( 'Reunion', 'peerraiser' ),
            'RO' =>	__( 'Romania', 'peerraiser' ),
            'RU' =>	__( 'Russian Federation', 'peerraiser' ),
            'RW' =>	__( 'Rwanda', 'peerraiser' ),
            'BL' =>	__( 'Saint Barthélemy', 'peerraiser' ),
            'SH' =>	__( 'Saint Helena, Ascension and Tristan da Cunha', 'peerraiser' ),
            'KN' =>	__( 'Saint Kitts and Nevis', 'peerraiser' ),
            'LC' =>	__( 'Saint Lucia', 'peerraiser' ),
            'MF' =>	__( 'Saint Martin (French part)', 'peerraiser' ),
            'PM' =>	__( 'Saint Pierre and Miquelon', 'peerraiser' ),
            'VC' =>	__( 'Saint Vincent and the Grenadines', 'peerraiser' ),
            'WS' =>	__( 'Samoa', 'peerraiser' ),
            'SM' =>	__( 'San Marino', 'peerraiser' ),
            'ST' =>	__( 'Sao Tome and Principe', 'peerraiser' ),
            'SA' =>	__( 'Saudi Arabia', 'peerraiser' ),
            'SN' =>	__( 'Senegal', 'peerraiser' ),
            'RS' =>	__( 'Serbia', 'peerraiser' ),
            'SC' =>	__( 'Seychelles', 'peerraiser' ),
            'SL' =>	__( 'Sierra Leone', 'peerraiser' ),
            'SG' =>	__( 'Singapore', 'peerraiser' ),
            'SX' =>	__( 'Sint Maarten (Dutch part)', 'peerraiser' ),
            'SK' =>	__( 'Slovakia', 'peerraiser' ),
            'SI' =>	__( 'Slovenia', 'peerraiser' ),
            'SB' =>	__( 'Solomon Islands', 'peerraiser' ),
            'SO' =>	__( 'Somalia', 'peerraiser' ),
            'ZA' =>	__( 'South Africa', 'peerraiser' ),
            'GS' =>	__( 'South Georgia and the South Sandwich Islands', 'peerraiser' ),
            'SS' =>	__( 'South Sudan', 'peerraiser' ),
            'ES' =>	__( 'Spain', 'peerraiser' ),
            'LK' =>	__( 'Sri Lanka', 'peerraiser' ),
            'SD' =>	__( 'Sudan', 'peerraiser' ),
            'SR' =>	__( 'Suriname', 'peerraiser' ),
            'SJ' =>	__( 'Svalbard and Jan Mayen', 'peerraiser' ),
            'SZ' =>	__( 'Swaziland', 'peerraiser' ),
            'SE' =>	__( 'Sweden', 'peerraiser' ),
            'CH' =>	__( 'Switzerland', 'peerraiser' ),
            'SY' =>	__( 'Syrian Arab Republic', 'peerraiser' ),
            'TW' =>	__( 'Taiwan', 'peerraiser' ),
            'TJ' =>	__( 'Tajikistan', 'peerraiser' ),
            'TZ' =>	__( 'Tanzania, United Republic of', 'peerraiser' ),
            'TH' =>	__( 'Thailand', 'peerraiser' ),
            'TL' =>	__( 'Timor-Leste', 'peerraiser' ),
            'TG' =>	__( 'Togo', 'peerraiser' ),
            'TK' =>	__( 'Tokelau', 'peerraiser' ),
            'TO' =>	__( 'Tonga', 'peerraiser' ),
            'TT' =>	__( 'Trinidad and Tobago', 'peerraiser' ),
            'TN' =>	__( 'Tunisia', 'peerraiser' ),
            'TR' =>	__( 'Turkey', 'peerraiser' ),
            'TM' =>	__( 'Turkmenistan', 'peerraiser' ),
            'TC' =>	__( 'Turks and Caicos Islands', 'peerraiser' ),
            'TV' =>	__( 'Tuvalu', 'peerraiser' ),
            'UG' =>	__( 'Uganda', 'peerraiser' ),
            'UA' =>	__( 'Ukraine', 'peerraiser' ),
            'AE' =>	__( 'United Arab Emirates', 'peerraiser' ),
            'GB' =>	__( 'United Kingdom', 'peerraiser' ),
            'UY' =>	__( 'Uruguay', 'peerraiser' ),
            'UZ' =>	__( 'Uzbekistan', 'peerraiser' ),
            'VU' =>	__( 'Vanuatu', 'peerraiser' ),
            'VE' =>	__( 'Venezuela, Bolivarian Republic of', 'peerraiser' ),
            'VN' =>	__( 'Vietnam', 'peerraiser' ),
            'VG' =>	__( 'Virgin Islands, British', 'peerraiser' ),
            'WF' =>	__( 'Wallis and Futuna', 'peerraiser' ),
            'EH' =>	__( 'Western Sahara', 'peerraiser' ),
            'YE' =>	__( 'Yemen', 'peerraiser' ),
            'ZM' =>	__( 'Zambia', 'peerraiser' ),
            'ZW' =>	__( 'Zimbabwe', 'peerraiser' ),
        );
        $this->states = array(
            'AK' => __( 'Alaska', 'peerraiser' ),
            'AL' => __( 'Alabama', 'peerraiser' ),
            'AR' => __( 'Arkansas', 'peerraiser' ),
            'AZ' => __( 'Arizona', 'peerraiser' ),
            'CA' => __( 'California', 'peerraiser' ),
            'CO' => __( 'Colorado', 'peerraiser' ),
            'CT' => __( 'Connecticut', 'peerraiser' ),
            'DC' => __( 'District of Columbia', 'peerraiser' ),
            'DE' => __( 'Delaware', 'peerraiser' ),
            'FL' => __( 'Florida', 'peerraiser' ),
            'GA' => __( 'Georgia', 'peerraiser' ),
            'HI' => __( 'Hawaii', 'peerraiser' ),
            'IA' => __( 'Iowa', 'peerraiser' ),
            'ID' => __( 'Idaho', 'peerraiser' ),
            'IL' => __( 'Illinois', 'peerraiser' ),
            'IN' => __( 'Indiana', 'peerraiser' ),
            'KS' => __( 'Kansas', 'peerraiser' ),
            'KY' => __( 'Kentucky', 'peerraiser' ),
            'LA' => __( 'Louisiana', 'peerraiser' ),
            'MA' => __( 'Massachusetts', 'peerraiser' ),
            'MD' => __( 'Maryland', 'peerraiser' ),
            'ME' => __( 'Maine', 'peerraiser' ),
            'MI' => __( 'Michigan', 'peerraiser' ),
            'MN' => __( 'Minnesota', 'peerraiser' ),
            'MO' => __( 'Missouri', 'peerraiser' ),
            'MS' => __( 'Mississippi', 'peerraiser' ),
            'MT' => __( 'Montana', 'peerraiser' ),
            'NC' => __( 'North Carolina', 'peerraiser' ),
            'ND' => __( 'North Dakota', 'peerraiser' ),
            'NE' => __( 'Nebraska', 'peerraiser' ),
            'NH' => __( 'New Hampshire', 'peerraiser' ),
            'NJ' => __( 'New Jersey', 'peerraiser' ),
            'NM' => __( 'New Mexico', 'peerraiser' ),
            'NV' => __( 'Nevada', 'peerraiser' ),
            'NY' => __( 'New York', 'peerraiser' ),
            'OH' => __( 'Ohio', 'peerraiser' ),
            'OK' => __( 'Oklahoma', 'peerraiser' ),
            'OR' => __( 'Oregon', 'peerraiser' ),
            'PA' => __( 'Pennsylvania', 'peerraiser' ),
            'RI' => __( 'Rhode Island', 'peerraiser' ),
            'SC' => __( 'South Carolina', 'peerraiser' ),
            'SD' => __( 'South Dakota', 'peerraiser' ),
            'TN' => __( 'Tennessee', 'peerraiser' ),
            'TX' => __( 'Texas', 'peerraiser' ),
            'UT' => __( 'Utah', 'peerraiser' ),
            'VA' => __( 'Virginia', 'peerraiser' ),
            'VT' => __( 'Vermont', 'peerraiser' ),
            'WA' => __( 'Washington', 'peerraiser' ),
            'WI' => __( 'Wisconsin', 'peerraiser' ),
            'WV' => __( 'West Virginia', 'peerraiser' ),
            'WY' => __( 'Wyoming', 'peerraiser' ),
            'AS' => __( 'American Samoa', 'peerraiser' ),
            'FM' => __( 'Federated States of Micronesia', 'peerraiser' ),
            'GU' => __( 'Guam', 'peerraiser' ),
            'MH' => __( 'Marshall Islands', 'peerraiser' ),
            'MP' => __( 'Northern Mariana Islands', 'peerraiser' ),
            'PR' => __( 'Puerto Rico', 'peerraiser' ),
            'PW' => __( 'Palau', 'peerraiser' ),
            'VI' => __( 'Virgin Islands', 'peerraiser' ),
            'AA' => __( 'Armed Forces Americas', 'peerraiser' ),
            'AE' => __( 'Armed Forces', 'peerraiser' ),
            'AP' => __( 'Armed Forces Pacific', 'peerraiser' ),
            'AB' => __( 'Alberta', 'peerraiser' ),
            'BC' => __( 'British Columbia', 'peerraiser' ),
            'MB' => __( 'Manitoba', 'peerraiser' ),
            'NB' => __( 'New Brunswick', 'peerraiser' ),
            'NL' => __( 'Newfoundland and Labrador', 'peerraiser' ),
            'NS' => __( 'Nova Scotia', 'peerraiser' ),
            'NT' => __( 'Northwest Territories', 'peerraiser' ),
            'NU' => __( 'Nunavut', 'peerraiser' ),
            'ON' => __( 'Ontario', 'peerraiser' ),
            'PE' => __( 'Prince Edward Island', 'peerraiser' ),
            'QC' => __( 'Quebec', 'peerraiser' ),
            'SK' => __( 'Saskatchewan', 'peerraiser' ),
            'YT' => __( 'Yukon', 'peerraiser' ),
        );
        $this->fields = array(
            array(
                'title'    => __('Donor Info', 'peerraiser'),
                'id'       => 'peerraiser-donor-info',
                'context'  => 'normal',
                'priority' => 'default',
                'fields'   => array(
                    'first_name' => array(
                        'name'       => __( 'First Name', 'peerraiser' ),
                        'id'         => 'first_name',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value' ),
                        'attributes'  => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A first name is required', 'peerraiser' ),
                        ),
                    ),
                    'last_name' => array(
                        'name'       => __( 'Last Name', 'peerraiser' ),
                        'id'         => 'last_name',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value' ),
                    ),
                    'user_id' => array(
                        'name'       => __( 'User Account', 'peerraiser' ),
                        'id'         => 'user_id',
                        'type'       => 'select',
                        'options_cb' => array( $this, 'get_users_for_select_field' ),
                    ),
                    'email_address' => array(
                        'name'       => __( 'Email Address', 'peerraiser' ),
                        'id'         => 'email_address',
                        'type'       => 'text_email',
                        'default_cb' => array( $this, 'get_field_value' ),
                        'attributes'  => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'An email address is required', 'peerraiser' ),
                        ),
                    ),
                    'street_address_1' => array(
                        'name'       => __( 'Street Address Line 1', 'peerraiser' ),
                        'id'         => 'street_address_1',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value' ),
                    ),
                    'street_address_2' => array(
                        'name'       => __( 'Street Address Line 2', 'peerraiser' ),
                        'id'         => 'street_address_2',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value' ),
                    ),
                    'city' => array(
                        'name'       => __( 'City', 'peerraiser' ),
                        'id'         => 'city',
                        'type'       => 'text',
                        'default_cb' => array( $this, 'get_field_value' ),
                    ),
                    'state_province' => array(
                        'name'             => __( 'State / Province', 'peerraiser' ),
                        'id'               => 'state_province',
                        'type'             => 'select',
                        'show_option_none' => true,
                        'options_cb'       => array( $this, 'get_select_options' ),
                        'default_cb'       => array( $this, 'get_field_value' ),
                    ),
                    'zip_postal' => array(
                        'name'       => __( 'Zip / Postal Code', 'peerraiser' ),
                        'id'         => 'zip_postal',
                        'type'       => 'text_small',
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'country' => array(
                        'name'             => __( 'Country', 'peerraiser' ),
                        'id'               => 'country',
                        'type'             => 'select',
                        'show_option_none' => true,
                        'options_cb'       => array( $this, 'get_select_options' ),
                        'default_cb'       => array( $this, 'get_field_value'),
                    ),
                ),
            ),
        );
    }

    /**
     * Get all fields
     *
     * @since     1.0.0
     * @return    array    Field data
     */
    public function get_fields() {
        return $this->fields;
    }

    /**
     * Get a specific field by id
     *
     * @since     1.0.0
     * @param     string    $id    The field ID
     *
     * @return    array|false    The field data if available, or false if not
     */
    public function get_field( $id ) {
        if ( isset( $this->fields[$id] ) ) {
            return $this->fields[$id];
        } else {
            return false;
        }
    }

    public function get_field_value( $field ) {
        if ( ! isset( $_GET['donor'] ) )
            return;

        $donor_model = new \PeerRaiser\Model\Donor( $_GET['donor'] );

        $field_id = $field['id'];

        switch ( $field['id'] ) {
            default:
                $field_value = isset( $donor_model->$field_id ) ? $donor_model->$field_id : '';
                break;
        }

        return $field_value;
    }

    /**
     * Add fields
     *
     * @since    1.0.0
     * @param    array    $fields    The fields to add
     *                               format: array( 'id' => array('key' => 'value' ) )
     *
     * @return    array    All of the current fields
     */
    public function add_fields( array $fields ) {
        array_push($this->fields, $fields);

        return $this->fields;
    }

    public function get_users_for_select_field( $field ) {
        if ( ! isset( $_GET['donor'] ) )
            return;

        // Empty array to fill with posts
        $results = array();

        $donor_model = new \PeerRaiser\Model\Donor( $_GET['donor'] );
        $field_id = $field->args['id'];

        if ( isset( $donor_model->$field_id ) && $donor_model->$field_id !== '' ) {
            $user_info = get_userdata( $donor_model->$field_id );
            if ( $user_info ) {
                $results[$donor_model->$field_id] = $user_info->display_name;
            }
        }

        return $results;
    }

    public function get_select_options( $field ) {

        switch ( $field->args['id'] ) {
            case 'country':
                return $this->countries;
                break;

            case 'state_province':
                return $this->states;

            default:
                return array();
                break;
        }

    }

}