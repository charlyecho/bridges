<?php
/**
* VDMBridge
* Returns the newest posts of VDM
*
* @name VDM Bridge
* @homepage http://viedemerde.fr/
* @description Returns the 35 newest posts
* @maintainer charlyecho
* @update 2015-08-08
*
*/
class VDMBridge extends BridgeAbstract {

  // get data
  public function collectData(array $param) {
    $count = 0;
    $html = file_get_html('http://www.viedemerde.fr/') or $this->returnError('Could not request VDM.', 404);
    date_default_timezone_set('Europe/Paris');

    foreach ($html->find('div.article') as $_div) {
      $content = "";

      $link     = $_div->find('a', 0)->getAttribute('href');
      $title    = $_div->getAttribute('id');
      $p = $_div->find('p', 0);
      foreach ($p->find('a') as $_a ) {
        $content .= $_a->plaintext;
      }

      // date
      $date = $_div->find('div.right_part', 0)->find('p', 1)->plaintext;
      $date = explode("-", $date)[0];
      $date = str_replace(array('Le', 'à'), '', $date);
      $date = str_replace('/', '-', $date);
      $date = strtotime($date);

      // item creation
      $item = new \Item();
      $item->thumbnailUri   = "http://cdn7.viedemerde.fr/fmylife/images/toplogo.fr.png";
      $item->uri            = "http://www.viedemerde.fr".$link;
      $item->title          = $title;
      $item->timestamp = $date;
      $item->content = $content;

      $this->items[] = $item;
      $count++;
    }
  }

  public function getName() {
    return 'VDM Bridge';
  }

  public function getURI() {
    return 'http://www.viedemerde.fr/';
  }

  public function getCacheDuration() {
    return 3600; // 3 hours
  }

}