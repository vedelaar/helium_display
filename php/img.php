<?php

class helium
{
  public $proxy = false;
  public $gd;
  public $black;
  public $white;
  public $gray;
  public $font = "./LiberationSans-Bold.ttf";

  public $canvas_width = 960;
  public $canvas_height = 540;

  public function __construct($w = 960, $h = 540) {
    $this->proxy = @$_SERVER["http_proxy"];
    $this->canvas_width = $w;
    $this->canvas_height = $h;
    $this->gd = @imagecreate($this->canvas_width, $this->canvas_height)
        or die("Cannot Initialize new GD image stream");
    imageantialias($this->gd, true);
    $this->black = imagecolorallocate($this->gd, 0, 0, 0);
    $this->white = imagecolorallocate($this->gd, 255, 255, 255);
    $this->gray = imagecolorallocate($this->gd, 128,128,128);
  }

  function time_elapsed_string($datetime, $full = false) {
      $now = new DateTime;
      $ago = new DateTime($datetime);
      $diff = $now->diff($ago);

      $diff->w = floor($diff->d / 7);
      $diff->d -= $diff->w * 7;

      $string = array(
          'y' => 'year',
          'm' => 'month',
          'w' => 'week',
          'd' => 'day',
          'h' => 'hour',
          'i' => 'minute',
          's' => 'second',
      );
      foreach ($string as $k => &$v) {
          if ($diff->$k) {
              $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
          } else {
              unset($string[$k]);
          }
      }
  
      if (!$full) $string = array_slice($string, 0, 1);
      return $string ? implode(', ', $string) . ' ago' : 'just now';
  }

    function imagettftextSp($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0)
    {        
        //if ($spacing == 0)
        //{
        //    imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
        //}
        //else
        //{
            $temp_x = $x;
            $temp_y = $y;
            for ($i = 0; $i < strlen($text); $i++)
            {
                imagettftext($image, $size, $angle, $temp_x, $temp_y, $color, $font, $text[$i]);
                $bbox = imagettfbbox($size, 0, $font, $text[$i]);
                $temp_x += cos(deg2rad($angle)) * ($spacing + ($bbox[2] - $bbox[0]));
                $temp_y -= sin(deg2rad($angle)) * ($spacing + ($bbox[2] - $bbox[0]));
            }
        //}
    }

  public function getRewardsForMiner($id = "112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6") {
    $date = date("Y-m-d", strtotime("+1 day"));
    $days = 8;
    $url = "https://api.helium.io/v1/hotspots/".$id."/rewards/sum?min_time=-".$days."%20day&max_time=".$date."T00%3A00%3A00.000Z&bucket=day";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "It's-a Me, Mario!");
    if ($this->proxy)
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    $html = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($html);

