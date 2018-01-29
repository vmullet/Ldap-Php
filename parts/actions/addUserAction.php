<?php
/**
 * Created by PhpStorm.
 * User: jbuisine
 * Date: 1/29/18
 * Time: 10:06 AM
 */

// retrieving and checking data from add user form

// default empty array
$errors = array();

// check data
if(!isset($_POST['userName'])){
    $errors = "Please select correct user name";
}
if(!isset($_POST['userSurname'])){
    $errors = "Please select correct user surname";
}

// check errors
if(!empty($errors)){
    foreach ($errors as &$error): ?>
        <p style="color:red;"> <?php echo $error ?> </p>
   <?php endforeach;
}else{

    // getting form data
    $userId = $_POST['userId'];
    $userName = $_POST['userName'];

    // TODO check add user parameters (need to add group ?)
    $ldapService->addUser($userId, $userName);
}
