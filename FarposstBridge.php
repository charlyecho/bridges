<?php
/**
* FarposstBridge
* Returns the newest posts of farposst
*
* @name Farposst Bridge
* @homepage http://www.farposst.ru/
* @description Returns the 16 newest posts
* @maintainer charlyecho
* @update 2015-08-08
*
*/
class FarposstBridge extends BridgeAbstract {

  // get data
  public function collectData(array $param) {
    $count = 0;
    $html = file_get_html('http://www.farposst.ru/') or $this->returnError('Could not request Farposst.', 404);

    foreach($html->find('table.border') as $_table) {

      // table website, no id, few classes ... => happy parsing
      $tables       = $_table->find('table');
      $header       = $tables[0]->find('td.capmain');
      $title        = $header[0]->find('b', 0)->innertext;

      $category     = $header[1]->find('a', 0);
      $category_t   = $category->find('b', 0)->innertext;
      $category_uri = $category->getAttribute('href');

      $link         = $tables[1]->find('a', 0)->getAttribute('href');
      $img          = $tables[1]->find('img', 0)->src;

      $author       = $tables[2]->find('a', 0)->innertext;
      $author_uri   = $tables[2]->find('a', 0)->getAttribute('href');

      // round 2 : russian website windows-1251 encoded
      $title        = iconv('WINDOWS-1251', 'UTF-8//TRANSLIT', $title);
      $category_t   = iconv('WINDOWS-1251', 'UTF-8//TRANSLIT', $category_t);
      $author       = iconv('WINDOWS-1251', 'UTF-8//TRANSLIT', $author);

      // item creation
      $item = new \Item();
      $item->uri            = $link;
      $item->thumbnailUri   = $img;
      $item->title          = $title.' by '.$author;
      $item->content = '<a href="' . $item->uri . '">';
      $item->content .= '<img src="' . $item->thumbnailUri . '" />';
      $item->content .= '<br/>'.  $item->title;
      $item->content .= '</a>';
      $item->content .= $author ? '<br/>User : <a href="'.$author_uri.'">'.  $author . '</a>' : null;
      $item->content .= $category_t ? '<br/>Category : <a href="'.$category_uri.'">'.  $category_t . '</a>' : null;

      $this->items[] = $item;
      $count++;
    }
  }

  public function getName() {
    return 'Farposst Bridge';
  }

  public function getURI() {
    return 'http://www.farposst.ru/';
  }

  public function getCacheDuration() {
    return 10800; // 3 hours
  }

}