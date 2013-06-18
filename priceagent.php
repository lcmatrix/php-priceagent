<?php
/**
 * A small price agent.
 *
 * @author Norman Seidel
 * @version 0.1 alpha
 */

/**
 * Constant for input file with links and threasholds.
 */
define("INPUT_FILE", "agent_input.txt");

// Ignore warnings (occur in DOMDocument)
error_reporting(E_ALL & ~E_WARNING);

/**
 * Price agent class.
 *
 * @author Norman Seidel
 */
class PriceAgent {

    public function loadInput($file) {
        $fd = fopen($file, 'r');
        //fgets($fd);
        fclose($fd);
    }
    
    private function createItem($url) {
    }
}

/**
 * Abstract item class.
 *
 * @author Norman Seidel
 */
abstract class Item {
    private $url = "";
    
    /**
     * Constructor.
     *
     * @param $url URL for this item
     */
    public function __construct($url) {
        $this->url = $url;
    }
    
    /**
     * Returns the URL for this item.
     *
     * @return URL
     */
    public function getUrl() {
        return $this->url;
    }
    
    /**
     * Get price for this item.
     * 
     * @return price
     */
    public abstract function getPriceForItem($htmlBody);
}

/**
 * Implementation for an Amazon item.
 *
 * @author Norman Seidel
 */
class AmazonItem extends Item {
    
    /**
     * Get price for this item.
     * 
     * @return price
     */
    public function getPriceForItem($htmlBody) {
        $doc = new DOMDocument();
        $doc->loadHTML($htmlBody);
        $xpath = new DOMXPath($doc);
        $result = $xpath->query('//b[@class="priceLarge"]');
        $price = $result->item(0)->nodeValue;
        $price = str_ireplace("EUR ", "", $price);
        return $price;
    }
}


/**
 * Fetch the HTML Body for the given URL.
 *
 * @param $url URL to read
 * @return HTML Body
 */
function getHTML($url) {
    if(!extension_loaded("curl")) {
        return file_get_contents($url,'r');
    }
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_PROXY, "http://proxy.mms-dresden.telekom.de:8080");
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}


// TODO: remove this example
$html = getHTML("http://www.amazon.de/Original-Samsung-Headset-schwarz-passend/dp/B007N7EVFI/ref=sr_1_1?ie=UTF8&qid=1371485569&sr=8-1&keywords=samsung+s2+headset");
$amazon = new AmazonItem("http://www.amazon.de/Original-Samsung-Headset-schwarz-passend/dp/B007N7EVFI/ref=sr_1_1?ie=UTF8&qid=1371485569&sr=8-1&keywords=samsung+s2+headset");
$price = $amazon->getPriceForItem($html);
$threshold = "5,0";
$threshold = str_ireplace(",", ".", $threshold);
if ($price < $threshold) {
	echo "ok";
}
?>