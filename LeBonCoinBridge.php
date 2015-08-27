<?php
/**
 * RssBridgeLeBonCoin
 * Search LeBonCoin for most recent ads in a specific region and topic.
 * Returns the most recent classified ads in results, sorting by date (most recent first).
 * Region identifiers : alsace, aquitaine, auvergne, basse_normandie, bourgogne, bretagne, centre,
 *     champagne_ardenne, corse, franche_comte, haute_normandie, ile_de_france, languedoc_roussillon,
 *     limousin, lorraine, midi_pyrenees, nord_pas_de_calais, pays_de_la_loire, picardie,
 *     poitou_charentes, provence_alpes_cote_d_azur, rhone_alpes, guadeloupe, martinique, guyane, reunion.
 * 2014-07-22
 *
 * @name LeBonCoin
 * @homepage http://www.leboncoin.fr
 * @description Returns most recent results from LeBonCoin for a region and a keyword.
 * @maintainer 16mhz
 * @use1(c="Category", r="Region identifier", k="Keyword")
 */

class LeBonCoinBridge extends BridgeAbstract{

  public function collectData(array $param) {

    $html = '';
    $category = $param["c"] ? $param["c"] : "annonces";
    $keyword = str_replace(" ", "+", $param["k"]);
    $link = 'http://www.leboncoin.fr/' . $category . '/offres/' . $param["r"] . '/?f=a&th=1&q=' . $keyword;
    $html = file_get_html($link) or $this->returnError('Could not request LeBonCoin.', 404);

    $list = $html->find('.list-lbc', 0);
    $tags = $list->find('a');

    $cur_day = date("d");
    $cur_month = date("m");
    $cur_year = date("Y");

    foreach($tags as $element) {
      $item = new \Item();
      $item->uri = $element->href;
      $title = $element->getAttribute('title');


      $src = $element->find('div.image', 0)->find('img', 0);//->getAttribute('src')
      if (is_object($src)) {
        $src = is_object($src) ? $src->getAttribute('src') :  "http://static.leboncoin.fr/img/logo_25.png";
      }

      $content = '<img src="' . $src . '" alt="thumbnail">';

      // date hack
      $day = $cur_year."-".$cur_month."-".$cur_day;
      $day_str = utf8_encode($element->find('div.date', 0)->find('div', 0)->getAttribute('innertext'));
      if ($day_str == "Aujourd'hui") {
        $day = $cur_year."-".$cur_month."-".$cur_day;
      }
      elseif ($day_str == "Hier") {
        $date = strtotime($day);
        $date = strtotime("-1 day", $date);
        $day = date('Y-m-d', $date);
      }
      elseif(strpos($day_str, " ") !== false) {

        $ex = explode(" ", $day_str);
        $m = array("01" => "jan", "02" => "fév", "03" => "mar", "04" => "avr", "05" => "mai", "06" => "jui", "07" => "jui", "08" => "aou", "09" => "sep", "10" => "oct", "11" => "nov", "12" => "déc");
        $day = str_pad($ex[0], 2, "0", STR_PAD_LEFT);
        $month = array_search($ex[1], $m);
        $year = $cur_year;
        if (($month == "12" || $month == "11" || $month == "10") && $cur_month < 10) {
          $year = $cur_year-1;
        }
        $day = $year."-".$month."-".$day;
      }
      $time = $element->find('div.date', 0)->find('div', 1)->getAttribute('innertext').":00";
      $date = strtotime($day.' '.$time);

      $detailsList = $element->find('div.detail', 0);

      for ($i = 1; $i < 4; $i++) {
        $line = $detailsList->find('div', $i);
        $content .= $line;
      }

      $item->timestamp	= $date;
      $item->title = $title . ' - ' . $detailsList->find('div', 3);
      $item->content = $content;
      $this->items[] = $item;
    }
  }

  public function getName(){
    return 'LeBonCoin';
  }

  public function getURI(){
    return 'http://www.leboncoin.fr';
  }

  public function getCacheDuration(){
    return 0; // 1 hour
  }
}