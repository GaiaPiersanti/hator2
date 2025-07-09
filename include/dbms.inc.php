<?php

    $host = "localhost";
    $user = "root";
    $password = ""; // Modifica questa riga con la tua password
    $database = "hator_db";

    function cifratura($password, $email) {
        /* cifratura goes here */

        return md5($password.md5($email)); // esempio di cifratura semplice
    }



    $conn = new mysqli($host, $user, $password, $database);
        // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "<br/>");
    }

    /* connection to mysql succesful

*/