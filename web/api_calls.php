<?php

function make_api_call($request_type, $endpoint, $payload=null){
    $ch=curl_init("https://ec2-18-119-163-218.us-east-2.compute.amazonaws.com/api/$endpoint");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if($request_type == "POST"){
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'content-type: application/x-www-form-urlencoded',
            'content-length: '.strlen($payload))
        );
    } else{
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['content-type: application/x-www-form-urlencoded']);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result=curl_exec($ch);
    curl_close($ch);

    $jsonResult=json_decode($result,true);

    return $jsonResult;
}

?>