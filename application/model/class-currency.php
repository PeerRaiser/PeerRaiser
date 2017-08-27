<?php

namespace PeerRaiser\Model;

/**
 * PeerRaiser currency model.
 */
class Currency {

    /**
     * Contains all supported currencies.
     *
     * @var    array
     */
    protected $currencies = array();


    /**
     * Constructor
     */
    function __construct() {
        $this->currencies = array(
            array(
                'short_name' => 'USD',
                'full_name'  => __( 'United States Dollar', 'peerraiser' ),
                'symbol'     => '$'
            ),
            array(
                'short_name' => 'CAD',
                'full_name'  => __( 'Canadian Dollar', 'peerraiser' ),
                'symbol'     => '$'
            ),
            array(
                'short_name' => 'EUR',
                'full_name'  => __( 'Euro', 'peerraiser' ),
                'symbol'     => '€'
            ),
            array(
                'short_name' => 'GBP',
                'full_name'  => __( 'British Pound', 'peerraiser' ),
                'symbol'     => '£'
            ),
            array(
                'short_name' => 'ALL',
                'full_name'  => __( 'Albanian Lek', 'peerraiser' ),
                'symbol'     => 'Lek',
            ),
            array(
                'short_name' => 'DZD',
                'full_name'  => __( 'Algerian Dinar', 'peerraiser' ),
                'symbol'     => 'د.ج.‏',
            ),
            array(
                'short_name' => 'AMD',
                'full_name'  => __( 'Armenian Dram', 'peerraiser' ),
                'symbol'     => 'դր.',
            ),
            array(
                'short_name' => 'AUD',
                'full_name'  => __( 'Australian Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'AZN',
                'full_name'  => __( 'Azerbaijani Manat', 'peerraiser' ),
                'symbol'     => 'ман.',
            ),
            array(
                'short_name' => 'BDT',
                'full_name'  => __( 'Bangladeshi Taka', 'peerraiser' ),
                'symbol'     => '৳',
            ),
            array(
                'short_name' => 'BZD',
                'full_name'  => __( 'Belize Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'BAM',
                'full_name'  => __( 'Bosnia &amp; Herzegovina Convertible Mark', 'peerraiser' ),
                'symbol'     => 'KM',
            ),
            array(
                'short_name' => 'BWP',
                'full_name'  => __( 'Botswana Pula', 'peerraiser' ),
                'symbol'     => 'P',
            ),
            array(
                'short_name' => 'BND',
                'full_name'  => __( 'Brunei Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'BGN',
                'full_name'  => __( 'Bulgarian Lev', 'peerraiser' ),
                'symbol'     => 'лв.',
            ),
            array(
                'short_name' => 'KHR',
                'full_name'  => __( 'Cambodian Riel', 'peerraiser' ),
                'symbol'     => '៛',
            ),
            array(
                'short_name' => 'CNY',
                'full_name'  => __( 'Chinese Renminbi Yuan', 'peerraiser' ),
                'symbol'     => 'CN¥',
            ),
            array(
                'short_name' => 'CDF',
                'full_name'  => __( 'Congolese Franc', 'peerraiser' ),
                'symbol'     => 'FrCD',
            ),
            array(
                'short_name' => 'HRK',
                'full_name'  => __( 'Croatian Kuna', 'peerraiser' ),
                'symbol'     => 'kn',
            ),
            array(
                'short_name' => 'DKK',
                'full_name'  => __( 'Danish Krone', 'peerraiser' ),
                'symbol'     => 'kr',
            ),
            array(
                'short_name' => 'DOP',
                'full_name'  => __( 'Dominican Peso', 'peerraiser' ),
                'symbol'     => 'RD$',
            ),
            array(
                'short_name' => 'EGP',
                'full_name'  => __( 'Egyptian Pound', 'peerraiser' ),
                'symbol'     => 'ج.م.‏',
            ),
            array(
                'short_name' => 'ETB',
                'full_name'  => __( 'Ethiopian Birr', 'peerraiser' ),
                'symbol'     => 'Br',
            ),
            array(
                'short_name' => 'GEL',
                'full_name'  => __( 'Georgian Lari', 'peerraiser' ),
                'symbol'     => 'GEL',
            ),
            array(
                'short_name' => 'HKD',
                'full_name'  => __( 'Hong Kong Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'ISK',
                'full_name'  => __( 'Icelandic Króna', 'peerraiser' ),
                'symbol'     => 'kr',
            ),
            array(
                'short_name' => 'IDR',
                'full_name'  => __( 'Indonesian Rupiah', 'peerraiser' ),
                'symbol'     => 'Rp',
            ),
            array(
                'short_name' => 'ILS',
                'full_name'  => __( 'Israeli New Sheqel', 'peerraiser' ),
                'symbol'     => '₪',
            ),
            array(
                'short_name' => 'JMD',
                'full_name'  => __( 'Jamaican Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'KZT',
                'full_name'  => __( 'Kazakhstani Tenge', 'peerraiser' ),
                'symbol'     => 'тңг.',
            ),
            array(
                'short_name' => 'KES',
                'full_name'  => __( 'Kenyan Shilling', 'peerraiser' ),
                'symbol'     => 'Ksh',
            ),
            array(
                'short_name' => 'LBP',
                'full_name'  => __( 'Lebanese Pound', 'peerraiser' ),
                'symbol'     => 'ل.ل.‏',
            ),
            array(
                'short_name' => 'MOP',
                'full_name'  => __( 'Macanese Pataca', 'peerraiser' ),
                'symbol'     => 'MOP$',
            ),
            array(
                'short_name' => 'MKD',
                'full_name'  => __( 'Macedonian Denar', 'peerraiser' ),
                'symbol'     => 'MKD',
            ),
            array(
                'short_name' => 'MYR',
                'full_name'  => __( 'Malaysian Ringgit', 'peerraiser' ),
                'symbol'     => 'RM',
            ),
            array(
                'short_name' => 'MDL',
                'full_name'  => __( 'Moldovan Leu', 'peerraiser' ),
                'symbol'     => 'MDL',
            ),
            array(
                'short_name' => 'MAD',
                'full_name'  => __( 'Moroccan Dirham', 'peerraiser' ),
                'symbol'     => 'د.م.‏',
            ),
            array(
                'short_name' => 'MZN',
                'full_name'  => __( 'Mozambican Metical', 'peerraiser' ),
                'symbol'     => 'MTn',
            ),
            array(
                'short_name' => 'MMK',
                'full_name'  => __( 'Myanmar Kyat', 'peerraiser' ),
                'symbol'     => 'K',
            ),
            array(
                'short_name' => 'NAD',
                'full_name'  => __( 'Namibian Dollar', 'peerraiser' ),
                'symbol'     => 'N$',
            ),
            array(
                'short_name' => 'NPR',
                'full_name'  => __( 'Nepalese Rupee', 'peerraiser' ),
                'symbol'     => 'नेरू',
            ),
            array(
                'short_name' => 'ANG',
                'full_name'  => __( 'Netherlands Antillean Gulden', 'peerraiser' ),
                'symbol'     => 'T$',
            ),
            array(
                'short_name' => 'TWD',
                'full_name'  => __( 'New Taiwan Dollar', 'peerraiser' ),
                'symbol'     => 'NT$',
            ),
            array(
                'short_name' => 'NZD',
                'full_name'  => __( 'New Zealand Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'NGN',
                'full_name'  => __( 'Nigerian Naira', 'peerraiser' ),
                'symbol'     => '₦',
            ),
            array(
                'short_name' => 'NOK',
                'full_name'  => __( 'Norwegian Krone', 'peerraiser' ),
                'symbol'     => 'kr',
            ),
            array(
                'short_name' => 'PKR',
                'full_name'  => __( 'Pakistani Rupee', 'peerraiser' ),
                'symbol'     => '₨',
            ),
            array(
                'short_name' => 'PHP',
                'full_name'  => __( 'Philippine Peso', 'peerraiser' ),
                'symbol'     => '₱',
            ),
            array(
                'short_name' => 'PLN',
                'full_name'  => __( 'Polish Złoty', 'peerraiser' ),
                'symbol'     => 'zł',
            ),
            array(
                'short_name' => 'QAR',
                'full_name'  => __( 'Qatari Riyal', 'peerraiser' ),
                'symbol'     => 'ر.ق.‏',
            ),
            array(
                'short_name' => 'RON',
                'full_name'  => __( 'Romanian Leu', 'peerraiser' ),
                'symbol'     => 'RON',
            ),
            array(
                'short_name' => 'RUB',
                'full_name'  => __( 'Russian Ruble', 'peerraiser' ),
                'symbol'     => 'руб.',
            ),
            array(
                'short_name' => 'SAR',
                'full_name'  => __( 'Saudi Riyal', 'peerraiser' ),
                'symbol'     => 'ر.س.‏',
            ),
            array(
                'short_name' => 'RSD',
                'full_name'  => __( 'Serbian Dinar', 'peerraiser' ),
                'symbol'     => 'дин.',
            ),
            array(
                'short_name' => 'SGD',
                'full_name'  => __( 'Singapore Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'SOS',
                'full_name'  => __( 'Somali Shilling', 'peerraiser' ),
                'symbol'     => 'Ssh',
            ),
            array(
                'short_name' => 'ZAR',
                'full_name'  => __( 'South African Rand', 'peerraiser' ),
                'symbol'     => 'R',
            ),
            array(
                'short_name' => 'LKR',
                'full_name'  => __( 'Sri Lankan Rupee', 'peerraiser' ),
                'symbol'     => 'SL Re',
            ),
            array(
                'short_name' => 'SEK',
                'full_name'  => __( 'Swedish Krona', 'peerraiser' ),
                'symbol'     => 'kr',
            ),
            array(
                'short_name' => 'CHF',
                'full_name'  => __( 'Swiss Franc', 'peerraiser' ),
                'symbol'     => 'CHF',
            ),
            array(
                'short_name' => 'TZS',
                'full_name'  => __( 'Tanzanian Shilling', 'peerraiser' ),
                'symbol'     => 'TSh',
            ),
            array(
                'short_name' => 'THB',
                'full_name'  => __( 'Thai Baht', 'peerraiser' ),
                'symbol'     => '฿',
            ),
            array(
                'short_name' => 'TOP',
                'full_name'  => __( 'Tongan Paʻanga', 'peerraiser' ),
                'symbol'     => 'T$',
            ),
            array(
                'short_name' => 'TTD',
                'full_name'  => __( 'Trinidad and Tobago Dollar', 'peerraiser' ),
                'symbol'     => '$',
            ),
            array(
                'short_name' => 'TRY',
                'full_name'  => __( 'Turkish Lira', 'peerraiser' ),
                'symbol'     => 'TL',
            ),
            array(
                'short_name' => 'UGX',
                'full_name'  => __( 'Ugandan Shilling', 'peerraiser' ),
                'symbol'     => 'USh',
            ),
            array(
                'short_name' => 'UAH',
                'full_name'  => __( 'Ukrainian Hryvnia', 'peerraiser' ),
                'symbol'     => '₴',
            ),
            array(
                'short_name' => 'AED',
                'full_name'  => __( 'United Arab Emirates Dirham', 'peerraiser' ),
                'symbol'     => 'د.إ.‏',
            ),
            array(
                'short_name' => 'UZS',
                'full_name'  => __( 'Uzbekistani Som', 'peerraiser' ),
                'symbol'     => 'UZS',
            ),
            array(
                'short_name' => 'YER',
                'full_name'  => __( 'Yemeni Rial', 'peerraiser' ),
                'symbol'     => 'ر.ي.‏',
            ),
        );
    }


    /**
     * Get currencies.
     *
     * @return    array    currencies
     */
    public function get_currencies() {
        return $this->currencies;
    }


    /**
     * Get short name by currency_id.
     *
     * @param    integer    $currency_id
     *
     * @return    string    $short_name
     */
    public function get_short_name_by_currency_id( $currency_id ) {
        $short_name = null;

        foreach ( $this->currencies as $currency ) {
            if ( (int) $currency['id'] === (int) $currency_id ) {
                $short_name = $currency['short_name'];
                break;
            }
        }

        return $short_name;
    }


    /**
     * Get currency id by ISO 4217 currency code.
     *
     * @param    string    $name    ISO 4217 currency code
     *
     * @return    int|null    $currency_id
     */
    public function get_currency_id_by_iso4217_code( $name ) {
        $currency_id = null;

        foreach ( $this->currencies as $currency ) {
            if ( $currency['short_name'] === $name ) {
                $currency_id = $currency['id'];
                break;
            }
        }

        return $currency_id;
    }


    /**
     * Get full name of currency by ISO 4217 currency code.
     *
     * @param    string    $name    ISO 4217 currency code
     *
     * @return    string    $full_name
     */
    public function get_currency_name_by_iso4217_code( $name ) {
        $full_name = '';

        foreach ( $this->currencies as $currency ) {
            if ( $currency['short_name'] === $name ) {
                $full_name = $currency['full_name'];
                break;
            }
        }

        return $full_name;
    }


    /**
     * Get currency symbol by ISO 4217 currency code.
     *
     * @param    string    $name    ISO 4217 currency code
     *
     * @return    string    $symbol
     */
    public function get_currency_symbol_by_iso4217_code( $name ) {
        $symbol = '';

        foreach ( $this->currencies as $currency ) {
            if ( $currency['short_name'] === $name ) {
                $symbol = $currency['symbol'];
                break;
            }
        }

        return $symbol;
    }

}
