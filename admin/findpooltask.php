<form action="claim.php" method="post" >
    <input type="text" name="idpooltask">
    </br>
    </br>
    <input type="submit" value="Claim" name="claim">
</form>

<?php
session_start();
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8080/server/api/workflow/pooltasks/search/findByUser?page=0&size=50&uuid='.$_SESSION['uuid'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'X-XSRF-TOKEN: '.$_SESSION['crsf'],
    'Authorization: '.$_SESSION['auth'],
    'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION['crsf']
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$x = json_decode($response, true);
print_r($x);
$pooltaskscollect = $x["_embedded"]["pooltasks"];
foreach ($pooltaskscollect as $pooltasks) {
  echo "Id pooltasks : ".$pooltasks['id']."       Id workflowitem : ".$pooltasks['_embedded']['workflowitem']['id']."        Nama workflowitem :".$pooltasks['_embedded']['workflowitem']['_embedded']['item']['name'];
  echo "</br>";
};
?>