<?php
/**
 * @copyright 2013-2019 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Application\Models;
use Blossom\Classes\Url;

class AddressService
{
	/**
	 * Define custom form fields for dealing with AddressServiceCache data
	 *
	 * AddressServiceCache fields can be included in forms, such as search,
	 * and displayed with the rest of the ticket information.
	 *
	 * When the sytem displays ticket information, it will look at this
	 * array for any additional fields of information to display
	 *
	 * When the system draws a search form, a call will be made to get this
	 * description of any custom fields to include.
	 * The description will be used as the label
	 * The formElement controls what HTML form element to render.
	 *		If the formElement is "select", then a drop down will be created,
	 *		populated with all possible values from the addressServiceCache
	 *
	 *		All other form elements will be rendered as a plain text input
	 */
	public static $customFieldDescriptions = [
		'neighborhoodAssociation' => [
			'description' => 'Neighborhood Association',
			'formElement' => 'select'
		],
		'township' => [
			'description' => 'Township',
			'formElement' => 'select'
		]
	];

	/**
	 * Loads the data from your address service for the location
	 *
	 * This function is where you can add any extra, custom fields that this
	 * application will keep track of.
	 *
	 * Your address system should have only one entry matching the location string.
	 * If there is not exactly one entry, the returned data will be empty
	 * This can be used to see if the location string is a valid address
	 * in your address system
	 *
	 * It's important to match $data fieldnames with Ticket fieldnames.
	 * When this data is given to a Ticket, any fields that have the same name as Ticket
	 * properties will update the appropriate Ticket property.
	 *
	 * @param  string $location
	 * @return array
	 */
	public static function getLocationData(string $location): ?array
	{
        $data     = [];
		$location = trim($location);
		$parse    = self::parseAddress($location);

		if (defined('ADDRESS_SERVICE') && isset($parse['street_number'])) {
			$url = new Url(ADDRESS_SERVICE.'/addresses');
			$url->format  = 'json';
			$url->address = $location;

			$searchResult = self::loadJsonResponse($url->__toString());
			if ($searchResult && count($searchResult)==1) {
                $info = self::addressInfo((int)$searchResult[0]['id']);
                if ($info) {
                    $data = self::extractAddressData($info, $parse);
                }
			}
		}
		return $data;
	}

	public static function searchAddresses(string $query): array
	{
        $query   = trim($query);
        $results = [];
        if (defined('ADDRESS_SERVICE')) {
			$url = new Url(ADDRESS_SERVICE.'/addresses');
			$url->format  = 'json';
			$url->address = $query;

			$searchResult = self::loadJsonResponse($url->__toString());
			if ($searchResult) {
                foreach ($searchResult as $address) {
                    $data = [
                        'location'  => $address['streetAddress'],
                        'addressId' => $address['id'           ],
                        'city'      => $address['city'         ]
                    ];
                }
			}
        }
        return $results;
	}

	public static function addressInfo(int $address_id): ?array
	{
        if (defined('ADDRESS_SERVICE')) {
            $url = ADDRESS_SERVICE."/addresses/$address_id?format=json";
            return self::loadJsonResponse($url);
        }
	}

	/**
	 * @param  string $address
	 * @return array            Data array from json web service
	 */
	public static function parseAddress(string $address): ?array
	{
		if (defined('ADDRESS_SERVICE')) {
			$url          = new Url(ADDRESS_SERVICE.'/addresses/parse');
			$url->format  = 'json';
			$url->address = $address;
			return self::loadJsonResponse($url->__toString());
		}
	}

	/**
	 * @see https://github.com/City-of-Bloomington/master_address/blob/master/src/Domain/Addresses/UseCases/Info/InfoResponse.php
	 * @see https://github.com/City-of-Bloomington/master_address/blob/master/src/Domain/Addresses/UseCases/Parse/ParseResponse.php
	 *
	 * @param  array $info   The  InfoResponse from Master Address
	 * @param  array $parse  The ParseResponse from Master Address
	 * @return array
	 */
	private static function extractAddressData(array $info, array $parse): array
	{
		$data = [
            'location'  => $info['address']['streetAddress'],
            'addressId' => $info['address']['id'           ],
            'city'      => $info['address']['city'         ],
            'state'     => $info['address']['state'        ],
            'zip'       => $info['address']['zip'          ],
            'latitude'  => $info['address']['latitude'     ],
            'longitude' => $info['address']['longitude'    ],
            'township'  => $info['address']['township_name'],
        ];

		// See if there's a neighborhood association
		foreach ($info['purposes'] as $p) {
            if ($p['purpose_type'] == 'NEIGHBORHOOD ASSOCIATION') {
                $data['neighborhoodAssociation'] = $p['name'];
                break;
            }
		}

		// See if this is a subunit
		if ($parse['subunitIdentifier']) {
            foreach ($info['subunits'] as $s) {
                if ($s['identifier'] == $parse['subunitIdentifier']) {
                    $data['subunit_id'] = $s['id'];
                    $data['location'  ] = "$data[location] $s[type_code] $s[identifier]";
                    break;
                }
            }
		}
		return $data;
	}

    private static function loadJsonResponse(string $url): ?array
    {
        $response = Url::get($url);
        if ($response) {
            $json = json_decode($response, true);
            return $json;
        }
    }
}
