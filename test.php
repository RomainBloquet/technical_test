<?php
//importation des classes
include 'get_tripAdvisor_class.php';

//information pour la connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "test_technique";

// Creation de la connection
$conn = new mysqli($servername, $username, $password, $database);

//permettre de rentrer des caractères spéciaux dans la table
$conn->set_charset("utf8");

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
echo ("</br>");

//vérifier si l'attribut tripadvisor_url existe
if (isset($_GET['tripadvisor_url']))
{
  $lien = $_GET['tripadvisor_url'];
  //si le lien n'est pas vide alors exécuter le code suivant
  if(!empty($lien))
  {
    //récupération de l'adresse de l'établissement
    $adresse = new Adresse($lien);
    $adresse->set_rue();
    $adresse->set_code_postal();
    $adresse->set_pays();

    //vérification si l'adresse est connue
    if(!empty($adresse->get_rue()))
    {
      // récupération des cooronnées gps
      $gps = new Coordonnees_gps();
      $gps->set_gps($adresse);
    }

    //création de l'établissement
    $etablissement = new Etablissement($lien);
    $etablissement->set_id();
    $etablissement->set_nom();
    $etablissement->set_adresse($adresse);
    if(!empty($adresse->get_rue()))
    {
      $etablissement->set_gps_coordo();
    }
    $etablissement->set_note();

    //création des attributs à entrer dans la table  avec sécurité contre injection sql
    $id = mysqli_real_escape_string($conn, $etablissement->get_id());
    $nom = mysqli_real_escape_string($conn, $etablissement->get_nom());
    if(!empty($adresse->get_rue()))
    {
      $location = mysqli_real_escape_string($conn, $etablissement->get_adresse()->return_adresse());
      $gps = mysqli_real_escape_string($conn, $etablissement->get_gps_coord()->return_gps());
    }
    else
    {
      $location = mysqli_real_escape_string($conn,ltrim ($etablissement->get_adresse()->return_adresse(),","));
      $gps = "position inconnue";
    }
    $rate = mysqli_real_escape_string($conn, $etablissement->get_note());

    //insertion des données dans la table
    //empecher les doublons
    $existant = $conn->query("SELECT id_tripadvisor FROM data WHERE id_tripadvisor='".$id."'");
    $existant = mysqli_fetch_all($existant,MYSQLI_ASSOC);
    if (empty($existant))
    {
      $sql = "INSERT INTO data (id_tripadvisor, nom, adresse, gps, note) VALUES ('$id', '$nom', '$location', '$gps', '$rate')";
      if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
        echo ("</br>");
      }
      else
      {
        echo "Error: " . $sql . "<br>" . $conn->error;
      }
    }
    else
    {
      {
        echo "resultat deja existant";
      }
    }

    $result = $conn->query("SELECT * FROM data");
    echo "<pre>";
    print_r(mysqli_fetch_all($result,MYSQLI_ASSOC));
    echo "</pre>";
  }
  else
  {
    //si aucun lien n'a été spécifié
    echo 'Il faut renseigner le lien de tripadvisor !';
  }
}
?>
