<a href="./koko.php">menu</a>

<?php
include 'variable.php';
$curl = curl_init();
session_start();
$idclaimtask =  $_POST['idclaimtask'];

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8080/server/api/workflow/claimedtasks/'.$idclaimtask,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'submit_approve=true',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded',
    'X-XSRF-TOKEN: '.$_SESSION['crsf'],
    'Authorization: '.$_SESSION['auth'],
    'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION['crsf']
  ),
));

$response = curl_exec($curl);

curl_close($curl);
print_r($response);
?>