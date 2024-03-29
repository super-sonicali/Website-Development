<?php
    // Initialize the session
    session_start();
    
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
     
    function redirect($url) {
        header("location: ".$url);
        exit();
    }

    // Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        redirect("login.php");
    }

    // Include config file
    require_once "config.php";
     
    // Define variables and initialize with empty values
    $username = $password = $confirm_username = $id1 = $id = "";
    $username_err = $password_err = $confirm_username_err = $id_err = "";

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if(!empty($_POST["Login"]))
        {
            // Check if username is empty
            if(empty(test_input($_POST["username"]))) {
                $username_err = "Please enter username.";
            } else{
                if (test_input($_POST["username"]) == $_SESSION["username"]) {
                    $username = test_input($_POST["username"]);
                }
                else{
                    redirect("logout.php");
                    //if you make a mistake you get logged out
                }
            }
            
            // Check if password is empty
            if(empty(test_input($_POST["password"]))) {
                $password_err = "Please enter your password.";
            } else{
                $password = test_input($_POST["password"]);
            }
            
            // Validate credentials
            if(empty($username_err) && empty($password_err)) {
                
                // Prepare a select statement
                $sql = "SELECT id, username, password FROM users WHERE username = ?";
                
                if($stmt = mysqli_prepare($link, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "s", $param_username);
                    
                    // Set parameters
                    $param_username = $username;
                    
                    // Attempt to execute the prepared statement
                    if(mysqli_stmt_execute($stmt)) {
                        // Store result
                        mysqli_stmt_store_result($stmt);
                        
                        // Check if username exists, if yes then verify password
                        if(mysqli_stmt_num_rows($stmt) == 1) {                    
                            // Bind result variables
                            mysqli_stmt_bind_result($stmt, $id1, $username, $hashed_password);
                            if(mysqli_stmt_fetch($stmt)) {
                                if(password_verify($password, $hashed_password)) {
                                    
                                } else{
                                    redirect("logout.php");
                                    //if you make a mistake you get logged out
                                }
                            }
                        } else{
                            // Display an error message if username doesn't exist
                            redirect("logout.php");
                            //if you make a mistake you get logged out lol
                        }
                    } else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        } 
            
        if(!empty($_POST["Delete"]))
        {
            if(empty(test_input($_POST["id"]))) {
                $id_err = "Please enter a id.";     
            } elseif(strlen(test_input($_POST["id"])) < 0) {
                $id_err = "Please enter a valid id";
            } elseif (test_input($_POST["id"]) != $_SESSION["id"]) {
                $id_err = "You cannot delete other people's accounts here.";
            } else{
                $id = test_input($_POST["id"]);
            }
            
            // Validate confirm username
            if(empty(test_input($_POST["confirm_username"]))) {
                $confirm_username_err = "Please confirm username.";     
            } elseif (test_input($_POST["username"]) != $_SESSION["username"]) {
                $username_err = "You cannot delete other people's accounts here.";
            } else{
                $confirm_username = test_input($_POST["confirm_username"]);
            }
            
            // Check input errors before inserting in database
            if(empty($id_err) && empty($confirm_username_err)) {
                
                // Prepare a delete statement
                $sql = "DELETE FROM users WHERE id = ?";
             
                if($stmt = mysqli_prepare($link, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "s", $param_id);
                    
                    // Set parameters
                    $param_id = $id;

                   //Attempt to execute the prepared statement
                    if(mysqli_stmt_execute($stmt)) {
                        // Redirect to index page
                        redirect("logout.php");
                    } else{
                        echo "Something went wrong. Please try again later.";
                    }
                }
                 
                // Close statement
                mysqli_stmt_close($stmt);
            }
        } 
        // Close connection
        mysqli_close($link);    
    }

    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- i hate js-->
        <title>Alestorm</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="index.png" />
        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="stylish.css"> 
        <!-- i hate js--> 
    </head>
    <body class="myPage" data-spy="scroll" data-target=".navbar" data-offset="50">
        <?php include 'nav.php'; ?>
		<div class="wrapper">
        <h2>Delete Users</h2>
        <p>Please fill in your account details</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : '';  echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                    <label >Username</label>
                    <input  type="text" name="username" class="form-control" placeholder="Username" value="<?php echo $username; ?>">
                    <label >Password</label>
                    <input type="password" name="password" placeholder="password" class="form-control">
                    <span class="help-block"><?php echo $password_err; echo $username_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" name = "Login" value="Login">
                </div>
                <?php echo " User: " . $username. ", id: " . $id1. " will be deleted<br> Warning: deletion is permanant"; ?>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($id_err)) ? 'has-error' : '';  echo (!empty($confirm_username_err)) ? 'has-error' : ''; ?>">
                    <div class="form-group <?php?>">    
                        <label>ID</label>
                        <input type="text" name="id" class="form-control" value="<?php echo $id; ?>">
                        <span class="help-block"><?php echo $id_err; ?></span>
                        <label>Confirm Username</label>
                        <input type="text" name="confirm_username" class="form-control" value="<?php echo $confirm_username; ?>">
                        <span class="help-block"><?php echo $confirm_username_err; ?></span>
                    </div>
                    <div class="form-group <?php?>">
                        <input type="submit" class="btn btn-primary" name = "Delete" value="Delete">
                        <input type="reset" class="btn btn-default" value="Reset">
                    </div>
                </div>
            </form>
        </div>
        <?php include 'foot.php'; ?>
    </body>
</html>