<?php

class MemcachedManager {

    private $server = 'localhost';
    private $port = 11211;

    private function send($command) {

        $sock = @fsockopen($this->server,$this->port);
        if (!$sock){
            die("Cant connect to:".$this->server.':'.$this->port);
        }

        fwrite($sock, $command."\r\n");

        $buf='';
        while ((!feof($sock))) {
            $buf .= fgets($sock, 256);
            if (strpos($buf,"END\r\n")!==false){ // stat says end
                break;
            }
            if (strpos($buf,"DELETED\r\n")!==false || strpos($buf,"NOT_FOUND\r\n")!==false){ // delete says these
                break;
            }
            if (strpos($buf,"OK\r\n")!==false){ // flush_all says ok
                break;
            }
        }
        fclose($sock);

        return $buf;
    }

    private function getKeyFromItem($item_no) {
        $string = $this->send('stats cachedump ' . $item_no . ' 100');
        if (preg_match("/ITEM (.*?) /", $string, $matches)) {
            return $matches[1];
        }
        return false;
    }

    public function getItems() {
        $items = array();
        $lines = explode("\r\n", $this->send('stats items'));
        foreach($lines as $line) {
            if (preg_match("/STAT items:(?P<digit>\d+):number/", $line, $matches)) {
                if (isset($matches['digit'])) {
                    $item = $this->getKeyFromItem($matches['digit']);
                    if ($item!==false) $items[]=$item;
                }
            }
        }
        return $items;
    }
}
