<?php    
    function addpdf($idaddpdf, $pathfile){

        $curl = curl_init();   
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://localhost:8080/server/api/submission/workspaceitems/'.$idaddpdf,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new CURLFile($pathfile)),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: multipart/form-data',
            'X-XSRF-TOKEN: '. $_SESSION["crsf"],
            'Authorization: '.$_SESSION["auth"],
            'Cookie: DSPACE-XSRF-COOKIE='.$_SESSION["crsf"],
            ),
        ));
        curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            echo $error_msg;
        }else{
            echo "add pdf berhasil";
            echo "</br>";
        };

        curl_close($curl);

    }
?>