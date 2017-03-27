<?php

namespace PeerRaiser\Model\Admin;

class Donors extends \PeerRaiser\Model\Admin {

    private $fields = array();
    private $countries = array();
    private $states = array();

    public function __construct() {
        $this->countries = array(
            'United States' => __( 'United States', 'peerraiser'),
            'Afghanistan' => __( 'Afghanistan', 'peerraiser'),
            'Aland Islands' => __( 'Aland Islands', 'peerraiser'),
            'Albania' => __( 'Albania', 'peerraiser'),
            'Algeria' => __( 'Algeria', 'peerraiser'),
            'American Samoa' => __( 'American Samoa', 'peerraiser'),
            'Andorra' => __( 'Andorra', 'peerraiser'),
            'Angola' => __( 'Angola', 'peerraiser'),
            'Anguilla' => __( 'Anguilla', 'peerraiser'),
            'Antarctica' => __( 'Antarctica', 'peerraiser'),
            'Antigua and Barbuda' => __( 'Antigua and Barbuda', 'peerraiser'),
            'Argentina' => __( 'Argentina', 'peerraiser'),
            'Armenia' => __( 'Armenia', 'peerraiser'),
            'Aruba' => __( 'Aruba', 'peerraiser'),
            'Australia' => __( 'Australia', 'peerraiser'),
            'Austria' => __( 'Austria', 'peerraiser'),
            'Azerbaijan' => __( 'Azerbaijan', 'peerraiser'),
            'Bahamas' => __( 'Bahamas', 'peerraiser'),
            'Bahrain' => __( 'Bahrain', 'peerraiser'),
            'Bangladesh' => __( 'Bangladesh', 'peerraiser'),
            'Barbados' => __( 'Barbados', 'peerraiser'),
            'Belarus' => __( 'Belarus', 'peerraiser'),
            'Belgium' => __( 'Belgium', 'peerraiser'),
            'Belize' => __( 'Belize', 'peerraiser'),
            'Benin' => __( 'Benin', 'peerraiser'),
            'Bermuda' => __( 'Bermuda', 'peerraiser'),
            'Bhutan' => __( 'Bhutan', 'peerraiser'),
            'Bolivarian Republic of Venezuela' => __( 'Bolivarian Republic of Venezuela', 'peerraiser'),
            'Bonaire, Sint Eustatios and Saba' => __( 'Bonaire, Sint Eustatios and Saba', 'peerraiser'),
            'Bosnia and Herzegovina' => __( 'Bosnia and Herzegovina', 'peerraiser'),
            'Botswana' => __( 'Botswana', 'peerraiser'),
            'Bouvet Island' => __( 'Bouvet Island', 'peerraiser'),
            'Brazil' => __( 'Brazil', 'peerraiser'),
            'British Indian Ocean Territory' => __( 'British Indian Ocean Territory', 'peerraiser'),
            'Brunei Darussalam' => __( 'Brunei Darussalam', 'peerraiser'),
            'Bulgaria' => __( 'Bulgaria', 'peerraiser'),
            'Burkina Faso' => __( 'Burkina Faso', 'peerraiser'),
            'Burundi' => __( 'Burundi', 'peerraiser'),
            'Cambodia' => __( 'Cambodia', 'peerraiser'),
            'Cameroon' => __( 'Cameroon', 'peerraiser'),
            'Canada' => __( 'Canada', 'peerraiser'),
            'Cape Verde' => __( 'Cape Verde', 'peerraiser'),
            'Cayman Islands' => __( 'Cayman Islands', 'peerraiser'),
            'Central African Republic' => __( 'Central African Republic', 'peerraiser'),
            'Chad' => __( 'Chad', 'peerraiser'),
            'Chile' => __( 'Chile', 'peerraiser'),
            'China' => __( 'China', 'peerraiser'),
            'Christmas Island' => __( 'Christmas Island', 'peerraiser'),
            'Cocos (Keeling) Islands' => __( 'Cocos (Keeling) Islands', 'peerraiser'),
            'Colombia' => __( 'Colombia', 'peerraiser'),
            'Comoros' => __( 'Comoros', 'peerraiser'),
            'Congo' => __( 'Congo', 'peerraiser'),
            'Cook Islands' => __( 'Cook Islands', 'peerraiser'),
            'Costa Rica' => __( 'Costa Rica', 'peerraiser'),
            'Cote D\'Ivoire' => __( 'Cote D\'Ivoire', 'peerraiser'),
            'Croatia' => __( 'Croatia', 'peerraiser'),
            'Cuba' => __( 'Cuba', 'peerraiser'),
            'Curacao' => __( 'Curacao', 'peerraiser'),
            'Cyprus' => __( 'Cyprus', 'peerraiser'),
            'Czech Republic' => __( 'Czech Republic', 'peerraiser'),
            'Democratic People\'s Republic of Korea' => __( 'Democratic People\'s Republic of Korea', 'peerraiser'),
            'The Democratic Republic of the Congo' => __( 'The Democratic Republic of the Congo', 'peerraiser'),
            'Denmark' => __( 'Denmark', 'peerraiser'),
            'Djibouti' => __( 'Djibouti', 'peerraiser'),
            'Dominica' => __( 'Dominica', 'peerraiser'),
            'Dominican Republic' => __( 'Dominican Republic', 'peerraiser'),
            'Ecuador' => __( 'Ecuador', 'peerraiser'),
            'Egypt' => __( 'Egypt', 'peerraiser'),
            'El Salvador' => __( 'El Salvador', 'peerraiser'),
            'Equatorial Guinea' => __( 'Equatorial Guinea', 'peerraiser'),
            'Eritrea' => __( 'Eritrea', 'peerraiser'),
            'Estonia' => __( 'Estonia', 'peerraiser'),
            'Ethiopia' => __( 'Ethiopia', 'peerraiser'),
            'Falkland Islands (Malvinas)' => __( 'Falkland Islands (Malvinas)', 'peerraiser'),
            'Faroe Islands' => __( 'Faroe Islands', 'peerraiser'),
            'Federated States of Micronesia' => __( 'Federated States of Micronesia', 'peerraiser'),
            'Fiji' => __( 'Fiji', 'peerraiser'),
            'Finland' => __( 'Finland', 'peerraiser'),
            'The Former Yugoslav Republic of Macedonia' => __( 'The Former Yugoslav Republic of Macedonia', 'peerraiser'),
            'France' => __( 'France', 'peerraiser'),
            'French Guiana' => __( 'French Guiana', 'peerraiser'),
            'French Polynesia' => __( 'French Polynesia', 'peerraiser'),
            'French Southern Territories' => __( 'French Southern Territories', 'peerraiser'),
            'Gabon' => __( 'Gabon', 'peerraiser'),
            'Gambia' => __( 'Gambia', 'peerraiser'),
            'Georgia' => __( 'Georgia', 'peerraiser'),
            'Germany' => __( 'Germany', 'peerraiser'),
            'Ghana' => __( 'Ghana', 'peerraiser'),
            'Gibraltar' => __( 'Gibraltar', 'peerraiser'),
            'Greece' => __( 'Greece', 'peerraiser'),
            'Greenland' => __( 'Greenland', 'peerraiser'),
            'Grenada' => __( 'Grenada', 'peerraiser'),
            'Guadeloupe' => __( 'Guadeloupe', 'peerraiser'),
            'Guam' => __( 'Guam', 'peerraiser'),
            'Guatemala' => __( 'Guatemala', 'peerraiser'),
            'Guernsey' => __( 'Guernsey', 'peerraiser'),
            'Guinea' => __( 'Guinea', 'peerraiser'),
            'Guinea-Bissau' => __( 'Guinea-Bissau', 'peerraiser'),
            'Guyana' => __( 'Guyana', 'peerraiser'),
            'Haiti' => __( 'Haiti', 'peerraiser'),
            'Heard Island and McDonald Islands' => __( 'Heard Island and McDonald Islands', 'peerraiser'),
            'Holy See (Vatican City State)' => __( 'Holy See (Vatican City State)', 'peerraiser'),
            'Honduras' => __( 'Honduras', 'peerraiser'),
            'Hong Kong' => __( 'Hong Kong', 'peerraiser'),
            'Hungary' => __( 'Hungary', 'peerraiser'),
            'Iceland' => __( 'Iceland', 'peerraiser'),
            'India' => __( 'India', 'peerraiser'),
            'Indonesia' => __( 'Indonesia', 'peerraiser'),
            'Iraq' => __( 'Iraq', 'peerraiser'),
            'Ireland' => __( 'Ireland', 'peerraiser'),
            'Islamic Republic of Iran' => __( 'Islamic Republic of Iran', 'peerraiser'),
            'Isle of Man' => __( 'Isle of Man', 'peerraiser'),
            'Israel' => __( 'Israel', 'peerraiser'),
            'Italy' => __( 'Italy', 'peerraiser'),
            'Jamaica' => __( 'Jamaica', 'peerraiser'),
            'Japan' => __( 'Japan', 'peerraiser'),
            'Jersey' => __( 'Jersey', 'peerraiser'),
            'Jordan' => __( 'Jordan', 'peerraiser'),
            'Kazakhstan' => __( 'Kazakhstan', 'peerraiser'),
            'Kenya' => __( 'Kenya', 'peerraiser'),
            'Kiribati' => __( 'Kiribati', 'peerraiser'),
            'Kuwait' => __( 'Kuwait', 'peerraiser'),
            'Kyrgyzstan' => __( 'Kyrgyzstan', 'peerraiser'),
            'Laos People\'s Democratic Republic' => __( 'Laos People\'s Democratic Republic', 'peerraiser'),
            'Latvia' => __( 'Latvia', 'peerraiser'),
            'Lebanon' => __( 'Lebanon', 'peerraiser'),
            'Lesotho' => __( 'Lesotho', 'peerraiser'),
            'Liberia' => __( 'Liberia', 'peerraiser'),
            'Libya' => __( 'Libya', 'peerraiser'),
            'Liechtenstein' => __( 'Liechtenstein', 'peerraiser'),
            'Lithuania' => __( 'Lithuania', 'peerraiser'),
            'Luxembourg' => __( 'Luxembourg', 'peerraiser'),
            'Macao' => __( 'Macao', 'peerraiser'),
            'Madagascar' => __( 'Madagascar', 'peerraiser'),
            'Malawi' => __( 'Malawi', 'peerraiser'),
            'Malaysia' => __( 'Malaysia', 'peerraiser'),
            'Maldives' => __( 'Maldives', 'peerraiser'),
            'Mali' => __( 'Mali', 'peerraiser'),
            'Malta' => __( 'Malta', 'peerraiser'),
            'Marshall Islands' => __( 'Marshall Islands', 'peerraiser'),
            'Martinique' => __( 'Martinique', 'peerraiser'),
            'Mauritania' => __( 'Mauritania', 'peerraiser'),
            'Mauritius' => __( 'Mauritius', 'peerraiser'),
            'Mayotte' => __( 'Mayotte', 'peerraiser'),
            'Mexico' => __( 'Mexico', 'peerraiser'),
            'Monaco' => __( 'Monaco', 'peerraiser'),
            'Mongolia' => __( 'Mongolia', 'peerraiser'),
            'Montenegro' => __( 'Montenegro', 'peerraiser'),
            'Montserrat' => __( 'Montserrat', 'peerraiser'),
            'Morocco' => __( 'Morocco', 'peerraiser'),
            'Mozambique' => __( 'Mozambique', 'peerraiser'),
            'Myanmar' => __( 'Myanmar', 'peerraiser'),
            'Namibia' => __( 'Namibia', 'peerraiser'),
            'Nauru' => __( 'Nauru', 'peerraiser'),
            'Nepal' => __( 'Nepal', 'peerraiser'),
            'Netherlands' => __( 'Netherlands', 'peerraiser'),
            'New Caledonia' => __( 'New Caledonia', 'peerraiser'),
            'New Zealand' => __( 'New Zealand', 'peerraiser'),
            'Nicaragua' => __( 'Nicaragua', 'peerraiser'),
            'Niger' => __( 'Niger', 'peerraiser'),
            'Nigeria' => __( 'Nigeria', 'peerraiser'),
            'Niue' => __( 'Niue', 'peerraiser'),
            'Norfolk Island' => __( 'Norfolk Island', 'peerraiser'),
            'Northern Mariana Islands' => __( 'Northern Mariana Islands', 'peerraiser'),
            'Norway' => __( 'Norway', 'peerraiser'),
            'Oman' => __( 'Oman', 'peerraiser'),
            'Pakistan' => __( 'Pakistan', 'peerraiser'),
            'Palau' => __( 'Palau', 'peerraiser'),
            'Palestinian Territory, Occupied' => __( 'Palestinian Territory, Occupied', 'peerraiser'),
            'Panama' => __( 'Panama', 'peerraiser'),
            'Papua New Guinea' => __( 'Papua New Guinea', 'peerraiser'),
            'Paraguay' => __( 'Paraguay', 'peerraiser'),
            'Peru' => __( 'Peru', 'peerraiser'),
            'Philippines' => __( 'Philippines', 'peerraiser'),
            'Pitcairn' => __( 'Pitcairn', 'peerraiser'),
            'Plurinational State of Bolivia' => __( 'Plurinational State of Bolivia', 'peerraiser'),
            'Poland' => __( 'Poland', 'peerraiser'),
            'Portugal' => __( 'Portugal', 'peerraiser'),
            'Puerto Rico' => __( 'Puerto Rico', 'peerraiser'),
            'Qatar' => __( 'Qatar', 'peerraiser'),
            'Republic of Korea' => __( 'Republic of Korea', 'peerraiser'),
            'Republic of Moldova' => __( 'Republic of Moldova', 'peerraiser'),
            'Reunion' => __( 'Reunion', 'peerraiser'),
            'Romania' => __( 'Romania', 'peerraiser'),
            'Russian Federation' => __( 'Russian Federation', 'peerraiser'),
            'Rwanda' => __( 'Rwanda', 'peerraiser'),
            'Saint Barthelemy' => __( 'Saint Barthelemy', 'peerraiser'),
            'Saint Helena, Ascension and Tristan da Cunha' => __( 'Saint Helena, Ascension and Tristan da Cunha', 'peerraiser'),
            'Saint Kitts and Nevis' => __( 'Saint Kitts and Nevis', 'peerraiser'),
            'Saint Lucia' => __( 'Saint Lucia', 'peerraiser'),
            'Saint Martin (French)' => __( 'Saint Martin (French)', 'peerraiser'),
            'Saint Pierre and Miquelon' => __( 'Saint Pierre and Miquelon', 'peerraiser'),
            'Saint Vincent and the Grenadines' => __( 'Saint Vincent and the Grenadines', 'peerraiser'),
            'Samoa' => __( 'Samoa', 'peerraiser'),
            'San Marino' => __( 'San Marino', 'peerraiser'),
            'Sao Tome and Principe' => __( 'Sao Tome and Principe', 'peerraiser'),
            'Saudi Arabia' => __( 'Saudi Arabia', 'peerraiser'),
            'Senegal' => __( 'Senegal', 'peerraiser'),
            'Serbia' => __( 'Serbia', 'peerraiser'),
            'Seychelles' => __( 'Seychelles', 'peerraiser'),
            'S. Georgia &amp; S. Sandwich Isls.' => __( 'S. Georgia &amp; S. Sandwich Isls.', 'peerraiser'),
            'Sierra Leone' => __( 'Sierra Leone', 'peerraiser'),
            'Singapore' => __( 'Singapore', 'peerraiser'),
            'Sint Maarten (Dutch)' => __( 'Sint Maarten (Dutch)', 'peerraiser'),
            'Slovakia' => __( 'Slovakia', 'peerraiser'),
            'Slovenia' => __( 'Slovenia', 'peerraiser'),
            'Solomon Islands' => __( 'Solomon Islands', 'peerraiser'),
            'Somalia' => __( 'Somalia', 'peerraiser'),
            'South Africa' => __( 'South Africa', 'peerraiser'),
            'South Sudan' => __( 'South Sudan', 'peerraiser'),
            'Spain' => __( 'Spain', 'peerraiser'),
            'Sri Lanka' => __( 'Sri Lanka', 'peerraiser'),
            'Sudan' => __( 'Sudan', 'peerraiser'),
            'Suriname' => __( 'Suriname', 'peerraiser'),
            'Svalbard and Jan Mayen' => __( 'Svalbard and Jan Mayen', 'peerraiser'),
            'Swaziland' => __( 'Swaziland', 'peerraiser'),
            'Sweden' => __( 'Sweden', 'peerraiser'),
            'Switzerland' => __( 'Switzerland', 'peerraiser'),
            'Syrian Arab Republic' => __( 'Syrian Arab Republic', 'peerraiser'),
            'Taiwan, Province of China' => __( 'Taiwan, Province of China', 'peerraiser'),
            'Tajikistan' => __( 'Tajikistan', 'peerraiser'),
            'Thailand' => __( 'Thailand', 'peerraiser'),
            'Timor-Leste' => __( 'Timor-Leste', 'peerraiser'),
            'Togo' => __( 'Togo', 'peerraiser'),
            'Tokelau' => __( 'Tokelau', 'peerraiser'),
            'Tonga' => __( 'Tonga', 'peerraiser'),
            'Trinidad and Tobago' => __( 'Trinidad and Tobago', 'peerraiser'),
            'Tunisia' => __( 'Tunisia', 'peerraiser'),
            'Turkey' => __( 'Turkey', 'peerraiser'),
            'Turkmenistan' => __( 'Turkmenistan', 'peerraiser'),
            'Turks and Caicos Islands' => __( 'Turks and Caicos Islands', 'peerraiser'),
            'Tuvalu' => __( 'Tuvalu', 'peerraiser'),
            'Uganda' => __( 'Uganda', 'peerraiser'),
            'Ukraine' => __( 'Ukraine', 'peerraiser'),
            'United Arab Emirates' => __( 'United Arab Emirates', 'peerraiser'),
            'United Kingdom' => __( 'United Kingdom', 'peerraiser'),
            'United Republic of Tanzania' => __( 'United Republic of Tanzania', 'peerraiser'),
            'Uruguay' => __( 'Uruguay', 'peerraiser'),
            'USA Minor Outlying Islands' => __( 'USA Minor Outlying Islands', 'peerraiser'),
            'Uzbekistan' => __( 'Uzbekistan', 'peerraiser'),
            'Vanuatu' => __( 'Vanuatu', 'peerraiser'),
            'Viet Nam' => __( 'Viet Nam', 'peerraiser'),
            'Virgin Islands (British)' => __( 'Virgin Islands (British)', 'peerraiser'),
            'Virgin Islands (USA)' => __( 'Virgin Islands (USA)', 'peerraiser'),
            'Wallis and Futuna' => __( 'Wallis and Futuna', 'peerraiser'),
            'Western Sahara' => __( 'Western Sahara', 'peerraiser'),
            'Yemen' => __( 'Yemen', 'peerraiser'),
            'Zambia' => __( 'Zambia', 'peerraiser'),
            'Zimbabwe' => __( 'Zimbabwe', 'peerraiser'),
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
                    'donor_first_name' => array(
                        'name' => __( 'First Name', 'peerraiser' ),
                        'id'   => '_peerraiser_donor_first_name',
                        'type' => 'text',
                    ),
                    'donor_last_name' => array(
                        'name' => __( 'Last Name', 'peerraiser' ),
                        'id'   => '_peerraiser_donor_last_name',
                        'type' => 'text',
                    ),
                    'donor_user_account' => array(
                        'name'    => __('User Account', 'peerraiser'),
                        'id'      => '_peerraiser_donor_user_account',
                        'type'    => 'select',
                        'options' => array( $this, 'get_participants_for_select_field' ),
                    ),
                    'donor_email' => array(
                        'name' => __( 'Email Address', 'peerraiser' ),
                        'id'   => '_peerraiser_donor_email',
                        'type' => 'text_email',
                    ),
                    'donor_street_1' => array(
                        'name' => __( 'Street Address Line 1', 'peerraiser' ),
                        'id'   => '_peerraiser_donor_street_1',
                        'type' => 'text',
                    ),
                    'donor__street_2' => array(
                        'name' => __( 'Street Address Line 2', 'peerraiser' ),
                        'id'   => '_peerraiser_donor_street_2',
                        'type' => 'text',
                    ),
                    'donor_city' => array(
                        'name' => __( 'City', 'peerraiser' ),
                        'id'   => '_peerraiser_donor_city',
                        'type' => 'text',
                    ),
                    'donor_state' => array(
                        'name'    => __( 'State / Province', 'peerraiser' ),
                        'id'      => '_peerraiser_donor_state',
                        'type'    => 'select',
                        'options' => array( $this, 'get_select_options' ),
                    ),
                    'donor_zip' => array(
                        'name'    => __( 'Zip / Postal Code', 'peerraiser' ),
                        'id'      => '_peerraiser_donor_zip',
                        'type'    => 'text_small',
                    ),
                    'donor_country' => array(
                        'name'    => __( 'Country', 'peerraiser' ),
                        'id'      => '_peerraiser_donor_country',
                        'type'    => 'select',
                        'options' => array( $this, 'get_select_options' ),
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

    public function custom_label( $field_args, $field ) {

        $label = $field_args['name'];

        if ( $field_args['options']['tooltip'] ) {
            $label .= sprintf( '<span class="pr_tooltip"><i class="pr_icon fa %s"></i><span class="pr_tip">%s</span></span>', $field_args['options'][ 'tooltip-class' ], $field_args['options'][ 'tooltip' ]);
        }

        return $label;
    }

    /**
     * Get posts for CMB2 Select fields
     *
     * @since     1.0.0
     * @param     CMB2_Field    $field    The CMB2 field object
     * @return    array                   An array of posts
     */
    public function get_posts_for_select_field( $field ) {

        switch ( $field->args['name'] ) {
            case 'Campaign':
            case 'Campaigns':
                $post_type = 'pr_campaign';
                break;
            case 'Team':
            case 'Teams':
                $post_type = 'pr_team';
                break;
            case 'Fundraiser':
            case 'Fundraisers':
                $post_type = 'fundraiser';
                break;
            default:
                $post_type = 'post';
                break;
        }

        // Empty array to fill with posts
        $results = array();

        // WP_Query arguments
        $args = array (
            'post_type'              => array( $post_type ),
            'posts_per_page'         => '-1'
        );

        // The Query
        $query = new \WP_Query( $args );
        $posts = $query->get_posts();

        foreach($posts as $post) {
            $title = '(ID: ' . $post->ID .') '. $post->post_title;
            $results[$post->ID] = $title;
        }

        return $results;
    }


    public function get_selected_post( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) && $field->value !== '' ) {
            $post = get_post($field->value);
            $results[$field->value] = get_the_title( $post );
        }

        return $results;
    }


    public function get_participants_for_select_field( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) ) {
            $user_info = get_userdata($field->value);
            if ( $user_info ) {
                $results[$field->value] = $user_info->display_name;
            }
        }

        return $results;
    }


    public function get_select_options( $field ) {

        switch ( $field->args['id'] ) {
            case '_donor_country':
                return $this->countries;
                break;

            case '_donor_state':
                return $this->states;

            default:
                return array();
                break;
        }

    }


    public function get_donations( $post_id, $paged = 1 ) {
        $args = array(
            'post_type'       => 'pr_donation',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'donation_to_donor',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

    private function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
    }

}