    usort($decoded->data, function($a, $b) {
        return strcmp($a->timestamp, $b->timestamp);
    });
    return $decoded;
  }

  public function getMiner($id = "112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6") {
    $url = "https://api.helium.io/v1/hotspots/".$id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "It's-a Me, Mario!");
    if ($this->proxy)
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    $html = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($html);
    //print_r($decoded);
    return $decoded;
  }

  public function getWitnessedsForMiner($id = "112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6") {
    $url = "https://api.helium.io/v1/hotspots/".$id."/witnessed";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "It's-a Me, Mario!");
    if ($this->proxy)
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    $html = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($html);
    return $decoded;
  }

  public function getRolesForMiner($id = "112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6", $cursor = false) {
    if ($cursor)
      $cursor_ = "&cursor=".$cursor;
    else
      $cursor_ = "";
    $url = "https://api.helium.io/v1/hotspots/".$id."/roles?limit=100".$cursor_;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "It's-a Me, Mario!");
    if ($this->proxy)
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    $html = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($html);
    if (!$cursor)
      $decoded = $this->getRolesForMiner($id, $decoded->cursor);
    return $decoded;
  }

  public function getTransactionForMiner($hash, $id = "112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6") {
    $url = "https://api.helium.io/v1/transactions/".$hash."?actor=".$id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "It's-a Me, Mario!");
    if ($this->proxy)
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    $html = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($html);
    return $decoded;
  }

  public function getAmFromTransactionData($data) {
    $am = 0;
    if (!is_object($data) || !property_exists($data, "data")) {
      return 0;
    }
    foreach($data->data->rewards as $rew) {
      $am += $rew->amount;
    }
    return round($am / 100000000,3);
  }

  public function getWitnessedsFromTransactionData($data) {
    if (!is_object($data) || !property_exists($data, "data")) {
      return 0;
    }
    $valid = 0;
    $invalid = 0;
    foreach($data->data->path[0]->witnesses as $w) {
      if ($w->is_valid)
        $valid++;
      else
        $invalid++;
    }
    return ["valid"=>$valid,"invalid"=>$invalid];
  }

  public function getRolesTextByData($data, $miner, $limit = 6) {
    $ret = array();
    $cnt = 0;
    foreach($data->data as $dat) {
      $str = "";
      $tra = $this->getTransactionForMiner($dat->hash, $miner);
      if ($dat->role == "witness") { // Witnessed beacon
        $str = "Witnessed beacon";
        //$str .= " (".$this->getWitnessedsFromTransactionData($tra)["valid"].")";
      } else if ($dat->role == "reward_gateway") { // Received mining rewards
        $str = "Received mining rewards";
        $str .= " (".$this->getAmFromTransactionData($tra).")";
      } else if ($dat->role == "challenger") { // Constructed challenge | Challenged beaconer
        $str = "Constructed challenge";
      } else if ($dat->role == "challengee") { // Broadcast beacon
        $str = "Broadcast beacon";
      } else if ($dat->role == "packet_receiver") { // Transferred packets
        $str = "Transferred packets";
      }

      if ($str) {
        $when = $this->time_elapsed_string("@".$dat->time);
        $ret[] = [ "text"=>$str, "when"=>$when];
        $cnt++;
      }
      if ($cnt >= $limit) {
        break;
      }
    }
    return $ret;
  }

  public function getNameFromMinerData($data) {
    return ucwords(str_replace("-", ' ', $data->data->name));
  }

  public function getSingleNameFromMinerData($data) {
    $arr = explode(' ', $this->getNameFromMinerData($data));
    return end($arr);
  }

  public function getAvg() {
    $url = "https://explorer-api.helium.com/api/network/rewards/averages";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "It's-a Me, Mario!");
    if ($this->proxy)
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
    $html = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($html);

    usort($decoded, function($a, $b) {
        return strcmp($a->date, $b->date);
    });

    $newarr = [];
    $am = 7;
    for($i=30-$am; $i<=30; $i++){
      $newarr[] = $decoded[$i];
    }
    return $newarr;
  }

  public function calcHighest($miners, $avg) {
    $max = 0;
    foreach($miners as $miner) {
      foreach($miner->data as $val) {
        if ($val->total > $max)
          $max = $val->total;
      }
    }
    foreach($avg as $val) {
      if ($val->avg_rewards > $max)
        $max = $val->avg_rewards;
    }
    return $max;
  }

  public function drawBar($bot_x, $bot_y, $width, $color, $height) {
      imagefilledarc($this->gd, $bot_x, $bot_y, $width, $width/2,  0, 180, $color, IMG_ARC_PIE);
      imagefilledrectangle($this->gd, $bot_x-($width/2), $bot_y, $bot_x+($width/2), $bot_y-$height, $color);
      imagefilledarc($this->gd, $bot_x, $bot_y-$height, $width, $width/2,  180, 0, $color, IMG_ARC_PIE);
  }

  public function putAmountLabels($x, $y, $color, $amount, $size = 14, $angle = 315) {
    imagettftext($this->gd, $size, $angle, $x, $y, $color, $this->font, round($amount,3));
  }
  public function putTitle($x, $y, $color, $title, $size = 20) {
    imagettftext($this->gd, $size, 0, $x, $y, $color, $this->font, $title);
  }

  public function drawBars($id = "112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6", $y = 450) {
    $avg = $this->getAvg();
    $data = $this->getRewardsForMiner($id);
    $max = $this->calcHighest([$data], $avg);
    $im = &$this->gd;
    $white = &$this->white;
    $gray = &$this->gray;

    for($i=0;$i<8;$i++) {
      $x = $i*60 + 30;
      $num = (int)(($data->data[$i]->total * 100)/$max);
      $this->drawBar($x+20, $y, 20, $this->white, $num);
      $this->putAmountLabels($x+22, $y+20, $this->white, $data->data[$i]->total);

      $avgnum = count($avg) - (7-$i);
      if (isset($avg[$avgnum])) {
          $num = (int)($avg[$avgnum]->avg_rewards * 100)/$max;
          $this->drawBar($x, $y, 20, $this->gray, $num);
          $this->putAmountLabels($x-2, $y+20, $this->gray, $avg[$avgnum]->avg_rewards);
      }
    }
  }

  public function drawBars2($id) {
    $y = 430;
    $avg = $this->getAvg();
    $data = $this->getRewardsForMiner($id);
    $max = $this->calcHighest([$data], $avg);
    $im = &$this->gd;
    $white = &$this->white;
    $gray = &$this->gray;

    for($i=0;$i<8;$i++) {
      $barmult = 3.5;
      $barw = 46;
      $x = $i*(20+$barw+$barw) + 30;
      $num = (($data->data[$i]->total * 100)/$max);
      $this->drawBar($x+$barw, $y, $barw, $this->white, round($num*$barmult));
      $this->putAmountLabels($x+$barw+2, $y+30, $this->white, $data->data[$i]->total, 28,320);

      $avgnum = count($avg) - (7-$i);
      if (isset($avg[$avgnum])) {
          $num = ($avg[$avgnum]->avg_rewards * 100)/$max;
          $this->drawBar($x, $y, $barw, $this->gray, round($num*$barmult));
          $this->putAmountLabels($x-2, $y+30, $this->gray, $avg[$avgnum]->avg_rewards,28,320);
      }
    }
  }

  public function drawBars3($id1, $id2) {
    $y = 430;
    $avg = $this->getAvg();
    $data1 = $this->getRewardsForMiner($id1);
    $data2 = $this->getRewardsForMiner($id2);
    $max = $this->calcHighest([$data1, $data2], $avg);
    $im = &$this->gd;
    $white = &$this->white;
    $gray = &$this->gray;

    $prevx = -1;
    $prevy = -1;
    for($i=0;$i<8;$i++) {
      $barmult = 3.5;
      $barw = 46;
      $x = $i*(20+$barw+$barw) + 30;

      $num1 = (($data1->data[$i]->total * 100)/$max);
      $this->drawBar($x, $y, $barw, $this->white, round($num1*$barmult));
      $this->putAmountLabels($x-2, $y+30, $this->white, $data1->data[$i]->total, 28,320);

      $num2 = (($data2->data[$i]->total * 100)/$max);
      $this->drawBar($x+$barw, $y, $barw, $this->gray, round($num2*$barmult));
      $this->putAmountLabels($x+$barw+2, $y+30, $this->gray, $data2->data[$i]->total, 28,320);

      $avgnum = count($avg) - (7-$i);
      if (isset($avg[$avgnum])) {
          $num = ($avg[$avgnum]->avg_rewards * 100)/$max;
          //$this->drawBar($x, $y, $barw, $this->gray, round($num*$barmult));
          $color = ($num1>$num)? $this->gray : $this->white;
          $am = 2;
          imagefilledrectangle($this->gd, $x-($barw/2), $y-round($num*$barmult)-$am, $x+$barw-($barw/2), $y-round($num*$barmult)+$am, $color);
          imagefilledrectangle($this->gd, $x-($barw/2)+$barw, $y-round($num*$barmult)-$am, $x+$barw+$barw-($barw/2), $y-round($num*$barmult)+$am, $this->white);
          //if ($prevx != -1) {
          //    imageline($this->gd, $prevx, $prevy, $x-($barw/2), $y-round($num*$barmult), $this->white);
          //}
          $prevx = $x+$barw+$barw-($barw/2);
          $prevy = $y-round($num*$barmult);
      }
    }
  }

  public function drawRoles($id, $y) {
    $roles = $this->getRolesTextByData($this->getRolesForMiner($id), $id);
    foreach($roles as $key=>$role) {
      imagettftext($this->gd, 14, 0, 535, $y-60+($key*20), $this->white, $this->font, $role["text"]);
      imagettftext($this->gd, 14, 0, 840, $y-60+($key*20), $this->white, $this->font, $role["when"]);
    }
  }

  public function draw($id =  "112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6", $y = 450) {
    $minerData = $this->getMiner($id);
    $name = $this->getNameFromMinerData($minerData);
    $witnesseds = $this->getWitnessedsForMiner($id);
    imagettftext($this->gd, 18, 0, 550, $y-90, $this->white, $this->font, "Witnessed: ".count($witnesseds->data));
    $this->drawRoles($id, $y);

    $this->putTitle(30,$y-100-20,$this->white,$name);
    $this->drawBars($id, $y);

    $this->putTitle(390,40,$x->white,"Helium tracker");
  }

  public function putRewardScale($x, $y, $color, $minerData, $size) {
    $rs = round($minerData->data->reward_scale,2);
    imagettftext($this->gd, $size, 0, $x, $y, $color, $this->font, "ts: ".$rs);
  }

  public function getRewardScaleFromMinerData($minerData) {
    return $minerData->data->reward_scale;
  }

  public function draw2($id) {
    $minerData = $this->getMiner($id);
    $name = $this->getNameFromMinerData($minerData);
    $this->putTitle      (30,40,$this->white,$name,30);
    $this->putRewardScale(730,40,$this->white,$minerData,30);
    $this->drawBars2($id);
  }

  public function draw3($id1, $id2) {
    $minerData1 = $this->getMiner($id1);
    $name1 = $this->getSingleNameFromMinerData($minerData1);
    $ts1 = round($minerData1->data->reward_scale,2);
    $this->putTitle(30,40,$this->white,$name1." ts: ".$ts1,30);

    $minerData2 = $this->getMiner($id2);
    $name2 = $this->getSingleNameFromMinerData($minerData2);
    $ts2 = round($minerData2->data->reward_scale,2);
    $miner2titletext = "ts: ".$ts2.' '.$name2;
    $textsize = $this->calculateTextBox($miner2titletext, $this->font, 30, 0);
    $this->putTitle(960-30-$textsize['width'],40,$this->gray,$miner2titletext,30);
    $this->drawBars3($id1, $id2);
  }

