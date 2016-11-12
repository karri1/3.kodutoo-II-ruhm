<?php

require("functions.php");
//muutujad
$note = "Telli ajakiri: ";

//veateated
$orderError = "";

//kui id'd ei ole, siis suunatakse sisselogimise lehele
if(!isset ($_SESSION["userId"])){
	header("Location: login.php");
	exit();                      
}

//kui on ?logout aadressireal, siis sessioon lõpetatakse ja suunatakse sisselogimise lehele
if (isset($_GET["logout"])) {
	session_destroy();
	header("Location: login.php");
	exit();
}

//Kuude massiiv
$m = array("jaanuar","veebruar","märts","aprill","mai","juuni","juuli","august","september","oktoober","november","detsember"); 

//enne 20.kuupäeva tellimus alates järgmisest kuust, muul juhul alates ülejärgmisest kuust
if (date("d") > 20){
	//$fromYmd = $toYmd = date("Y-m-d", mktime(0, 0, 0, date("m") + 2, 15, date("y")));
	$fromMonth = $toMonth = date('n', strtotime("+2 Months"));  
	$fromYear = $toYear = date('Y', strtotime("+2 Months")); 
	
	
}else {

	$fromMonth = $toMonth = date('n', strtotime("+1 Months"));
	$fromYear = $toYear = date('Y', strtotime("+1 Months"));
	

}

//kontrollin tellimuse perioodi
if(isset($_POST["from"])){
	if(isset($_POST["until"])){
		$strOrderFrom = $_POST["from"];               //str, et võrrelda kasutaja sisestust valikutega
		$strOrderTo = $_POST["until"];
		$orderFrom = date_create($_POST["from"]);    //date, et teha kuupäevadega arvutusi
		$orderTo = date_create($_POST["until"]);
	
		if($orderFrom > $orderTo){
			echo "Tellimuse periood ei saa lõppeda varem, kui algab";
		}else{
			
			$diff = date_diff($orderFrom, $orderTo);
			//$diff= $diff->format("%m") + 1;
			$diff = (($diff->format('%y') * 12) + $diff->format('%m'));
			$note = "Tellimuse periood kuudes: ";
			$note .= $diff + 1 . ", hind: " . 5* ($diff +1) . "€<br>";  
			$orderFrom = $orderFrom->format("Y-m-d");
			$orderTo = $orderTo->format("Y-m-d");
			$note .= placeOrder($orderFrom, $orderTo, $_SESSION["userId"]);  //funktsioon tellimuse andmebaasi lisamiseks
			
		}
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Sisse loginud</title>
</head>
<body>
<a href="?logout=1">Logi välja</a>

<p>Tere tulemast <?=$_SESSION["userName"];?>!</p><br>

<p><?=$note;?></p>

<form method="post">
<!--alates -->
alates:
<select name="from">

<?php

for($i = 0; $i < 6; $i++){
	$fromYmd = $fromYear . "-" . $fromMonth . "-15";
	$chooseFrom = $m[$fromMonth - 1]." ".$fromYear;
	if ($fromYmd == $strOrderFrom){                   //et valitud kuupäev oleks nähtav pärast submit vajutamist
		$selected = "selected = 'selected'";
	} else {
		$selected = "";
	}
	 echo "<option value='$fromYmd' $selected>$chooseFrom</option>";
	 if($fromMonth == 12) { 
		$fromMonth = 1; 
        $fromYear++; 
     } else { 
        $fromMonth++; 
     }
}
?>
</select>
 
<!-- kuni -->
&nbsp kuni(k.a):      
<select name="until">


<?php

for($i = 0; $i < 18; $i++){
	$toYmd = $toYear . "-" . $toMonth . "-15";
	$chooseTo = $m[$toMonth - 1]." ".$toYear;
	if ($toYmd == $strOrderTo){                      //et valitud kuupäev oleks nähtav pärast submit vajutamist
		$selected = "selected = 'selected'";
	} else {
		$selected = "";
	}
	 echo "<option value='$toYmd' $selected>$chooseTo</option>";
	 
	 if($toMonth == 12) { 
		$toMonth = 1; 
        $toYear++; 
     } else { 
        $toMonth++; 
     }  
}
?>
<br>
</select>
<input type="submit" value="Telli">
</form>
<!--Olemasolevad tellimused-->
 <p>Sinu tellimused</p>
 <?php
 //kutsun funktsiooni,  userOrders= array(order, order), kus order on stdClass.... (Order_nr=>order_idDB, From=>from, To=>to)
 $userOrders = getData($_SESSION["userId"]);
 
 $html = "<table style='border: 1px solid black';>";
	$html .= "<tr>";
		$html .= "<th style='border: 1px solid black';>Tellimuse nr</th>";
		$html .= "<th style='border: 1px solid black';>Algus</th>";
		$html .= "<th style='border: 1px solid black';>Lõpp</th>";
	$html .= "</tr>";
	
	foreach($userOrders as $o){
		$html .= "<tr >";
		$html .= "<td style='border: 1px solid black';>" . $o->Order_nr . "</td>";
		$html .= "<td style='border: 1px solid black';>" . $o->From . "</td>";
		$html .= "<td style='border: 1px solid black'; >" . $o->To . "</td>";  
		$html .= "<td><a href='edit.php?orderid=".$o->Order_nr."'>Pikenda</a></td>"; //klikkides aadressireale edit.php?orderid=tell.nr
		$html .= "</tr>";
	}
 $html .= "</table>";
 echo $html;
 
 
 
 ?>


</body>
</html>



