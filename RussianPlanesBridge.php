<?php
/**
 * RussianPlanesBridge
 * Returns the newest posts of russian planes
 *
 * @name RussianPlanes Bridge
 * @homepage http://russianplanes.net/
 * @description Returns the 50 newest posts
 * @maintainer charlyecho
 * @update 2015-08-08
 *
 */
class RussianPlanesBridge extends BridgeAbstract {

  // get data
  public function collectData(array $param) {
    $count = 0;
    $html = file_get_html('http://russianplanes.net/f!b!t!a52!c!d!l20!g!m!s0!u!r!k!v!h!i!p1!reg!ser!n') or $this->returnError('Could not request Farposst.', 404);

    date_default_timezone_set('Europe/Moscow');

    $date = null;

    $list =  $html->find('#scrdiv > tr');
    foreach ($list as $nb => $line) {
      if ($line->getAttribute('class') == 'photoheader') {
        $date = $line->find('td', 0)->getAttribute('title');
        continue;
      }

      if ($line->getAttribute('style') != 'background-color: #fff; ') {
        continue;
      }

      $link = $line->find('a', 0)->href;
      $id = explode('id', $link)[1];
      $title = $id;

      $_a = $line->find('a.userNode', 0);
      $author = $_a ? $_a->innertext : null;
      $author_uri = $_a ? $_a->getAttribute('href') : null;

      $img = $line->find('img', 0)->src;
      $img_hd = str_replace('-200', '', $img);

      // item creation

      $item = new \Item();
      $item->uri            = $link;
      $item->thumbnailUri   = $img;
      $item->title          = $title.' by '.$author;
      $item->content = '<a href="' . $item->uri . '">';
      $item->content .= '<img src="' . $img_hd . '" />';
      $item->content .= '<br/>'.  $item->title;
      $item->timestamp = strtotime($date);
      $item->content .= '</a>';
      $item->content .= $author && $author_uri ? '<br/>User : <a href="'.$author_uri.'">'.  $author . '</a>' : null;

      $this->items[] = $item;
      $count++;
    }
  }

  public function getName() {
    return 'Russian Planes Bridge';
  }

  public function getURI() {
    return 'http://russianplanes.net/';
  }

  public function getCacheDuration() {
    return 3600; // 1 hour
  }

}