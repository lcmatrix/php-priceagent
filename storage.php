<?php
/**
 * Storage classes for price agent.
 *
 * @author Norman Seidel
 * @version 0.3
 */


/**
 *  Storage class. 
 *
 * @author Norman Seidel
 */ 
class Storage {

    private $db;

    public function __construct() {
    }
    
    public function open() {
        $this->db = new SQLite3('prices.db');
        $this->db->exec('CREATE TABLE IF NOT EXISTS item (id TEXT, link TEXT, targetPrice TEXT, CONSTRAINT pk_item PRIMARY KEY(id) );');
    }
    
    public function close() {
        $this->db->close();
    }
    
    public function findItem($id) {
        $stm = $this->db->prepare("SELECT id, link, targetPrice FROM item WHERE id = :id");
        $stm->bindValue(':id', $id, SQLITE3_TEXT);
        $res = $stm->execute();
        $array = $res->fetchArray();
        $item = new AmazonItem($array[0], $array[1]);
        $res->finalize();
        $stm->close();
        return $item;
    }
    
    public function findAll() {
        $items = array();
        $res = $this->db->query("SELECT * FROM item");
        while($arr = $res->fetchArray()) {
            try {
                $item = Item::createItem($arr[0], $arr[1]);
                $items[] = $item;
            } catch (Exception $e) {
                print("Error creating item from database: $e");
            }
        }
        $res->finalize();
        return $items;
    }
    
    public function saveItem($item) {
        $stm = $this->db->prepare("INSERT INTO item (id, link) VALUES (?, ?)");
        $stm->bindValue(1, sha1($item->getUrl()), SQLITE3_TEXT);
        $stm->bindValue(2, $item->getUrl(), SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
        return $this->db->changes() == 1;
    }
}
?>
