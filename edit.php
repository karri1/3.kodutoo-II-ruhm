<?php
require("functions.php");
$selectedMonth = "";
$note = "";


if(!isset($_GET["orderid"])){
	header("Location: data.php");
	exit();
}

if(isset($_POST["choose"])){
	//var_dump ($_POST);             key=>value array, kus on kõik POST meetodiga saadetud informatsioon
	//echo "vajutati pikenda nupule"; echo $_POST["months"]; echo $_GET["orderid"]; echo $_POST["order_id"];
	//echo $selectedMonth;          //???   väärtust pole, kuigi vormis saab väärtuse  ???
	$note = "";
	changeOrder($_POST["months"], $_POST["order_id"], $_SESSION["userId"]);
	header("Location: edit.php?orderid=".$_POST["order_id"]."&success=true");  
    exit();
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
<a href="data.php"> tagasi </a>
<h4>Vali mitu kuud tellimust pikendatakse</h4>

<form method="post" >
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
		$html .= "<td style='border: 1px solid black';>" . $singleOrder->order_id . "</td>";
		$html .= "<td style='border: 1px solid black';>" . $singleOrder->date_from . "</td>";
		$html .= "<td style='border: 1px solid black'; >" . $singleOrder->date_to . "</td>";  
		$html .= "</tr>";
	
 $html .= "</table>";
 echo $html;
 echo "<br>" . $note;
 ?>
</body>
</html>