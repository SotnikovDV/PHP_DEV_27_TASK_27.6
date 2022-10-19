<?php
/*
$notEqual = false;
$errors    = [];  

if (isset($_POST['submit'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $email = $_POST['email'];
    $remember = isset($_POST['remember']);

    // проверяем совпадение паролей
    if ($password !== $password2) {
        $notEqual = true;
        // header("Location: register?notequal");
    } else {

        $usr = $user->addUser($login, $password, $email);
        //var_dump($usr);

        if (!$usr) {
            $errors = $user->lastErrors;
        } else {
            $usr = $user->logon($login, $password, $remember);
            header("Location: /");
        }
        
    }   
}
*/
?>

<div id="register" class="tabcontent">
  <form class="modal-content" action="/action_page.php">
    <div class="container">
      <h1>Sign Up</h1>
      <p>Please fill in this form to create an account.</p>
      <hr>
      <label for="email"><b>Email</b></label>
      <input type="text" placeholder="Enter Email" name="email" required>

      <label for="psw"><b>Password</b></label>
      <input type="password" placeholder="Enter Password" name="psw" required>

      <label for="psw-repeat"><b>Repeat Password</b></label>
      <input type="password" placeholder="Repeat Password" name="psw-repeat" required>
      
      <label>
        <input type="checkbox" <?php if ($remember) { echo 'checked'; } ?> name="remember" style="margin-bottom:15px"> Remember me
      </label>

      <p>By creating an account you agree to our <a href="#" style="color:dodgerblue">Terms & Privacy</a>.</p>

      <div class="clearfix">
        <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
        <button type="submit" class="signupbtn">Sign Up</button>
      </div>
    </div>
  </form>
</div>