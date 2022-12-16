<form action="halamanlogin.php" method="post" >
    <input type="text" name="usernamelogin">
    </br>
    </br>
    <input type="text" name="passwordlogin">
    </br>
    </br>
    <input type="submit" value="Post" name="login">
</form>

<?php
    session_start();
    include 'generatecrsf.php';
    include 'login.php';
    include 'status.php';
    if(isset($_POST['login'])){
        $namalogin =  $_POST['usernamelogin'];
        $katasandilogin =  $_POST['passwordlogin'];

        $inihasilcrsf = generatecrsf($namalogin, $katasandilogin);
        $inihasillogin = login($inihasilcrsf, $namalogin, $katasandilogin);
        [$auth, $crsf] = $inihasillogin;
        $_SESSION["auth"] = $auth;
        $_SESSION["crsf"] = $crsf;
        getuuidfromstatus();
        if ((!empty($_SESSION["auth"])) && (!empty($_SESSION["crsf"])) && (!empty($_SESSION["uuid"]))) {
            header('Location: menu.php');
        }else{
            echo "login gagal";
        };
    }
?>