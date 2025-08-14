<?php
$img='https://joymarket.ua/content/images/1/356x356l85nn0/31298627200991.webp';


 

function get_ext($fileName){
    $data=explode('.',$fileName);
    $data=explode('?',end($data));
    echo $data;
}