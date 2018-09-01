<?php

/**
 * Created by PhpStorm.
 * User: Grabe Grabe
 * Date: 8/20/2018
 * Time: 5:30 AM
 */
class Scraper
{
    public $debug = TRUE;
    protected $db_pdo;

    public function exportProducts(){
        $pdo = $this->getPdo();
        $sql = 'SELECT *
                FROM `inorganic` ORDER BY `id` DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;
        return $result;
    }

    public function exportInputs(){
        $pdo = $this->getPdo();
        $sql = 'SELECT `keyword`
                FROM `inorganic1` ORDER BY `id` DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        $pdo = null;
        return $result;
    }

    public function insertKeyword($keyword){
        $pdo = $this->getPdo();
        $sql = 'SELECT count(`id`) AS rowCount FROM `inorganic1` WHERE `keyword` = "'.$keyword.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->fetch(PDO::FETCH_ASSOC)['rowCount'] == 0){
            $sql = 'INSERT INTO `inorganic1` SET `keyword` = "'.$keyword.'"';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        $pdo = null;
    }

    public function getKeywords($offset = 0, $limit = 10){
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `inorganic1` WHERE `status` = 0 ORDER BY id ASC LIMIT '.$offset.','.$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
            $sql = 'UPDATE `inorganic1` SET `status` = 1 WHERE `id` = '.$row['id'];
            $stmtU = $pdo->prepare($sql);
            $stmtU->execute();
        }
        $pdo = null;
        return $result;
    }

    public function isAsinExist($asin, $date){
        $pdo = $this->getPdo();
        $sql = 'SELECT * FROM `inorganic` WHERE `asin` = "'.$asin.'" AND `time` LIKE "'.$date.'%"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdo = null;
        return $result;
    }

    public function insertAsinLink($id, $url){
        $pdo = $this->getPdo();
        $sql = 'UPDATE `asins` SET `asin_review_url` = "'.$url.'" WHERE `id` = '.$id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function addProduct($data){
        $pdo = $this->getPdo();
        $sql = 'INSERT INTO `inorganic` SET `position` = '.$data['position'].', `keyword` = "'.$data['keyword'].'", `brand` = "'.$data['brand'].'", `message` = "'.$data['message'].'", `asin` = "'.$data['asin'].'", `title` = "'.$data['title'].'", `locale` = "'.$data['locale'].'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }
    public function addProducts($data, $date){
        $pdo = $this->getPdo();
        $values = '';
        for($x = 0; $x < count($data); $x++){

            if($x == count($data) -1 ){
                $values .= '('.$data[$x][0].',
                "'.$data[$x][1].'",
                "'.$data[$x][2].'",
                "'.$data[$x][3].'",
                "'.$data[$x][4].'",
                "'.$data[$x][5].'",
                "'.$data[$x][6].'",
                "'.$date.'"
                )';
            }else{
                $values .= '('.$data[$x][0].',
                "'.$data[$x][1].'",
                "'.$data[$x][2].'",
                "'.$data[$x][3].'",
                "'.$data[$x][4].'",
                "'.$data[$x][5].'",
                "'.$data[$x][6].'",
                "'.$date.'"
                ),';
            }
        }


        $sql = 'INSERT INTO `inorganic`
                  (`position`, `keyword`, `brand`, `message`, `asin`, `title`, `locale`, `date_executed`)
                VALUES '.$values.'
               ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    function reset(){
        $pdo = $this->getPdo();
        $sql = 'UPDATE `inorganic1` SET status = 0;';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    function checkReviewId($reviewId){
        $pdo = $this->getPdo();
        $sql = 'SELECT count(`id`) as matchCount FROM `reviews` WHERE `review_id` = "'.$reviewId.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['matchCount'];
        $pdo = null;

        return $count;
    }

    public function updateTotalReviewCount($id, $locale, $count){
        $pdo = $this->getPdo();
        $sql = 'SELECT count(id) as rowCount, id FROM `total_reviews` WHERE `asins_id` = '.$id.' AND `locale` = "'.$locale.'"';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)['rowCount'] == 0){
            $sql = 'INSERT INTO `total_reviews` SET `asins_id` = '.$id.', `locale` = "'.$locale.'", `total_review_count` = '.$count;
        }else{
            $sql = 'UPDATE  `total_reviews` SET `asins_id` = '.$id.', `locale` = "'.$locale.'", `total_review_count` = '.$count .' WHERE `id` = '.$stmt->fetch(PDO::FETCH_ASSOC)['id'];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pdo = null;
        return true;
    }

    public function curlTo($url){

        $port1 = '56362';
        $port2 = '43848';
        $proxy[] = array(
            '213.184.110.82',
            '213.184.112.53',
            '213.184.114.168',
            '213.184.114.175',
            '196.16.224.156',
            '196.16.224.158',
            '196.16.224.168',
            '196.16.224.170',
            '196.16.246.146',
            '196.16.246.147',
            '196.16.246.157',
            '196.16.246.99'
        );
        $proxy[] = array(
            '213.184.109.142',
            '213.184.110.15',
            '213.184.112.145',
            '213.184.114.178',
            '196.16.224.25',
            '196.16.246.29'
        );

        $proxyIndex = rand(0, 1);
        if($proxyIndex == 0){
            $port = $port1;
        }else{
            $port = $port2;
        }

        $ip = $proxy[$proxyIndex][mt_rand(0,count($proxy) - 1)];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
            CURLOPT_PROXY => $ip,
            CURLOPT_PROXYPORT => $port,
            CURLOPT_PROXYUSERPWD => 'amznscp:dfab7c358',
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Postman-Token: 85969a77-227f-4da2-ab22-81feaa26c0c4"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return array('html' => $err);
        } else {
            return array('html' => $response, 'ip' => $ip);
        }
    }

    public function getPdo()
    {
        if (!$this->db_pdo)
        {
            if ($this->debug)
            {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            }
            else
            {
                $this->db_pdo = new PDO(DB_DSN, DB_USER, DB_PWD);
            }
        }
        return $this->db_pdo;
    }
}