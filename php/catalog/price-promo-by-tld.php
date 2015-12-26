#!/usr/bin/php
#
# PHP example to list all extensions currently in promotion at Gandi
# Requirements: PEAR XML_RPC2 package
# Reference: http://doc.rpc.gandi.net/catalog/reference.html
# 
# Limitations:
# - Will not find promotion on second level top level domain names.
#   Do to so, query the Catalog API directly with a FQDN.
# 
<?php
require_once 'XML/RPC2/Client.php';

// See http://doc.rpc.gandi.net/hosting/usage.html#connect-to-the-api-server
$apikey = 'my 24-character API key';
$catalog_api = XML_RPC2_Client::create('https://rpc.gandi.net/xmlrpc/',
	array('prefix' => 'catalog.', 'sslverify' => False)
);

// Parameters for catalog.list()
$query_parms = array (
	"product" => array ("type" => "domain"),
	"action"  => array ("duration" => 1, 'name' => 'create')
);
$currency = 'EUR';
$grid = 'A';

$result = $catalog_api->list($apikey, $query_parms, $currency, $grid);

/* products which have a special catalog price have 'special_op' as 'true'
   as part of the additional unit_price array element in our result */

$promotions = array();

foreach($result as $res) {

// Necessary loop as 'special_op' isn't always the first array element.
	$has_promotion = false;
        foreach($res['unit_price'] as $unit) {
                if ($unit['special_op'] == true) {
                        $special_price = $unit['price'];
			$has_promotion = true;
		}
		
                else
                        $regular_price = $unit['price'];
        }

        if ($has_promotion) {
                $promo_item = array( 'tld' => $res['product']['description'],
                                'special_price' => $special_price,
                                'regular_price' => $regular_price);
                array_push($promotions,$promo_item);
        }

}

// sort by domain name extension
sort ($promotions);

// print result
echo "Current Gandi Promotions. Action: create (1 year), Grid: " . $grid . ", Currency: ". $currency . "\n";
printf ("Extension            | Special Price | Regular Price\n");
printf ("----------------------------------------------------\n");
foreach ($promotions as $promo) {
	printf ("%-20s | %13.2f | %13.2f\n", $promo['tld'], $promo['special_price'], $promo['regular_price']);
}
?>
