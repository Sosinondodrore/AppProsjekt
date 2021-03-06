<?php

session_start();
if(!(isset($_SESSION['username'])))
{
    header("Location: index.php");
}
// Definer variabler initialiser med tomme verdier
require_once "../PHP/config.php";


if (isset($_GET['upload'])){
    echo '
        <script> 
        timer = setTimeout(function() {
            TooltipMessage("Profilbilde oppdatert");
        }, 200);
        </script>
    ';
}
 
// Definer variabler initialiser med tomme verdier
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
 
// Definer variabler initialiser med tomme verdier
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Valider nytt passord
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Skriv inn et nytt passord";     
    } elseif(strlen(trim($_POST["new_password"])) < 8){
        $new_password_err = "Passord må ha minst 8 karakterer";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Valider confirm passord
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Bekreft ditt passord";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Passordene er ikke like";
        }
    }
        
    // Se etter input-error før insetting i database
    if(empty($new_password_err) && empty($confirm_password_err)){
        $sql = "UPDATE user SET password = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
            
            // Velg parametre 
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            // Utfør statement
            if(mysqli_stmt_execute($stmt)){
                // Passord endret, exit stopp session og gå til profilsiden
                session_destroy();
                header("location: profil.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Lukk statement
        mysqli_stmt_close($stmt);
    }
    
    // Lukk connection
    mysqli_close($link);
}

// Gi innlogget bruker en session ID
$userId = $_SESSION['id'];

// Fetch data om brukeren fra databasen
$result = mysqli_query($link, "SELECT * FROM user WHERE id = $userId");
$rowUser = mysqli_fetch_row($result);

//Profilbilde for bruker
$userPicture = !empty($rowUser[6]) ? $rowUser[6] : 'placeholder-profile.png'; //Hvis avatar ikke tom så bruk avatar id hvis ikke brukes placeholder.png
$userPictureURL = '../Pictures/upload/'.$userPicture;

?>


<!DOCTYPE html>
<html lang="no">
<head>
	<title>Min profil - SocialCampus</title>
	<?php include '../PHP/head.php';?>
</head>
<body>
	<div class="wrapper">

	<?php
        include '../PHP/nav.php';?>
        <section class="profile-wrapper">
        	<div class="profile-container">
	        	<div class="profile-header">
	        		<div class="imgContainer img-upload">
                    <form id="img-upload-post" method="post" action="../PHP/profilePhoto.php" enctype="multipart/form-data">
	        		    <img id="img-upload-result" src="<?php echo $userPictureURL; ?>">
                        <input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg" onchange="checkImgFileSize(this)"> <!-- Kjører opplastning av bilde -->
                        <label id="img-name" for="avatar">Velg bilde</label>
                    </form>
	        		</div>
                    <h1><?php echo $rowUser[1]?></h1>
                    <!-- <p><a href="../PHP/logout.php"><i class="fas fa-sign-out-alt"></i>Logg ut</a></p> -->
	        	</div>

	        	<div class="profile-info">
	        		<div class="container">
	        			<h3>Brukernavn</h3>
	        			<div class="inputContainer">
                            <i class="fas fa-user"></i>
	        				<input type="text"class="input" disabled value="<?php echo $rowUser[1]?>">
	        			</div>
	        		</div>

	        		<div class="container">
	        			<h3>E-post</h3>
	        			<div class="inputContainer">
	        				<i class="fas fa-at"></i>
	        				<input type="text" class="input" disabled value="<?php echo $rowUser[5]?>">
	        			</div>
	        		</div>

	        		<div class="container">
	        			<h3>Fullt navn</h3>
	        			<div class="inputContainer">
	        				<i class="fas fa-address-book"></i>
	        				<input type="text" class="input" value="<?php echo $rowUser[3]?>" disabled>
	        			</div>
	        		</div>

	        		<div class="container">
	        			<h2>Endre passord</h2>
	        		</div>

	        		<div class="container">
	        			<h3>Gammelt passord</h3>
	        			<div class="inputContainer">
	        				<i class="fas fa-key"></i>
	        				<input class="input" type="password" placeholder="••••••••••" autocomplete="false">
	        			</div>
	        		</div>

                    <div class="container 
                    <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                    <h3>Nytt passord</h3>
                    <div class="inputContainer">
                        <i class="fas fa-key"></i>
                        <input class="input" type="password" name="new_password" placeholder="••••••••••" autocomplete="false""
                        <?php echo $new_password; ?>">
                        <span class="help-block">
                        <?php echo $new_password_err; ?></span>
                    </div>
                    </div>
                        
                    <div class="container">
                    <h3>Bekreft passord</h3>
                    <div class="inputContainer <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                        <i class="fas fa-key"></i>
                        <input class="input" type="password" ame="confirm_password" placeholder="••••••••••">
                        <span class="help-block">
                        <?php echo $confirm_password_err; ?></span>
                    </div>
                    </div>

                    <div class="button-wrapper">
                        <input type="submit" class="btn btn-primary" value="Lagre endringer">
                    </div>

	            </div>
        	</div>
        </section>
        
	</div>
        
<?php include '../PHP/footer.php';?>
        
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</body>
</html>