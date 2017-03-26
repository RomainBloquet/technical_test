<?php

class Adresse
{
  private $_rue;
  private $_code_postal;
  private $_ville;
  private $_pays;
  private $_doc;
  private $_finder;

  public function __construct($lien)
  {
    $this->_doc = new DOMDocument();
    @$this->_doc->loadHTMLFile($lien);
    $this->_doc->validateOnParse = true;
    $this->_finder = new DOMXPath($this->_doc);

  }

  public function set_rue()
  {
    $localisation = $this->_finder->query("//*[contains(@class, 'street-address')]");
    foreach ($localisation as $loc)
    {
      if(!empty($loc->nodeValue))
      {
        $this->_rue = $loc->nodeValue;
      }
      else
      {
        $this->_rue;
      }
      break;
    }
  }

  public function get_rue()
  {
    return $this->_rue;
  }

  public function set_code_postal()
  {
    $cp = $this->_finder->query("//*[contains(@class, 'locality')]");
    foreach ($cp as $c){
      $this->_code_postal = $c->nodeValue;
      break;
    }
    $this->_code_postal = str_replace(',', '',   $this->_code_postal);
    $city = explode (" ", $this->_code_postal);
    $this->_code_postal = $city[0];
    $ville = "";
    for ($i = 1; $i < count($city); $i++)
    {
      $ville = $ville . $city[$i];
    }
    $this->_ville = $ville;
  }

  public function get_code_postal()
  {
    return $this->_code_postal;
  }

  public function get_ville()
  {
    return $this->_ville;
  }

  public function set_pays()
  {
    $country = $this->_finder->query("//*[contains(@class, 'country-name')]");
    foreach ($country as $ctry)
    {
      $this->_pays = $ctry->nodeValue;
      break;
    }
  }

  public function get_pays()
  {
    return $this->_pays;
  }

  public function return_adresse()
  {
    return $this->_rue . ", " . $this->_code_postal . " " . $this->_ville . ", " . $this->_pays;
  }
}



class Coordonnees_gps
{
  private $latitude;
  private $longitude;

  public function set_gps(Adresse $ad)
  {
    $adresse = rawurlencode($ad->get_rue() . " " . $ad->get_ville());
    $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=" . $adresse;
    $response = file_get_contents($url);
    $json = json_decode($response,true);
    $this->latitude = $json['results'][0]['geometry']['location']['lat'];
    $this->longitude = $json['results'][0]['geometry']['location']['lng'];
  }

  public function get_latitude()
  {
    return $this->latitude;
  }
  public function get_longitude()
  {
    return $this->longitude;
  }

  public function return_gps()
  {
    return $this->latitude . " " . $this->longitude;
  }
}

class Etablissement
{
  private $_id_tripAdvisor;
  private $_nom_etablissement;
  private $_adresse;
  private $_coordonnes_gps;
  private $_note;
  private $_doc;
  private $_finder;
  private $_lien;


  public function __construct($lien)
  {
    $this->_lien = $lien;
    $this->_doc = new DOMDocument();
    @$this->_doc->loadHTMLFile($lien);
    $this->_doc->validateOnParse = true;
    $this->_finder = new DOMXPath($this->_doc);
  }

  public function set_id()
  {
    $tab_id = explode("-", $this->_lien);
    $this->_id_tripAdvisor = $tab_id[1] . "-" . $tab_id[2];
  }

  public function get_id()
  {
    return $this->_id_tripAdvisor;
  }

  public function set_nom()
  {
    $this->_nom = $this->_doc->getElementById('HEADING')->textContent;
    $this->_nom = trim($this->_nom,"\n");
  }

  public function get_nom()
  {
    return $this->_nom;
  }

  public function set_adresse(Adresse $adress)
  {
    $this->_adresse = new Adresse($this->_lien);
    $this->_adresse = $adress;
  }

  public function get_adresse()
  {
    return $this->_adresse;
  }

  public function set_gps_coordo()
  {
    $this->_coordonnes_gps = new Coordonnees_gps();
    $this->_coordonnes_gps->set_gps($this->_adresse);
  }

  public function get_gps_coord()
  {
    return $this->_coordonnes_gps;
  }

  public function set_note()
  {
    $html=file_get_contents($this->_lien);
    $metas = $this->_doc->getElementsByTagName('meta');

    for ($i = 0; $i < $metas->length; $i++)
    {
      $meta = $metas->item($i);
      if($meta->getAttribute('name') == 'description')
      $description = $meta->getAttribute('content');
    }

    $rate = strstr($description, 'noté', true);
    if(!empty($rate))
    {
      $rating = explode ('noté', $description);
      $rating = $rating[1];
      $rating= explode (' ', $rating);
      $this->_note = $rating[1];
    }
    else {
      $this->_note = null;
    }
  }

  public function get_note()
  {
    return $this->_note;
  }

}
?>
