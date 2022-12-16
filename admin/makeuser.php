<form action="makeuser.php" method="post" enctype="multipart/form-data">
    <p>nama depan</p>
    <input type="text" name="namadepan">
    </br>
    </br>
    <p>nama belakang</p>
    <input type="text" name="namabelakang">
    </br>
    </br>
    <p>email</p>
    <input type="text" name="email">
    </br>
    </br>
    <p>password</p>
    <input type="text" name="password">
    </br>
    </br>
    <input type="submit" value="Post" name="kirim">
</form>

<?php
session_start();
include 'adduser.php';
include 'addpassword.php';
include 'addtogroup.php';

if(isset($_POST['kirim'])){
    $namadepan =  $_POST['namadepan'];
    $namabelakang =  $_POST['namabelakang'];
    $email =  $_POST['email'];
    $password =  $_POST['password'];
    
    echo "</br>";
    echo "ADD USER";
    echo "</br>";
    $uuid = adduser($namadepan, $namabelakang, $email);
    echo "</br>";
    echo "ADDINFO";
    echo "</br>";
    addpassword($uuid, $password);
    echo "</br>";
    echo "ADDTOGROUPW";
    echo "</br>";
    addtogroup($uuid);     
}
?>