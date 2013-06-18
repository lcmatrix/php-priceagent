<?php
/**
 * A small price agent.
 *
 * @author Norman Seidel
 * @version 0.1 alpha
 */

/**
 * Get price for an object.
 * 
 * @return price
 */
function getPriceForObject($url) {
    $body = file_get_contents($url,'r');
    $doc = new DOMDocument();
    $doc->loadHTML($body);
    $xpath = new DOMXPath($doc);
    $result = $xpath->query('//b[@class="priceLarge"]');
    $price = $result->item(0)->nodeValue;
    $price = str_ireplace("EUR ", "", $price);
    return $price;
}

error_reporting(E_ALL & ~E_WARNING);

// TODO: remove this example
$price = getPriceForObject("http://www.amazon.de/Original-Samsung-Headset-schwarz-passend/dp/B007N7EVFI/ref=sr_1_1?ie=UTF8&qid=1371485569&sr=8-1&keywords=samsung+s2+headset");
$threshold = "5,0";
$threshold = str_ireplace(",", ".", $threshold);
if ($price < $threshold) {
	echo "ok";
}
?>