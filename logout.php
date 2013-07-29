<?php

require_once("Utils/config.php");

session_destroy();

header("Location: index.php");