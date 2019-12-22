
<title> Program Botu</title>
<meta charset="utf-8">

 <link type="text/css" rel="stylesheet" href="bot.css" />


<form action="kaydet.php" method="POST">

<div id="veriListesi">
  <div class="veriKategori">
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
/*/ 
anan isimli input'dan veri geliyor

/*/
$site = $_POST['anan']; 
$kategori = $_POST['kategori']; 



      $giris = SiteBaglan($site = $_POST['anan']);

 function SiteBaglan($site) {

  $ch = curl_init();
  $hc = "YahooSeeker-Testing/v3.9 (compatible; Mozilla 4.0; MSIE 5.5; Yahoo! Search - Web Search)";
  curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com');
  curl_setopt($ch, CURLOPT_URL, $site);
  curl_setopt($ch, CURLOPT_USERAGENT, $hc);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $site = curl_exec($ch);
  curl_close($ch);
  
  // Veriyi parcalara ayıralım
  preg_match_all('@<h1>(.*?)</h1>@si',$site,$veri_baslik); //baslik
  preg_match_all('@<td class="kalin sag">(.*?)</td>@si',$site,$veri_lisans); //lisans
  preg_match_all('@<td class="kalin">(.*?)</td>@si',$site,$veri_boyut); //Boyut
  preg_match_all('@<td class="kalin">(.*?)</td>@si',$site,$veri_kisitlama); //kisitlama
  preg_match_all('@<td class="kalin">(.*?)</td>@si',$site,$veri_uretici); //uretici
  preg_match_all('@<td class="kalin">(.*?)</td>@si',$site,$veri_isletim); //işletim sistemi
  preg_match_all('@<div id="pOzet">(.*?)</div>@si',$site,$veri_ozet); //özet - short-story eklenecek
  preg_match_all('@<div id="ptanitim">(.*?)</div>@si',$site,$veri_tanitim); //Tanıtım - full-story eklenecek
  preg_match_all('@img/pimg/(.*?)"@si',$site,$veri_resim); //resim




$sonuc["veri_baslik"]=$veri_baslik[0][0];
$sonuc["veri_lisans"]=$veri_lisans[0][0];
$sonuc["veri_boyut"]=$veri_boyut[0][0];
$sonuc["veri_kisitlama"]=$veri_kisitlama[0][2];
$sonuc["veri_uretici"]=$veri_uretici[0][3];
$sonuc["veri_isletim"]=($veri_isletim[0][4]);
$sonuc["veri_ozet"]=$veri_ozet[0][0];
$sonuc["veri_tanitim"]=$veri_tanitim[0][0];
$sonuc["veri_resim"]=$veri_resim[0][0];



/*/print_r($veri_ozet);/*/

echo "Program Adı";
echo "<br>";
echo "<input type='text' size='50' name='veri_baslik' class='minicikkutucuk' value='".strip_tags($veri_baslik[0][0])."'>";
echo "<br>";
echo "Lisans";
echo "<br>";
echo "<input type='text' size='50' name='veri_lisans' class='minicikkutucuk' value='".strip_tags($veri_lisans[0][0])."'>";
echo "<br>";
echo "Boyut";
echo "<br>";
echo "<input type='text' size='50' name='veri_boyut' class='minicikkutucuk' value='".strip_tags($veri_boyut[0][0])."'>";
echo "<br>";
echo "KISITLAMA";
echo "<br>";
echo "<input type='text' size='50' name='veri_kisitlama' class='minicikkutucuk' value='".strip_tags($veri_kisitlama[0][2])."'>";
echo "<br>";
echo "Uretici";
echo "<br>";
echo "<input type='text' size='50' name='veri_uretici' class='minicikkutucuk' value='".strip_tags($veri_uretici[0][3])."'>";
echo "<br>";
echo "Isletim Sistemi";
echo "<br>";
echo "<input type='text' size='70' name='veri_isletim' class='minicikkutucuk' value='".strip_tags($veri_isletim[0][6])."'>";
echo "<br>";
echo "Ozet";
echo "<br>";
echo '<input type="text" size="70" name="veri_ozet" class="minicikkutucuk" value="'.strip_tags($veri_ozet[0][0]).'">';
echo "<br>";
echo "Tanitim";
echo "<br>";
echo '<input type="text" size="70" name="veri_tanitim" class="minicikkutucuk" value="'.strip_tags($veri_tanitim[0][0]).'">';
echo "<br>";
echo "resim";
echo "<br>";
echo '<input type="text" size="70" name="veri_resim" class="minicikkutucuk" value="http://www.inndir.com/'.strip_tags($veri_resim[0][1]).'">';
return $sonuc;

 }



 echo "<br>";
 echo "Eklenecek Kategori ID";
 echo "<br>";
echo "<input type='text' name='kategori' value='$kategori'>"; 
 echo "<br>";

?>

<br>

  <input type="submit" value="PROGRAM BİLGİLERİNİ KAYDET" class="minicikkutucuk" name="a">

</form>
</div>
</div>
