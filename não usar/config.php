<?php

    $dbHost = 'localhost';
    $dbUsername = 'root';
    $dbPassword = '';
    $dbName = 'fly';

    $conexao = new mysqli($dbHost,$dbUsername,$dbPassword)

    if($conexao->connect_errno)
    {
        echo "Erro";
    }
    else 
    {
        echo "conectado";
    }
?>

