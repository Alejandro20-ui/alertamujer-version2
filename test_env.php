<?php
echo json_encode([
    "MYSQLHOST" => getenv("MYSQLHOST"),
    "MYSQLUSER" => getenv("MYSQLUSER"),
    "MYSQLPASSWORD" => getenv("MYSQLPASSWORD"),
    "MYSQLDATABASE" => getenv("MYSQLDATABASE"),
    "MYSQL_DATABASE" => getenv("MYSQL_DATABASE"),
    "DATABASE" => getenv("DATABASE"),
    "MYSQLPORT" => getenv("MYSQLPORT"),
    "MYSQL_PORT" => getenv("MYSQL_PORT")
], JSON_PRETTY_PRINT);
