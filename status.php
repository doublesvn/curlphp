<?php

function getuuidfromstatus(){
   include 'getuuid.php';
   $curl = curl_init();

   curl_setopt_array($curl, array(
   CURLOPT_URL => "http://localhost:8080/server/api/authn/status",
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_ENCODING => '',
   CURLOPT_MAXREDIRS => 10,
   CURLOPT_TIMEOUT => 0,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
   CURLOPT_CUSTOMREQUEST => 'GET',
   CURLOPT_HTTPHEADER => array(
      'Authorization: '.$_SESSION["auth"],
      ),
   ));

      $resp = curl_exec($curl);
      $x = json_decode($resp, true);
      curl_close($curl);
      $uuidlink = $x["_links"]["eperson"]["href"];
      $uuid = getuuid($uuidlink);
      $_SESSION["uuid"] = $uuid;
}

?>