// https://www.php.net/manual/en/function.imagettfbbox.php
public function calculateTextBox($text,$fontFile,$fontSize,$fontAngle) {
    /************
    simple function that calculates the *exact* bounding box (single pixel precision).
    The function returns an associative array with these keys:
    left, top:  coordinates you will pass to imagettftext
    width, height: dimension of the image you have to create
    *************/
    $rect = imagettfbbox($fontSize,$fontAngle,$fontFile,$text);
    $minX = min(array($rect[0],$rect[2],$rect[4],$rect[6]));
    $maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6]));
    $minY = min(array($rect[1],$rect[3],$rect[5],$rect[7]));
    $maxY = max(array($rect[1],$rect[3],$rect[5],$rect[7]));
   
    return array(
     "left"   => abs($minX) - 1,
     "top"    => abs($minY) - 1,
     "width"  => $maxX - $minX,
     "height" => $maxY - $minY,
     "box"    => $rect
    );
}

  public function toCppData() {
    for($y=0; $y<$this->canvas_height; $y++) {
      $byte = 0;
      $done = true;
      for($x=0; $x<$this->canvas_width; $x++) {
        $pix = imagecolorat($this->gd, $x, $y);
        $colors = imagecolorsforindex($this->gd, $pix);
        $col = (($colors["red"] + $colors["green"] + $colors["blue"]) / 3) >> 4;
        //print_r($colors);
        //echo "$col\n";
        if ($x%2 == 0) {
          $byte = $col;
          $done = false;
        } else {
          $byte |= ($col << 4);
          echo pack("C",$byte);
          //echo "0x".bin2hex(pack("C",$byte)).", ";
          $done = true;
        }
      }
      if (!$done)
          echo pack("C",$byte);
          //echo "0x".bin2hex(pack("C",$byte)).", ";
      //echo "\n";
    }
  }

  public function toImage() {
    imagepng($this->gd, "img.png");
    imagedestroy($this->gd);
  }
}


$x = new helium();
$x->draw3("112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6", "11xY7iccN7GLb2FaymovJLFW5MPT1GBoSDYGHpr2ncVHt5MvDqW");
//$x->draw2("112Ba7ybtoxxa1n5mVFcFfvyyUwwgyZt9FEDgS7np9QQt8q5k7k6");
//$x->draw("11UYHXKndJKP13EXtZR3yPGnS7bxvBq7h6LLxfCSCTCQK9QGS8s", 450);

ob_start();
$x->toCppData();
header("Content-length: ".ob_get_length());
ob_end_flush();

$x->toImage();

