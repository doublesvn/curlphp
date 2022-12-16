<form action="accept.php" method="post" >
    <input type="text" name="idclaimtask">
    </br>
    </br>
    <input type="submit" value="accept" name="accept">
</form>
<?php
$curl = curl_init();
session_start();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://localhost:8080/server/api/workflow/claimedtasks/search/findByUser?uuid='.$_SESSION['uuid'],
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
$claimedtaskscollect = $x["_embedded"]["claimedtasks"];
foreach ($claimedtaskscollect as $claimtasks) {
    echo "Id claimtask : ".$claimtasks['id']."       Id workflowitem : ".$claimtasks['_embedded']['workflowitem']['id']."        Nama workflowitem :".$claimtasks['_embedded']['workflowitem']['_embedded']['item']['name'];
    echo "</br>";
};