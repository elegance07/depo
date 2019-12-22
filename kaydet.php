<title> bot</title>
<meta charset="utf-8">


 <link type="text/css" rel="stylesheet" href="bot.css" />
 


<?php 
define ( 'DATALIFEENGINE', true );
define ( 'ROOT_DIR', dirname ( __FILE__ ) );
define ( 'ENGINE_DIR', ROOT_DIR . '/engine' );
 
// Tüm DLE ayarlarýný okur
require_once ENGINE_DIR . '/data/config.php';
 
// Veritabanýna baðlanmak için gerekli script
require_once ENGINE_DIR . '/classes/mysql.php';
// Veritabaný bilgileri ile baðlantý kurar ( $db )
require_once ENGINE_DIR . '/data/dbconfig.php';

?>

<?php


$veri_baslik=htmlspecialchars($_POST["veri_baslik"]);
$veri_lisans=htmlspecialchars($_POST["veri_lisans"]);
$veri_boyut=htmlspecialchars($_POST["veri_boyut"]);
$veri_kisitlama=htmlspecialchars($_POST["veri_kisitlama"]);
$veri_uretici=htmlspecialchars($_POST["veri_uretici"]);
$veri_isletim=htmlspecialchars($_POST["veri_isletim"]);
$veri_ozet=addslashes($_POST["veri_ozet"]);
$veri_tanitim=addslashes($_POST["veri_tanitim"]);
$veri_resim=htmlspecialchars($_POST["veri_resim"]);
$kategori= $_POST["kategori"];


$p = "1";
$ekleyen = "TrueLove";

$sabit ="0"; //1 ana sayfaya sabitler
$simdi=date('Y-m-d H:i:s');
$lisans = "lisans|";
$boyut = "||boyut|";
$kisitlama ="||kisitlama|";
$uretici ="||uretici|";
$isletim ="||isletim|";
$resim ="||resim|";
$birlestir = $lisans.$veri_lisans.$boyut.$veri_boyut.$kisitlama.$veri_kisitlama.$uretici.$veri_uretici.$isletim.$veri_isletim.$resim.$veri_resim;



$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, keywords, descr, category, alt_name, allow_comm, approve, allow_main, fixed, allow_br, symbol, tags) VALUES ('$simdi', '$ekleyen', '$veri_ozet', '$veri_tanitim', '$birlestir', '$veri_baslik', '', '', '$kategori', '', '0', '1', '1', '0', '1', '', '')" );
$post_id = $db->insert_id();
$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, user_id) VALUES ('{$post_id}', '1', '$p')" );

$db->query( "UPDATE " . PREFIX . "_users set news_num=news_num+1 where name = '$p'" );



echo "<div id='veriListesi'>
  <div class='veriKategori'>";

echo "<li><a  href='#'> $veri_baslik Başarıyla sisteme eklendi</a></li>";
echo "</div></div>";
// Baðlantýyý keser
$db->close ();
?>
