<?php
/**
 * A small price agent.
 *
 * @author Norman Seidel
 * @version 0.1 alpha
 */

require('config.php');
 
// Ignore warnings (occur in DOMDocument)
error_reporting(E_ALL & ~E_WARNING);

/**
 * Price agent class.
 *
 * @author Norman Seidel
 */
class PriceAgent {

    public function __construct() {
        $this->loadInput();
    }

    private function loadInput() {
        $fd = fopen(INPUT_FILE, 'r');
        if ($fd) {
            while (($buffer = fgets($fd)) !== false) {
                $arr = preg_split('/'.DELIMITER.'/', $buffer);
                //var_dump($arr);
                // todo map
            }
            if (!feof($fd)) {
                echo "Error: unexcepted fgets() fail";
            }
            fclose($fd);
       }
    }
    
    private function createItem($url) {
        return Item::createItem($url);
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
    public abstract function getPriceForItem();
    
    public static function createItem($url) {
        if (preg_match('/amazon/i', $url)) {
            $item = new AmazonItem($url);
        }
        return $item;
    }
    
    /**
     * Fetch the HTML Body for the given URL.
     *
     * @return HTML Body
     */
    protected function getHTML() {
        if(!extension_loaded("curl")) {
            return file_get_contents($this->url,'r');
        }
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_PROXY, PROXY_URL);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
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
    public function getPriceForItem() {
        $htmlBody = $this->getHTML();
        $doc = new DOMDocument();
        $doc->loadHTML($htmlBody);
        $xpath = new DOMXPath($doc);
        $result = $xpath->query('//b[@class="priceLarge"]');
        $price = $result->item(0)->nodeValue;
        $price = str_ireplace("EUR ", "", $price);
        return $price;
    }
}





// TODO: remove this example
$agent = new PriceAgent();
/*
$item = $agent->createItem("http://www.amazon.de/Original-Samsung-Headset-schwarz-passend/dp/B007N7EVFI/ref=sr_1_1?ie=UTF8&qid=1371485569&sr=8-1&keywords=samsung+s2+headset");
var_dump($item);
//$html = getHTML("http://www.amazon.de/Original-Samsung-Headset-schwarz-passend/dp/B007N7EVFI/ref=sr_1_1?ie=UTF8&qid=1371485569&sr=8-1&keywords=samsung+s2+headset");
//$amazon = new AmazonItem("http://www.amazon.de/Original-Samsung-Headset-schwarz-passend/dp/B007N7EVFI/ref=sr_1_1?ie=UTF8&qid=1371485569&sr=8-1&keywords=samsung+s2+headset");

$price = $item->getPriceForItem();
$threshold = "5,0";
$threshold = str_ireplace(",", ".", $threshold);
if ($price < $threshold) {
	echo "ok";
}
*/
?>