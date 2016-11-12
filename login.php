<?php

require("functions.php");

// kui on juba sisse loginud siis suunan data lehele
if (isset($_SESSION["userId"])){
		
		//suunan sisselogimise lehele
		header("Location: data.php");
		exit();
		
}

//muutujad
$firstname = "";
$lastname = "";
$address = "";
$city = "";
$zip = "";
$email = "";
$password = "";
$gender = "";
$loginEmail = "";
//veateated
$loginError = "";
$loginEmailError = $loginPasswordError = "";
$firstnameError = "";
$lastnameError = "";
$emailError = "";
$addressError = "";
$zipError = "";
$cityError = "";

$passwordError = "";
$passwordAgainError = "";

if(isset($_POST["gender"])){
	if(!empty($_POST["gender"])){            
		$gender = $_POST["gender"];
	}
}

if( isset($_POST["firstname"]) ){
	//TRUE pärast nupule vajutamist
	if( empty($_POST["firstname"]) ){
		$firstnameError = "Kohustuslik väli";
	} else {
		$firstname = cleanInput($_POST["firstname"]);
	}
	
	if( empty($_POST["lastname"]) ){
		$lastnameError = "Kohustuslik väli";
	} else {
		$lastname = cleanInput($_POST["lastname"]);
	}
	
	if( empty($_POST["address"]) ){
		$addressError = "Kohustuslik väli";
	} else {
		$address = cleanInput($_POST["address"]);
	}
	if( empty($_POST["city"]) ){
		$cityError = "Kohustuslik väli";
	} else {
		$city = cleanInput($_POST["city"]);
	}
	
	if( empty($_POST["zip"]) ){
		$zipError = "Kohustuslik väli";
	} else {
		$zip = cleanInput($_POST["zip"]);
	}
	
	if( empty($_POST["email"]) ){
		$emailError = "Kohustuslik väli";
	} else {
		$email = cleanInput($_POST["email"]);
	}
	
	if( empty($_POST["password"]) ){
		$passwordError = "Kohustuslik väli";
	}else{
		if(strlen($_POST["password"]) < 8){
			$passwordError = "Salasõna peab olema vähemalt 8 tähemärki";
		} else {
			if($_POST["password"] == $_POST["passwordAgain"]){
				$password = cleanInput($_POST["password"]);
				$password = hash("sha512", $password);
			}
		}
	}
	
	if( empty($_POST["passwordAgain"]) ){
		$passwordAgainError = "Kohustuslik väli";
	}else{
		if($_POST["password"] != $_POST["passwordAgain"]){
		$passwordAgainError = "Salasõnad ei kattu";	
		}
	}
} 

// ühtegi errorit
	
if( isset($_POST["firstname"]) &&
	empty($firstnameError) &&
	empty($lastnameError) &&
	empty($addressError) &&
	empty($cityError) &&
	empty($zipError) &&
	empty($emailError) &&
	empty($passwordError) &&
	empty($passwordAgainError)
	) {
		signUp($firstname, $lastname,  $gender, $address, $city, $zip, $email, $password);
	}

/*
******************
         LOGIN   *
******************
*/
$loginError = "";
if(isset($_POST["loginEmail"]) && isset($_POST["loginPassword"]) &&
!empty($_POST["loginEmail"]) && !empty($_POST["loginPassword"])) {
		
		$loginEmail = cleanInput($_POST["loginEmail"]);
		$loginPassword = cleanInput($_POST["loginPassword"]);
		$loginError = login($loginEmail, $loginPassword);   //kutsun funktsiooni
}
if(isset($_POST["loginEmail"])){
	if(empty($_POST["loginEmail"])){
		$loginEmailError = "Kohustuslik väli";
	}else{
		$loginEmail = $_POST["loginEmail"];
	}
}
if(isset($_POST["loginPassword"] )){
	if(empty($_POST["loginPassword"])){
		$loginPasswordError = "Kohustuslik väli";
	}
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Ajakirja tellimine</title>
</head>
<body>
<h4>Tellimiseks ja tellimuste vaatamiseks logi sisse</h4>
<form method="post">
<p style="color:red;"><?php echo $loginError; ?></p>
<input name="loginEmail" type="text" placeholder="E-post" value="<?=$loginEmail;?>">  <?php echo $loginEmailError; ?>
<br>
<input name="loginPassword" type="password" placeholder="Salasõna"> <?php echo $loginPasswordError; ?>
<br>
<br>
<input name="login" type="submit" value="Logi sisse">
</form>
<form method="post">
<br>
<br>


<!--Kontaktandmed -->
<h4>Loo kasutaja</h4>
<form method="post">
<input name="firstname" type="text" placeholder="Eesnimi" value="<?=$firstname;?>"> <?php echo $firstnameError; ?> <br>
<input name="lastname" type="text" placeholder="Perekonnanimi" value="<?=$lastname;?>"> <?php echo $lastnameError; ?> <br>
<input name="address" type="text" placeholder="Tänav maja nr/ krt" value="<?=$address;?>"> <?php echo $addressError; ?> <br>
<input name="city" type="text" placeholder="Linn/asula" value="<?=$city;?>"> <?php echo $cityError; ?> <br>
<input name="zip" type="text" placeholder="Sihtnumber" value="<?=$zip;?>"> <?php echo $zipError; ?> <br>
<input name="email" type="text" placeholder="E-post" value="<?=$email;?>"> <?php echo $emailError; ?> <br> 
<input name="password" type="password" placeholder="Salasõna"> <?php echo $passwordError; ?> <br>
<input name="passwordAgain" type="password" placeholder="Salasõna uuesti"> <?php echo $passwordAgainError; ?> <br>
<br>
<br>

				   
<!--RADIO -->
<?php if($gender == "female"){ ?>
Naine<input name="gender" type="radio" value="female" checked >

<?php } else { ?>
Naine<input name="gender" type="radio" value="female">

<?php } ?>
<?php if($gender == "male"){ ?>
Mees<input name="gender" type="radio" value="male" checked >

<?php } else { ?>
Mees<input name="gender" type="radio" value="male">

<?php } ?>

<?php if($gender == "" || $gender == "none"){ ?>
Määramata<input name="gender" type="radio" value="none" checked>
<?php } else {?>
Määramata<input name="gender" type="radio" value="none">
<?php } ?>



<br>
<br>
<input type="submit" value="Loo kasutaja">
</form>
</body>
</html>

