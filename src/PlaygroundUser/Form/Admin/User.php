<?php

namespace PlaygroundUser\Form\Admin;

use PlaygroundUser\Options\UserCreateOptionsInterface;
use ZfcUser\Options\RegistrationOptionsInterface;
use ZfcUser\Form\Register as Register;
use Zend\Mvc\I18n\Translator;

class User extends Register
{
    /**
     * @var userRolesMapper
     */
    protected $userRolesMapper;
    /**
     * @var RegistrationOptionsInterface
     */
    protected $createOptionsOptions;

    protected $serviceManager;

    public function __construct($name, UserCreateOptionsInterface $createOptions, RegistrationOptionsInterface $registerOptions, Translator $translator, $serviceManager)
    {
        $this->setCreateOptions($createOptions);
        $this->setServiceManager($serviceManager);
        parent::__construct($name, $registerOptions);

        $availableRoles = $this->getUserRolesMapper()->getRoles();
        $rolesSelect = array();
        foreach ($availableRoles as $id => $role) {
            $rolesSelect[$role->getRoleId()] = array(
                'value' => $role->getRoleId(),
                'label' => $role->getRoleId(),
                'selected' => false,
            );
        }
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => 0
            ),
        ));

        // create a password automaticaly
        if ($createOptions->getCreateUserAutoPassword()) {
            $this->remove('password');
            $this->remove('passwordVerify');
        }

        $this->get('username')->setLabel($translator->translate('Username', 'playgrounduser'));
        $this->add(array(
            'name' => 'firstname',
            'options' => array(
                'label' => $translator->translate('First Name', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('First Name', 'playgrounduser'),
            ),
        ));

        $this->add(array(
            'name' => 'lastname',
            'options' => array(
                'label' => $translator->translate('Last Name', 'playgrounduser'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Last Name', 'playgrounduser'),
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'label' => $translator->translate('Title', 'playgrounduser'),
                'value_options' => array(
                    'M'  => $translator->translate('Mister', 'playgrounduser'),
                    'Me' => $translator->translate('Miss', 'playgrounduser'),
                ),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'roleId',
            'attributes' =>  array(
                'id' => 'roleId',
                'options' => $rolesSelect,
            ),
            'options' => array(
                'empty_option' => $translator->translate('Select a role', 'playgrounduser'),
                'label' => $translator->translate('Roles', 'playgrounduser'),
            ),
        ));

        $this->add(array(
            'name' => 'avatar',
            'attributes' => array(
                    'type'  => 'file',
            ),
            'options' => array(
                    'label' => $translator->translate('Avatar', 'playgrounduser'),
            ),
        ));

        $this->add(array(
                'name' => 'address',
                'options' => array(
                        'label' => $translator->translate('Address', 'playgrounduser'),
                ),
                'attributes' => array(
                        'type' => 'text',
                        'placeholder' => $translator->translate('Address', 'playgrounduser'),
                ),
        ));

        $this->add(array(
                'name' => 'address2',
                'options' => array(
                        'label' => $translator->translate('Address 2', 'playgrounduser'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('Address 2', 'playgrounduser'),
                ),
        ));

        $this->add(array(
            'name' => 'postalCode',
            'options' => array(
                    'label' => $translator->translate('Postal Code', 'playgrounduser'),
            ),
            'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('Postal Code', 'playgrounduser'),
            ),
        ));

        $this->add(array(
                'name' => 'city',
                'options' => array(
                        'label' => $translator->translate('City', 'playgrounduser'),
                ),
                'attributes' => array(
                        'type' => 'text',
                        'placeholder' => $translator->translate('City', 'playgrounduser'),
                ),
        ));

        $countries = $this->getCountries();
        $countries_label = array();
        foreach ($countries as $key => $name) {
            $countries_label[$key] = $translator->translate($name, 'playgrounduser');
        }
        asort($countries_label);
        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'country',
                'options' => array(
                        'empty_option' => $translator->translate('Select your country', 'playgrounduser'),
                        'value_options' => $countries_label,
                        'label' => $translator->translate('Country', 'playgrounduser')
                )
        ));

        $this->add(array(
                'name' => 'telephone',
                'options' => array(
                        'label' => $translator->translate('Telephone', 'playgrounduser'),
                ),
                'attributes' => array(
                        'type' => 'text',
                        'placeholder' => $translator->translate('Telephone', 'playgrounduser'),
                ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\DateTime',
            'name' => 'dob',
            'options' => array(
                'label' => $translator->translate('Date of birth', 'playgrounduser'),
                'format' => 'd/m/Y'
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Date of birth', 'playgrounduser'),
                'class'=> 'date-birth'
            )
        ));

        $this->add(array(
                'name' => 'optin',
                'type' => 'Zend\Form\Element\Radio',
                'options' => array(
                        'label' => $translator->translate('Newsletter', 'playgrounduser'),
                        'value_options' => array(
                                '1'  => $translator->translate('Yes', 'playgrounduser'),
                                '0'  => $translator->translate('No', 'playgrounduser'),
                        ),
                ),
        ));

        $this->add(array(
                'name' => 'optinPartner',
                'type' => 'Zend\Form\Element\Radio',
                'options' => array(
                        'label' => $translator->translate('Partners Newsletter', 'playgrounduser'),
                        'value_options' => array(
                                '1'  => $translator->translate('Yes', 'playgrounduser'),
                                '0'  => $translator->translate('No', 'playgrounduser'),
                        ),
                ),
        ));

        /*foreach ($this->getCreateOptions()->getCreateFormElements() as $name => $element) {
            $this->add(array(
                'name' => $element,
                'options' => array(
                    'label' => $name,
                ),
                'attributes' => array(
                    'type' => 'text'
                ),
            ));
        }*/

        $this->get('submit')->setLabel('Create');
    }

    public function getUserRolesMapper()
    {
        if (null === $this->userRolesMapper) {
            $this->userRolesMapper = $this->getServiceManager()->get('BjyAuthorize\Provider\Role\ObjectRepositoryProvider');
        }

        return $this->userRolesMapper;
    }

    public function setUserRolesMapper($userRolesMapper)
    {
        $this->userRolesMapper = $userRolesMapper;

        return $this;
    }

    public function setCreateOptions(UserCreateOptionsInterface $createOptionsOptions)
    {
        $this->createOptions = $createOptionsOptions;

        return $this;
    }

    public function getCreateOptions()
    {
        return $this->createOptions;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function getCountries()
    {
        return array(
                'FR' => 'France',
          'AF' => 'Afghanistan',
          'ZA' => 'South Africa',
          'AL' => 'Albania',
          'DZ' => 'Algeria',
          'DE' => 'Germany',
          'AD' => 'Andorra',
          'AO' => 'Angola',
          'AI' => 'Anguilla',
          'AQ' => 'Antarctica',
          'AG' => 'Antigua and Barbuda',
          'AN' => 'Netherlands Antilles',
          'SA' => 'Saudi Arabia',
          'AR' => 'Argentina',
          'AM' => 'Armenia',
          'AW' => 'Aruba',
          'AU' => 'Australia',
          'AT' => 'Austria',
          'AZ' => 'Azerbaijan',
          'BS' => 'Bahamas',
          'BH' => 'Bahrain',
          'BD' => 'Bangladesh',
          'BB' => 'Barbados',
          'BE' => 'Belgium',
          'BZ' => 'Belize',
          'BM' => 'Bermuda',
          'BT' => 'Bhutan',
          'BO' => 'Bolivia',
          'BA' => 'Bosnia and Herzegovina',
          'BW' => 'Botswana',
          'BN' => 'Brunei Darussalam',
          'BR' => 'Brazil',
          'BG' => 'Bulgaria',
          'BF' => 'Burkina Faso',
          'BI' => 'Burundi',
          'BY' => 'Belarus',
          'BJ' => 'Benin',
          'KH' => 'Cambodia',
          'CM' => 'Cameroon',
          'CA' => 'Canada',
          'CV' => 'Cape Verde',
          'CL' => 'Chile',
          'CN' => 'China',
          'CY' => 'Cyprus',
          'CO' => 'Colombia',
          'KM' => 'Comores',
          'CG' => 'Congo',
          'KP' => 'North Korea',
          'KR' => 'South Korea',
          'CR' => 'Costa Rica',
          'HR' => 'Croatia',
          'CU' => 'Cuba',
          'CI' => 'Côte d’Ivoire',
          'DK' => 'Denmark',
          'DJ' => 'Djibouti',
          'DM' => 'Dominique',
          'SV' => 'El Salvador',
          'ES' => 'Spain',
          'EE' => 'Estonia',
          'FJ' => 'Fiji',
          'FI' => 'Finland',
          'FR' => 'France',
          'GA' => 'Gabon',
          'GM' => 'Gambia',
          'GH' => 'Ghana',
          'GI' => 'Gibraltar',
          'GD' => 'Granada',
          'GL' => 'Greenland',
          'GR' => 'Greece',
          'GP' => 'Guadeloupe',
          'GU' => 'Guam',
          'GT' => 'Guatemala',
          'GG' => 'Guernesey',
          'GN' => 'Guinea',
          'GQ' => 'Equatorial Guinea',
          'GW' => 'Guinée-Bissau',
          'GY' => 'Guyana',
          'GF' => 'French Guiana',
          'GE' => 'Georgia',
          'GS' => 'South Georgia and the South Sandwich Islands',
          'HT' => 'Haïti',
          'HN' => 'Honduras',
          'HU' => 'Hungary',
          'IN' => 'India',
          'ID' => 'Indonesia',
          'IQ' => 'Iraq',
          'IR' => 'Iran',
          'IE' => 'Ireland',
          'IS' => 'Iceland',
          'IL' => 'Israel',
          'IT' => 'Italia',
          'JM' => 'Jamaica',
          'JP' => 'Japan',
          'JE' => 'Jersey',
          'JO' => 'Jordan',
          'KZ' => 'Kazakhstan',
          'KE' => 'Kenya',
          'KG' => 'Kirghizistan',
          'KI' => 'Kiribati',
          'KW' => 'Kuwait',
          'LA' => 'Laos',
          'LS' => 'Lesotho',
          'LV' => 'Latvia',
          'LB' => 'Lebanon',
          'LY' => 'Libya',
          'LR' => 'Liberia',
          'LI' => 'Liechtenstein',
          'LT' => 'Lithuania',
          'LU' => 'Luxembourg',
          'MK' => 'Macedonia',
          'MG' => 'Madagascar',
          'MY' => 'Malaysia',
          'MW' => 'Malawi',
          'MV' => 'Maldives',
          'ML' => 'Mali',
          'MT' => 'Malta',
          'MA' => 'Maroc',
          'MQ' => 'Martinique',
          'MU' => 'Maurice',
          'MR' => 'Mauritania',
          'YT' => 'Mayotte',
          'MX' => 'Mexico',
          'MD' => 'Moldova',
          'MC' => 'Monaco',
          'MN' => 'Mongolia',
          'MS' => 'Montserrat',
          'ME' => 'Monténégro',
          'MZ' => 'Mozambique',
          'MM' => 'Myanmar',
          'NA' => 'Namibia',
          'NR' => 'Nauru',
          'NI' => 'Nicaragua',
          'NE' => 'Niger',
          'NG' => 'Nigeria',
          'NU' => 'Niue',
          'NO' => 'Norway',
          'NC' => 'New Caledonia',
          'NZ' => 'New Zealand',
          'NP' => 'Nepal',
          'OM' => 'Oman',
          'UG' => 'Uganda',
          'UZ' => 'Uzbekistan',
          'PK' => 'Pakistan',
          'PW' => 'Palau',
          'PA' => 'Panama',
          'PG' => 'Papua New Guinea',
          'PY' => 'Paraguay',
          'NL' => 'Netherlands',
          'PH' => 'Philippines',
          'PN' => 'Pitcairn',
          'PL' => 'Poland',
          'PF' => 'French Polynesia',
          'PR' => 'Puerto Rico',
          'PT' => 'Portugal',
          'PE' => 'Peru',
          'QA' => 'Qatar',
          'HK' => 'R.A.S. Chinese Hong Kong',
          'MO' => 'Chinese R.A.S. Macau',
          'RO' => 'Romania',
          'GB' => 'United Kingdom',
          'RU' => 'Russia',
          'RW' => 'Rwanda',
          'CF' => 'Central African Republic',
          'DO' => 'Dominican Republic',
          'CD' => 'Democratic Republic of Congo',
          'CZ' => 'Czech Republice',
          'RE' => 'Reunion',
          'EH' => 'Western Sahara',
          'BL' => 'Saint Bartholomew',
          'KN' => 'Saint Kitts and Nevis',
          'SM' => 'San Marino',
          'MF' => 'Saint-Martin',
          'PM' => 'Saint Pierre and Miquelon',
          'VC' => 'Saint Vincent and the Grenadines',
          'SH' => 'St. Helena',
          'LC' => 'St. Lucia',
          'WS' => 'Samoa',
          'AS' => 'American Samoa',
          'ST' => 'Sao Tome and Principe',
          'RS' => 'Serbia',
          'CS' => 'Serbia and Montenegro',
          'SC' => 'Seychelles',
          'SL' => 'Sierra Leone',
          'SG' => 'Singapore',
          'SK' => 'Slovakia',
          'SI' => 'Slovenia',
          'SO' => 'Somalia',
          'SD' => 'Sudan',
          'LK' => 'Sri Lanka',
          'CH' => 'Switzerland',
          'SR' => 'Suriname',
          'SE' => 'Sweden',
          'SJ' => 'Svalbard and Jan Mayen',
          'SZ' => 'Swaziland',
          'SY' => 'Syria',
          'SN' => 'Senegal',
          'TJ' => 'Tajikistan',
          'TZ' => 'Tanzania',
          'TW' => 'Taiwan',
          'TD' => 'Chad',
          'TF' => 'French Southern Territories',
          'IO' => 'British Indian Ocean Territory',
          'PS' => 'Palestinian Territory',
          'TH' => 'Thailand',
          'TL' => 'East Timor',
          'TG' => 'Togo',
          'TK' => 'Tokelau',
          'TO' => 'Tonga',
          'TT' => 'Trinidad and Tobago',
          'TN' => 'Tunisia',
          'TM' => 'Turkmenistan',
          'TR' => 'Turkey',
          'TV' => 'Tuvalu',
          'UA' => 'Ukraine',
          'UY' => 'Uruguay',
          'VU' => 'Vanuatu',
          'VE' => 'Venezuela',
          'VN' => 'Vietnam',
          'WF' => 'Wallis and Futuna',
          'YE' => 'Yemen',
          'ZM' => 'Zambia',
          'ZW' => 'Zimbabwe',
          'ZZ' => 'indeterminate region',
          'EG' => 'Égypt',
          'AE' => 'UAE',
          'EC' => 'Ecuador',
          'ER' => 'Eritrea',
          'VA' => 'State of the Vatican City',
          'FM' => 'Federated States of Micronesia',
          'US' => 'United States',
          'ET' => 'Ethiopia',
          'BV' => 'Bouvet island',
          'CX' => 'Christmas Island',
          'NF' => 'Norfolk island',
          'IM' => 'Isle of Man',
          'KY' => 'Cayman Islands',
          'CC' => 'Cocos - Keeling',
          'CK' => 'Cook islands',
          'FO' => 'Faroe islands',
          'HM' => 'Heard and McDonald Islands',
          'FK' => 'Falklands',
          'MP' => 'Northern Mariana Islands',
          'MH' => 'Marshall islands',
          'UM' => 'Minor Outlying Islands United States',
          'SB' => 'Solomon Islands',
          'TC' => 'Turks and Caicos Islands',
          'VG' => 'BVI',
          'VI' => 'Virgin Islands of the United States',
          'AX' => 'Åland islands',
        );
    }
}
