<?php
if (!isEmpty($_POST['invite_token'])) $_SESSION['invite_token'] = $_POST['invite_token'];