<a href="./koko.php">menu</a>
<?php
session_start();
$curl = curl_init();
$idpooltask =  $_POST['idpooltask'];
echo $idpooltask;
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8080/server/api/workflow/claimedtasks',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'http://localhost:8080/server/api/workflow/pooltasks/'.$idpooltask,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: text/uri-list',
    'X-XSRF-TOKEN: '.$_SESSION['crsf'],
    'Authorization: '.$_SESSION['auth'],
    'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION['crsf']
  ),
));

$response = curl_exec($curl);

curl_close($curl);
print_r($response);
?>