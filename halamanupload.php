<form action="halamanupload.php" method="post" enctype="multipart/form-data">
    <p>Nama Workspace</p>
    <input type="text" name="nameworkspace">
    </br>
    </br>
    <p>Date Workspace</p>
    <input type="date" name="dateworkspace">
    </br>
    </br>
    <p>Add File</p>
    <input type="file" name="pdf">
    </br>
    </br>
    <input type="submit" value="Post" name="kirim">
</form>

<?php
session_start();
include 'makeworkspace.php';
include 'addinfo.php';
include 'addpdf.php';
include 'moveflow.php';
if(isset($_POST['kirim'])){
    $namaw =  $_POST['nameworkspace'];
    $datew =  $_POST['dateworkspace'];
    
    $path = "uploads/".$_FILES['pdf']['name'];
    move_uploaded_file($_FILES['pdf']['tmp_name'], $path);
    
    $filew = getcwd()."/".$path;
    echo "</br>";
    echo "MAKEWORKSPACE";
    echo "</br>";
    $idwork = makeworkspace();
    echo "</br>";
    echo "ADDINFO";
    echo "</br>";
    addinfo($idwork,$namaw,$datew);
    echo "</br>";
    echo "ADDPDF";
    echo "</br>";
    addpdf($idwork, $filew);
    echo "</br>";
    echo "MOVEWORKFLOW";
    echo "</br>";
    $moveflow = moveflow($idwork);
    
}
?>