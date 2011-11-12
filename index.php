<?php

$error = '';


//Extremely accurate email address validation function
function validEmail($email) {
  $isValid = true;
  $atIndex = strrpos($email, "@");
  if(is_bool($atIndex) && !$atIndex) {
    $isValid = false;
  }
  else {
    $domain = substr($email, $atIndex+1);
    $local = substr($email, 0, $atIndex);
    $localLen = strlen($local);
    $domainLen = strlen($domain);
    if ($localLen < 1 || $localLen > 64) {
      // local part length exceeded
      $isValid = false;
    }
    else if ($domainLen < 1 || $domainLen > 255) {
      // domain part length exceeded
      $isValid = false;
    }
    else if ($local[0] == '.' || $local[$localLen-1] == '.') {
      // local part starts or ends with '.'
      $isValid = false;
    }
    else if(preg_match('/\\.\\./', $local)) {
      // local part has two consecutive dots
      $isValid = false;
    }
    else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
      // character not valid in domain part
      $isValid = false;
    }
    else if(preg_match('/\\.\\./', $domain)) {
      // domain part has two consecutive dots
      $isValid = false;
    }
    else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local))) {
      // character not valid in local part unless 
      // local part is quoted
      if(!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))) {
        $isValid = false;
      }
    }
    if($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
      // domain not found in DNS
      $isValid = false;
    }
  }
  return $isValid;
}


//Function to encode/encrypt data automagically
function crypTic($data, $echoit) {

  //Hash out the first string
  $string1 = md5($data).sha1($data);

  //Hash out a second string
  $string2 = sha1($string1).md5($string1);

  //Combine for final string
  $string3 = sha1(md5($string2));

  if($echoit) {
    //Echo final string
    echo $string3;
  }
  else {
    //Return final string
    return $string3;
  }

}




if(isset($_POST['submit'])) {
  if(!validEmail($_POST['email'])) {
    $error = 'Please enter a valid email, as this is where your API key will be sent.';
  }
  else {
    $to = $_POST['email'];
    $subject = "ENTER SUBJECT HERE";
    $apikey = crypTic($to, false);

    $db = mysql_connect('MYSQL HOSTNAME', 'DB_USER', 'USER_PW');
    mysql_select_db('DB_NAME'); //insert appropriate data

    $double_check = mysql_query('SELECT * FROM  `TABLE_NAME` WHERE `email` = "$to"') or exit('Error: Email already exists in our database!'); //check for duplicates
    $num_rows = mysql_num_rows($double_check); //number of rows where duplicates exist
    if($num_rows == 0) {
      $sql="INSERT INTO `TABLE_NAME` (id, hash, email) VALUES ('', '$apikey', '$to')";
      if(!mysql_query($sql)) {
        $error = 'Error: Email already exists in our database.';
      }
      else {
        mysql_close();
        $message = "\n

ENTER SOME WELCOME TEXT HERE

+++++++++++++++++++++++++++++++++++++
 GETTING STARTED
+++++++++++++++++++++++++++++++++++++\n

Your API Key is: ".$apikey."\n


";

        $headers = "From: YOUR@EMAIL.COM\r\n";
        $headers .= "X-Mailer: PHP/".phpversion();
        mail($to, $subject, $message, $headers);
        header("Location: ?sent=true"); // Your code here to handle a successful verification
      }
    }
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>API Key Signup</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>

	<body>
    <div id="message">
      <?php if($_GET['sent'] == 'true' && empty($error)) { echo '<h4>Please check your email for your API key</h4>'; } ?>
      <?php if(!empty($error)) { echo '<h4>'.$error.'</h4>'; } ?>
    </div>
    <div id="form">
      <form action="" method="post">
        <label for="email">Email
          <input type="text" id="email" name="email" size="32" value="<?php echo $_POST['email']; ?>" class="text" />
        </label>
        <input type="submit" name="submit" value="submit" class="submit" />
      </form>
    </div>
	</body>
</html>