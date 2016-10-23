<?php
/*
    A simple PHP class to query the Gandi API domain.price.list()
    with a list of domains based on a few popular extensions to
    obtain price & availability & phase in one API call.

    Requirements:
        - A Gandi API key
        - XML_RPC2 from PHP PEAR

    Usage example:

        include 'DomainPrice.php';
        apikey = 'your-Gandi-API-key';
        $gapi = new DomainPrice($apikey);
        $gapi->doSearch("anydomainlabel"); // will search those extensions as defined in the class
        $results = $gapi->getCost(); // get the cost of the resulting available domains, returns cost & phase
        foreach ($results as $r) {
            echo $r['fqdn'] . "\t" . $r['available'] . " " . $r['cost'] . "\t" . $r['cost_vat'] . "\n";
        }

    Note:
        - Currency and price grid may be set with the setCurrency, setGrid function.
        - To include the VAT (under your reseller account) in the cost calculation enable it in the class:
            $cost_vat_calculation = true;
            $currency_has_decimals = true; (if your currency has decimal points)
        - Update $search_extensions to include other extensions.

*/

require_once 'XML/RPC2/Client.php';

class DomainPrice
{
    protected $api_key= '';
    protected $domain_price_api;
    protected $search_time_seconds = 20;
    protected $search_extensions = array('.pizza','.tw','.taipei','.com','.com.tw','.blog','.online');
    protected $search_results = array();
    protected $domain_cost_list = array();

    protected $grid = 'A';
    protected $currency = 'USD';
    protected $cost_vat_calculation = false;
    protected $currency_has_decimals = true;

    function getSearchResults()
    {
        return $this->search_results;
    }

    function getCost($includeUnavalable = false)
    {
        $this->domain_cost_list = array();
        foreach ($this->search_results as $search_result) {
            if ($search_result["available"] === "available") {
                foreach ($search_result["prices"] as $price_item_prices) {
                    $tax_rate = ($price_item_prices['taxes']['rate']);
                    if ($search_result["current_phase"] === $price_item_prices["action"]["param"]["tld_phase"]) {
                        foreach ($price_item_prices["unit_price"] as $unit_prices) {
                            $price = $price_vat = $unit_prices['price'];
                            if ($this->cost_vat_calculation == true)
                                $price_vat += $price_vat * ($tax_rate/100);
                                if ($this->currency_has_decimals == false)
                                    $price_vat = round($price_vat);

                            $cost_element = array(
                                'fqdn' => $search_result["extension"],
                                'available' => $search_result["available"],
                                'phase' => $search_result["current_phase"],
                                'cost' => $price,
                                'cost_vat' => $this->cost_vat_calculation ?  $price_vat : 0
                            );
                            array_push($this->domain_cost_list,$cost_element);

                            if ($unit_prices['special_op'] == true)
                                break;
                        }
                    }
                }
            }
            else {
                if ($includeUnavalable) {
                    $cost_element = array(
                     'fqdn' => $search_result["extension"],
                     'available' => $search_result["available"],
                     'phase' => $search_result["current_phase"],
                    );
                    array_push($this->domain_cost_list,$cost_element);
                }
            }
        }
        return $this->domain_cost_list;
    }

    function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    function setGrid($grid)
    {
        $this->grid = $grid;
    }

    function doSearch($search_label)
    {
        $fqdn_list = array();

        foreach ($this->search_extensions as $extension) {
            array_push($fqdn_list,$search_label . $extension);
        }

        $search_results = array();

        for ($i = 0;$i<$this->search_time_seconds;$i++) {

            $price_args = array(
                'tlds' => $fqdn_list,
                'grid' => $this->grid,
                'currency' => $this->currency,
                'action' => 'create',
            );
          
            $price_listing = $this->domain_price_api->list( $this->api_key, $price_args);

            foreach ($price_listing as $price_item) {
                if ($price_item["available"] !== "pending") {
                    array_push($this->search_results,$price_item);
                        foreach (array_keys($fqdn_list, $price_item['extension'], true) as $key) {
                            unset($fqdn_list[$key]);
                        }
                }
            }

            if (sizeof($fqdn_list) == 0)
                break;

              sleep(1);
        }

        return true;
    }

    function __construct($api_key)
    {
        $this->api_key = $api_key;
        $this->domain_price_api = XML_RPC2_Client::create (
            'https://rpc.gandi.net/xmlrpc/',
            array('prefix' => 'domain.price.','sslverify' => False)
        );
    }
}

?>
