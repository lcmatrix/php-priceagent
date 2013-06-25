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
    /**
     * Array with all items.
     */
    private $itemList = array();
    
    /**
     * Map with target price per item. The map key is the item ID.
     */
    private $targetPricePerItem = array();

    /**
     * Constructor.
     */
    public function __construct() {
        $this->loadInput();
    }

    /**
     * Reads input file with links and target prices. Input file is configured in constant INPUT_FILE.
     */
    private function loadInput() {
        $fd = fopen(INPUT_FILE, 'r');
        if ($fd) {
            while (($buffer = fgets($fd)) !== false) {
                $arr = preg_split('/'.DELIMITER.'/', $buffer);
                try {
                    $item = Item::createItem($arr[0]);
                    array_push($this->itemList, $item);
                    $pair = array($item->getId() => $arr[1]);
                    $this->targetPricePerItem = array_merge($this->targetPricePerItem, $pair);
                } catch (Exception $e) {
                    echo "Could not determine type for: " . $arr[0] . "\n";
                }
            }
            if (!feof($fd)) {
                echo "Error: unexcepted fgets() fail";
            }
            fclose($fd);
       }
    }
    
    /**
     * Gives a list/array with all items from the input file / store.
     *
     * @return list/array with all items
     */
    public function getItemList() {
        return $this->itemList;
    }
    
    /**
     * Checks all items whether the target price has been reached and sends an e-mail for those items.
     */
    public function check() {
        foreach ($this->itemList as $item) {
            $price = $item->getPrice();
            $targetPrice = $this->targetPricePerItem[$item->getId()];
            if ($price <= $targetPrice) {
                $ret = $this->sendMail($item);
                if ($ret == false) {
                    echo "ERROR while sending e-mail for item: " . $item->getUrl();
                }
            }
        }
    }
    
    /**
     * Send e-mail with information that a target price has been reached.
     */
    private function sendMail($item) {
        $body = str_ireplace("{0}", $item->getUrl(),  MAIL_BODY);
        $body = str_ireplace("{1}", $item->getPrice(),  $body);
        $headers = 'From: ' . MAIL_FROM;
        mail(MAIL_TO, MAIL_SUBJECT, $body, $headers);
    }
}

/**
 * Abstract item class.
 *
 * @author Norman Seidel
 */
abstract class Item {
    private $id = "";
    private $url = "";
    
    /**
     * Constructor.
     *
     * @param $url URL for this item
     */
    public function __construct($url) {
        $this->id = sha1($url);
        $this->url = $url;
    }
    
    /**
     * Getter for the item ID.
     *
     * @return ID
     */
    public function getId() {
        return $this->id;
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
    public abstract function getPrice();
    
    /**
     * Creates a concrete item object or throws an exception if the concrete type could not be determined.
     *
     * @return new item
     * @throws Exception if the type could not be determined
     */
    public static function createItem($url) {
        if (preg_match('/amazon/i', $url)) {
            $item = new AmazonItem($url);
            return $item;
        }
        throw new Exception("Couldn't determine item type!");
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
    public function getPrice() {
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
$agent->check();
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