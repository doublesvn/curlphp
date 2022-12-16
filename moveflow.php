<?php


function moveflow($idworkspace){

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost:8080/server/api/workflow/workflowitems',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'http://localhost:8080/server/api/submission/workspaceitems/'.$idworkspace,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: text/uri-list',
      'X-XSRF-TOKEN: '.$_SESSION["crsf"],
      'Authorization: '.$_SESSION["auth"],
      'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION["crsf"]
    )
  ));

  $response = curl_exec($curl);
  $x = json_decode($response, true);


  if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    echo $error_msg;
  }else{
    echo "move to workflow berhasil";
    echo "</br>";
    echo $x;
  }
  curl_close($curl);
}
?>