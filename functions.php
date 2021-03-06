<?php
require("../../config.php");
session_start();
function cleanInput($input){
		
		$input = trim($input);
		$input = stripslashes($input);
		$input = htmlspecialchars($input);
		
		return $input;
		
	}
	
function signUp($firstname, $lastname,  $gender, $address, $city, $zip, $email, $password){
	$database = "if16_karin";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
	$mysqli->set_charset("utf8");	
	//uue lisamine...("INSERT INTO tabelinimi (lahter1DB, lahter2DB) VALUES (?, ?)");
	$stmt = $mysqli->prepare("INSERT INTO users_katse (Firstname, Lastname, Gender, Address, City, Zipcode, Email, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	echo $mysqli -> error;   
		
	$stmt -> bind_param("ssssssss", $firstname, $lastname, $gender, $address, $city, $zip, $email, $password ); 

	if ($stmt->execute()) {
		echo "<br> Kasutaja loodud<br> <br>";
		echo "Nimi: ". $firstname . " " . $lastname . "<br>";	
		echo "E-post: ". $email . "<br>";
		echo "Aadress: ". $address . " " . $city . ", " . $zip . "<br>";
	} else {
		echo "ERROR ".$stmt->error;
	}
			
	$stmt->close();
	$mysqli->close();
}

function login($email, $password){
	
	$error = "";
	
	$database = "if16_karin";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
	$mysqli->set_charset("utf8");
	//olemasolevate küsimine...("SELECT lahter1DB, lahter2DB FROM tabelinimi WHERE lahter1DB = ?");
	$stmt = $mysqli->prepare("SELECT id, Firstname, Email, Password FROM users_katse WHERE Email = ?");
	
	echo $mysqli->error;
		
	$stmt->bind_param("s", $email);            //küsimärk asendatakse kasutaja sisestatud emailiga
		
	//määran väärtused muutujatesse
	$stmt->bind_result($id, $nameFromDb, $emailFromDb, $passwordFromDb);
	$stmt->execute();
	
	if($stmt->fetch()){
		
		//oli sellise meiliga kasutaja
		//password millega kasutaja tahab sisse logida
		$hash = hash("sha512", $password);
		if ($hash == $passwordFromDb) {
			echo "Kasutaja logis sisse ".$id;
				
			//määran sessiooni muutujad, millele saan ligi teistelt lehtedelt
			$_SESSION["userId"] = $id;
			$_SESSION["userEmail"] = $emailFromDb;
			$_SESSION["userName"] = $nameFromDb;
			header("Location: data.php");                     
			exit();
		}else {
			$error = "Vale parool";
		}
			
			
	} else {
			
		// ei leidnud kasutajat selle meiliga
		$error = "Ei ole sellist emaili";
	}
		
	return $error;
}

//TELLIMUS ANDMEBAASI

function placeOrder($orderFrom, $orderTo, $userId){
	$database = "if16_karin";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
	
	//kontrollin ega samasugust tellimust samalt kasutajalt juba pole
	//olemasolevate küsimine...("SELECT lahter1DB, lahter2DB FROM tabelinimi WHERE lahter1DB = ?");
	$stmt = $mysqli->prepare("SELECT Date_from, Date_to, User_id FROM orders_katse WHERE Date_from = ? AND Date_to = ? AND User_id = ? AND deleted IS NULL");
	$stmt -> bind_param("ssi", $orderFrom, $orderTo, $userId);
	$stmt -> bind_result($orderFromDB, $orderToDB, $userIdDB); 
	$stmt -> execute();
	if ($stmt->fetch()) {
		// pärast returni midagi edasi ei tehta funktsioonis
		return;
	}
	$stmt -> close();
	//tellimus andmebaasi	
	$stmt = $mysqli->prepare("INSERT INTO orders_katse (Date_from, Date_to, User_id) VALUES (?, ?, ?)");
	echo $mysqli -> error;   
		
	$stmt -> bind_param("sss", $orderFrom, $orderTo, $_SESSION["userId"] ); 

	if ($stmt->execute()) {
		$note = "<br> Tellimus vastu võetud <br> <br>";
	} else {
		$note = "ERROR ".$stmt->error;
	}
	return $note;	
		
	$stmt->close();
	$mysqli->close();
	
}

//KÕIK KASUTAJA TELLIMUSED
function getData($user_id, $q, $sort, $direction) {
		
	$database = "if16_karin";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
	//olemasolevate küsimine...("SELECT lahter1DB, lahter2DB FROM tabelinimi WHERE lahter1DB = ?");	
	//$stmt = $mysqli->prepare("SELECT Order_id, Date_from, Date_to FROM orders_katse WHERE User_id=? AND deleted IS NULL");
	
	//mis sort ja järjekord
		$allowedSortOptions = ["Order_id", "Date_from", "Date_to"];
		//kas sort on lubatud valikute sees
		if(!in_array($sort, $allowedSortOptions)){
			$sort = "Order_id";                     //vaikimisi
		}
		//echo "Sorteerin: ".$sort." ";
		
		$orderBy= "ASC";
		if($direction == "descending"){
			$orderBy= "DESC";
		}
		//echo "Järjekord: ".$orderBy." ";
	
	//olemasolevate küsimine ja sorteerimine
	//("SELECT lahter1DB, lahter2DB FROM tabelinimi /kui vaja: WHERE lahter1DB = ? AND deleted IS NULL/ ORDER BY lahterDB ASC/DESC");
	if($q == ""){
		$stmt = $mysqli->prepare("SELECT Order_id, Date_from, Date_to FROM orders_katse 
	                          WHERE User_id=? AND deleted IS NULL
							  ORDER BY $sort $orderBy");      //kus $sort on kasutaja valitud lahter
		$stmt->bind_param('i' , $user_id);
	}else{
		$searchword = $q."-%";
		$stmt = $mysqli->prepare("SELECT Order_id, Date_from, Date_to FROM orders_katse 
	                          WHERE User_id=? AND deleted IS NULL AND
							  (Date_from LIKE ? OR Date_to LIKE ?)    
							  ORDER BY $sort $orderBy"); 
		$stmt->bind_param("iss", $user_id, $searchword, $searchword);
	}
	
	echo $mysqli->error;
	
	$stmt->bind_result($order_idDB, $fromDB, $toDB);
	$stmt->execute();
	
	//tekitan massiivi
	$allUserOrders = array();
	
		while($stmt->fetch()){
			//$alates = date_create($alatesDB)  ... see on  object(DateTime)
			//$alates->format("m/Y")    ....m/Y formaati: 
			
			$from = date_create($fromDB)->format("m/Y") ;    
			$to = date_create($toDB)->format("m/Y");
			
			$order = new StdClass();    //order on object(stdClass) ....(Order_nr=>order_idDB, From=>from, To=>to)
			$order->Order_nr = $order_idDB;
			$order->From= $from;
			$order->To = $to;
			array_push($allUserOrders, $order);	//allUserOrders on array ....(order, order)
		}
	
	return $allUserOrders;
			
	$stmt->close();
	$mysqli->close();
		
		
}	
//KONKREETNE ÜKS TELLIMUS

function getSingleOrder($edit_id, $user_id){
    
    $database = "if16_karin";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
		
	$stmt = $mysqli->prepare("SELECT Order_id, Date_from, Date_to FROM orders_katse WHERE Order_id=? AND User_id=? AND deleted IS NULL");

		$stmt->bind_param("ii", $edit_id, $user_id);
		$stmt->bind_result($order_idDB, $date_fromDB, $date_toDB);
		$stmt->execute();
		
		//tekitan objekti
		$order = new Stdclass();
		
		//saime ühe rea andmeid
		if($stmt->fetch()){
			// kasutan bind_result muutujaid ehk tabelis olevad andmed Y-m-d => m/y kujule
			
			//$from = date_create($date_fromDB)  ... esiteks date_create()
			//$from->format("m/Y")    ....teiseks m/Y formaati: 
			$from = date_create($date_fromDB)->format("m/Y") ;    
			$to = date_create($date_toDB)->format("m/Y");
			
			$order->order_id = $order_idDB;
			$order->date_from = $from;
			$order->date_to = $to;
			
			
		}else{
			// ei saanud rida andmeid kätte
			// sellist id'd ei ole olemas
			// see rida võib olla kustutatud
			//echo "Midagi läks valesti";
			//header("Location: data.php");
			$order->order_id = "";
			$order->date_from = "";
			$order->date_to = "";
		}
		
		$stmt->close();
		$mysqli->close();
		
		return $order;
		
}

//PIKENDA TELLIMUST
function changeOrder($months, $order_id, $user_id){
	$database = "if16_karin";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
	//muutmine...("UPDATE tabelinimi SET lahter1DB = MUUTUS() WHERE lahter2DB = ? AND lahter3DB = ?");
	$stmt = $mysqli->prepare("UPDATE orders_katse SET Date_to = DATE_ADD(Date_to, INTERVAL ? MONTH) WHERE Order_id=? AND User_id=? AND deleted IS NULL"); 
	$stmt->bind_param("sii",$months, $order_id, $user_id);
	// kas õnnestus salvestada
		if($stmt->execute()){
			// õnnestus
			
		}else{
			echo "Midagi läks valesti";
		}
		
		$stmt->close();
		$mysqli->close();
}

//KUSTUTA TELLIMUS
function deleteOrder($order_id, $user_id){
	$database = "if16_karin";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $database);
	
	$stmt = $mysqli->prepare("UPDATE orders_katse SET deleted=NOW() WHERE Order_id=? AND User_id=? AND deleted IS NULL");
		$stmt->bind_param("ii",$order_id, $user_id);
		
		// kas õnnestus
		if($stmt->execute()){
			                  //NULL asendati kustutamise kuupäevaga NOW()
		}else{
			echo "Midagi läks valesti";
		}
		
		$stmt->close();
		$mysqli->close();
		
	}
?>