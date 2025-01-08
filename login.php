<?php

// Inclui o ficehiro de configuração da base de dados
#include('config.php');
require_once 'config.php';

// Verifica se o pedido é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lê os dados recebidos via POST e decodifica o JSON
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (isset($data['username']) && isset($data['password'])) {

        $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
        // Prepara a query SQL para selecionar o utilizador
        $query = "SELECT * FROM USER WHERE username = :username";

        try {
            // Obtém a conexão à base de dados
            $pdo = $conn;
            
            // Prepara a query
            $stmt = $pdo->prepare($query);

            // Atribui os valores aos parâmetros
            $stmt->bindParam(':username', $username);
            //$stmt->bindParam(':password', $passwordEnc);

            // Executa a query
            $stmt->execute();

            // Verifica se encontrou algum resultado
            if ($row = $stmt->fetch()) {

                if (!password_verify($data['password'], $row['password'])){
                    http_response_code(404);
                    echo json_encode(array("message" => "Password Incorreta."));
                    die;
                }

                session_start();
                $_SESSION['id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['idClub'] = $row['idClub'];
                $_SESSION['role'] = $row['role'];

                //Buscar as permissões
                $query = "SELECT p.id, p.name, p.description, p.icon, p.isMenu 
                            FROM PERMISSIONS_USER AS pu
                            JOIN PERMISSIONS AS p ON pu.idPermission = p.id  
                            WHERE pu.idUser = :idUser";

                $permissions = [];

                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idUser', $row['id'], PDO::PARAM_INT);
                $stmt->execute();
                
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $permissions[] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'icon' => $row['icon'],
                        'isMenu' => (bool)$row['isMenu']
                    ];
                }

                $_SESSION['permissions'] = $permissions;

                http_response_code(200);
                echo json_encode(array("message" => "Login efetuado com sucesso."));
            } else {
                // Se o utilizador não foi encontrado, retorna um erro
                http_response_code(404);
                echo json_encode(array("message" => "Nome de utilizador ou password incorretos."));
            }

        } catch (PDOException $e) {
            // Em caso de erro, retorna o erro com status 500
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage()));
        }

    } else {
        // Se os campos necessários não foram enviados, retorna um erro
        http_response_code(400);
        echo json_encode(array("message" => "Nome de utilizador e senha são necessários."));
    }
}
?>