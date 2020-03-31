<?php
if (count($_POST) == 0 && !isEmpty(json_decode(file_get_contents('php://input'), true)))
    $_POST = json_decode(file_get_contents('php://input'), true);