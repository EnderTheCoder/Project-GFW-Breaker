<?php

if (count($_POST) == 1) { 
    $Json = json_decode($_POST[0], true)
    foreach($Json as $key => $value) {
        $_POST[$key] = $value
    }
}

var_dump($_POST);