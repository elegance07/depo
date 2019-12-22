<title> Program Botu</title>
<meta charset="utf-8">

 <link type="text/css" rel="stylesheet" href="bot.css" />
<div id="veriListesi">
<form action="botForm.php" method="post">
   <h3>inndir.com program adresi</h3>
   <input type="text" name="anan" class="minicikkutucuk"/>

<div class="veriKategori">
<h3>Eklenecek Kategori:<br></h3>


<div class="veriKategori">
		<ul>
			<li>
				<select name="kategori">
<?php
 
/*
 DLE MySQL Bağlantısı
 Author: Yücel DURMAZ (TrueLove)
*/
 
define ( 'DATALIFEENGINE', true );
define ( 'ROOT_DIR', dirname ( __FILE__ ) );
define ( 'ENGINE_DIR', ROOT_DIR . '/engine' );
 
// Tüm DLE ayarlarını okur
require_once ENGINE_DIR . '/data/config.php';
 
// Veritabanına bağlanmak için gerekli script
require_once ENGINE_DIR . '/classes/mysql.php';
// Veritabanı bilgileri ile bağlantı kurar ( $db )
require_once ENGINE_DIR . '/data/dbconfig.php';
 
// Veritabanından kategorileri çekerek ekrana basar
$sql = $db->query( "SELECT * FROM " . PREFIX . "_category ORDER by name DESC" );
while ( $dondur = $db->get_row( $sql ) ) {
	echo  "<option value='".$dondur["id"]."' selected>".$dondur["name"]."</option>";
	echo "<br />";
}
 
 
// Bağlantıyı keser
$db->close ();
?>
	
				</select>
			</li>
			</ul>
	</div>
<ul>
<li><input type="submit" value="GETIR">
		</li></ul>
</form>