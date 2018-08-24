<?php
require 'Model/Init.php';
require 'Model/Scraper.php';
require 'simple_html_dom.php';
$scraper = new Scraper();
$locale = 'it';
$keywords = $scraper->getKeywords();
$dateNow = date('Y-m-d');


foreach($keywords as $row){
    $lists = array();
    $keyword = $row['keyword'];
    $id = $row['id'];
    $url = "https://www.amazon.$locale/s?field-keywords=$keyword";
    echo $keyword."\n";
    $htmlData = $scraper->curlTo($url);
    if($htmlData['html']){
        $html = str_get_html($htmlData['html']);
        if($html){

            // Banner Ads
            $bannerContainer = $html->find('.sky', 0);
            if($bannerContainer){
                $block = $bannerContainer->find('.block', 0);
                if($block){
                    $brand = $block->find('#hsaSponsoredByBrandName', 0)->plaintext;
                    $branding = $block->find('.desktopSparkle__branding', 0);
                    if($branding){
                        $message = $branding->find('.block', 1)->plaintext;
                        $lists[] = array(
                            1,
                            $keyword,
                            trim($brand),
                            trim($message),
                            '',
                            '',
                            $locale
                        );
                    }

                }
            }

            // end banner adds

            // regular add
            $s = 2;
            $resultsCol = $html->find('#resultsCol', 0);
            if($resultsCol){
                $itemList = $resultsCol->find('.s-result-item');
                if(count($itemList) > 0){
                    if($s <= 7){
                        for($i = 0; $i < count($itemList); $i++){
                            $asin = $itemList[$i]->getAttribute('data-asin');
                            $sponsored = $itemList[$i]->find('h5', 0);
                            $title = ($itemList[$i]->find('.s-access-title', 0) ? $itemList[$i]->find('.s-access-title', 0)->plaintext : '');
                            if($sponsored){
                                $brand = $itemList[$i]->find('.a-color-secondary', 1)->plaintext;
                                $lists[] = array(
                                    $s,
                                    $keyword,
                                    trim($brand),
                                    '',
                                    trim($asin),
                                    trim($title),
                                    $locale
                                );
                                $s++;
                            }
                        }
                    }
                }
            }
        }
    }

    if(count($lists) > 0){
        $scraper->addProducts( $lists);
    }
    sleep(mt_rand(1, 3));
}

function translateMonth($month, $locale){
    switch ($locale){
        case 'it':
            switch ($month){
                case 'gennaio':
                    return 'january';
                break;
                case 'febbraio':
                    return 'february';
                    break;
                case 'marzo':
                    return 'march';
                    break;
                case 'aprile':
                    return 'april';
                    break;
                case 'maggio':
                    return 'may';
                    break;
                case 'giugno':
                    return 'june';
                    break;
                case 'luglio':
                    return 'july';
                    break;
                case 'agosto':
                    return 'august';
                    break;
                case 'settembre':
                    return 'september';
                    break;
                case 'ottobre':
                    return 'october';
                    break;
                case 'novembre':
                    return 'november';
                    break;
                case 'dicembre':
                    return 'december';
                    break;
            }
        break;
    }
}