<?php

const DB_SERVER = "localhost";
const DB_USERNAME = "animac_bot";
const DB_PASSWORD = "xW5bM1rO2v";
const DB_NAME = "animac_bot";

$connect = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_set_charset($connect, "utf8mb4");

