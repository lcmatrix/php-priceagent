<?php
/**
 * A small price agent.
 *
 * @author Norman Seidel
 * @version 0.2
 */

require('config.php');
require('storage.php');
 
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
                if ($ret != true) {
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
        return mail(MAIL_TO, MAIL_SUBJECT, $body, $headers);
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
     * @param $id ID for this item
     * @param $url URL for this item
     */
    public function __construct($id, $url) {
        $this->id = $id;
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
     * @param $url URL of the item
     * @return new item
     * @throws Exception if the type could not be determined
     */
    public static function createItem($url) {
        if (preg_match('/amazon/i', $url)) {
            $item = new AmazonItem(sha1($url), $url);
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

// call price agent
$agent = new PriceAgent();
$agent->check();
?>
