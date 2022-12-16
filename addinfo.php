<?php

function addinfo($idworkspace, $nameworkspace, $dateworkspace){

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/'.$idworkspace,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_HEADER=> true,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_POSTFIELDS =>'[
    {"op":"add","path":"/sections/traditionalpageone/dc.title", "value": [{"value": "'.$nameworkspace.'"}]},
    {"op":"add","path":"/sections/traditionalpageone/dc.date.issued", "value": [{"value": "'.$dateworkspace.'"}]},
    {"op":"add","path":"/sections/license/granted", "value": true}
    ]',
    CURLOPT_HTTPHEADER => array(
      'X-XSRF-TOKEN: '.$_SESSION["crsf"],
      'Authorization: '.$_SESSION["auth"],
      'Content-Type: application/json',
      'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION["crsf"]
    ),
  ));

  curl_exec($curl);

  
  if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    echo $error_msg;
  }else{
    echo "add info berhasil";
    echo "</br>";
  };
  curl_close($curl);
}


?>
