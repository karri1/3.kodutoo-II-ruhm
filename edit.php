<?php
require("functions.php");
$selectedMonth = "";
$note = "";
$none = "";                //style= display:"";  ....tellimuse pikendamise vormi näidatakse

//link  tellimuse tühistamiseks
/*  <a href="?id=<?=$_GET["id"];?>&delete=true">kustuta</a>   */

$delete = "<a href=?orderid=";                       //aadressireale orderid
$delete .= $_GET["orderid"];                         //aadressireal olev orderid väärtus
$delete .= "&delete=true>Tühista tellimus</a>" ;     //aadressireale delete=true
if(!isset($_GET["orderid"])){
	header("Location: data.php");
	exit();
}

        //kui vajutati nuppu pikenda
if(isset($_POST["choose"])){            
	//var_dump ($_POST);             key=>value array, kus on kõik POST meetodiga saadetud informatsioon
	//echo $selectedMonth;          //???   väärtust pole, kuigi vormis saab väärtuse  ???
	
	
	changeOrder($_POST["months"], $_POST["order_id"], $_SESSION["userId"]);
	header("Location: edit.php?orderid=".$_POST["order_id"]."&success=true");  
	
    exit();
}

        //kui vajutati tühista linki
if(isset($_GET["delete"])){                         
	deleteOrder($_GET["orderid"], $_SESSION["userId"]);
	$delete = "";              //pole rohkem vaja Tühista linki
	$note = "Tellimus on tühistatud!";
	$none = "none";        //style= display:none;  ....tellimuse pikendamise vormi ei näidata

}
if(isset($_GET["success"])){
	$note = "Tellimus on pikendatud!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Pikenda tellimust</title>
</head>
<body>
<a href="data.php"> Tagasi tellimise lehele </a>
<br><br>

<form method="post" style="display: <?=$none;?>;">
<h4>Vali mitu kuud tellimust pikendatakse</h4>
	<input type="hidden" name="order_id" value="<?=$_GET["orderid"];?>" >
	<select name="months">
<?php
	//valik 1-12 kuud tellimuse pikendamiseks                                     
	if(isset($_POST["months"])){
		$selectedMonth = $_POST["months"];	
	}
	for($i=1; $i<13; $i++){
		if($i == $selectedMonth){
			$selected = "selected = 'selected'";
		} else {
			$selected = "";
		}
		echo "<option value='$i' $selected>$i</option>";     
	}
?>
	</select> 
	<input type="submit" name="choose" value="Pikenda">
</form>

<?php
//muudetav tellimus
$singleOrder = getSingleOrder($_GET["orderid"], $_SESSION["userId"]);
 $html = "<table style='border: 1px solid black';>";
	$html .= "<tr>";
		$html .= "<th style='border: 1px solid black';>Tellimuse nr</th>";
		$html .= "<th style='border: 1px solid black';>Algus</th>";
		$html .= "<th style='border: 1px solid black';>Lõpp</th>";
	$html .= "</tr>";
	
		$html .= "<tr >";
		if($singleOrder->order_id AND $singleOrder->date_from AND $singleOrder->date_to) {
			$html .= "<td style='border: 1px solid black';>" . $singleOrder->order_id . "</td>";
			$html .= "<td style='border: 1px solid black';>" . $singleOrder->date_from . "</td>";
			$html .= "<td style='border: 1px solid black'; >" . $singleOrder->date_to . "</td>"; 
		}else{
			$html .= "<td style='border: 1px solid black';>" . $_GET["orderid"] . "</td>";
			$html .= "<td style='border: 1px solid black';>TÜHISTATUD</td>";
			$html .= "<td style='border: 1px solid black';>TÜHISTATUD</td>";
		}
		$html .= "</tr>";
	
 $html .= "</table>";
 echo $html;
 echo "<br>" . $note . "<br>";
 echo "<br><br>" . $delete;
 ?>
</body>
</html>