<?php

include('config.php');

function addPermission_User($pdo, $idUser, $permissions, $sqlQuery, $role) {
    try {
        foreach ($permissions as $permission) {
            // Prepara o SQL para inserir as permissões
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindParam(1, $idUser, PDO::PARAM_INT);
            $stmt->bindParam(2, $permission, PDO::PARAM_INT);

            // Executa o INSERT
            $rowsInserted = $stmt->execute();

            if ($rowsInserted < 1) {
                return array(false, "Falha ao inserir as permissões para o " .$role);
            }
        }
        return array(true, "Permissões inseridas com sucesso.");
    } catch (Exception $e) {
        return array(false, "Erro ao inserir permissões: " . $e->getMessage());
    }
}

// Verifica se o pedido é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lê os dados recebidos via POST e decodifica o JSON
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (isset($data['username']) && isset($data['name']) && isset($data['surname']) && isset($data['password'])
        && isset($data['birthDate']) && isset($data['email']) && isset($data['phone']) && isset($data['role'])){

        $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $surname = filter_var($data['surname'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);
        $role = filter_var($data['role'], FILTER_SANITIZE_NUMBER_INT);

        try {
            // SQL para verificar se o nome de utilizador já existe
            $queryCheckUserName = "SELECT count(*) FROM USER WHERE username = :username";

            $checkUsernameEmailPhone = "SELECT * FROM USER WHERE username = ? OR email = ? OR phone = ?;";

            // SQL para inserir um novo utilizador
            $insertUserTableSQL = "
            INSERT INTO USER (username, name, surname, password, birthDate, email, phone, role, isActive, idClub)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

            // Conexão à base de dados
            $pdo = $conn;

            // Verifica se o nome de utilizador já existe
            /*
            $stmtCheck = $pdo->prepare($queryCheckUserName);
            $stmtCheck->bindParam(':username', $username);
            $stmtCheck->execute();
            $count = $stmtCheck->fetchColumn();
            */

            $stmtCheck = $pdo->prepare($checkUsernameEmailPhone);
            $stmtCheck->bindParam(1, $username);
            $stmtCheck->bindParam(2, $email);
            $stmtCheck->bindParam(3, $phone);
            $stmtCheck->execute();

            $emailError = false;
            $usernameError = false;
            $phoneError = false;

            foreach ($stmtCheck->fetchAll() as $row){
                if($row['username'] == $username){
                    $usernameError = true;
                }
                if ($row['email'] == $email){
                    $emailError = true;
                }
                if ($row['phone'] == $phone){
                    $phoneError = true;
                }
            }


            if ($usernameError || $emailError || $phoneError) {
                $message = "Erro! ";
                if ($usernameError){
                    $message .= "Nome de utilizador já em utilização.";
                }

                if ($emailError){
                    $message .= "Email já em utilização.";
                }

                if ($phoneError){
                    $message .= "Número de telefone já em utilização.";
                }

                echo json_encode(array("message" => $message), JSON_PRETTY_PRINT);
                http_response_code(406); // Not Acceptable
                die;
            } else {
                // Calcula o hash da password
                $passwordEnc = password_hash($data['password'], PASSWORD_DEFAULT);

                // Prepara a query de inserção de utilizador
                $stmtInsert = $pdo->prepare($insertUserTableSQL);

                $stmtInsert->bindParam(1, $username);
                $stmtInsert->bindParam(2, $name);
                $stmtInsert->bindParam(3, $surname);
                $stmtInsert->bindParam(4, $passwordEnc);
                $stmtInsert->bindParam(5, $data['birthDate']);
                $stmtInsert->bindParam(6, $email);
                $stmtInsert->bindParam(7, $phone);
                $stmtInsert->bindParam(8, $role);
                $stmtInsert->bindValue(9, true);
                $stmtInsert->bindValue(10, null);

                $stmtInsert->execute();

                // Obtém o ID gerado para o novo utilizador
                $generatedId = $pdo->lastInsertId();

                if ($generatedId > 0) {
                    // Atribuir permissões e inserir dados adicionais com base no papel (role)
                    switch ($data['role']) {
                        case 1: // Admin
                            $adminPermissions = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 29, 30);
                            $insertSQL = "INSERT INTO PERMISSIONS_USER (idUser, idPermission) VALUES (?, ?);";
                            $returnData = addPermission_User($pdo, $generatedId, $adminPermissions, $insertSQL, "Administrador");
                            if (!$returnData[0]) {
                                echo json_encode(array("message" => $returnData[1]), JSON_PRETTY_PRINT);
                                http_response_code(500); // Internal Server Error
                                exit();
                            }
                            break;

                        case 2: // Treinador
                            $coachPermissions = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 19, 23, 24, 25, 26, 27, 29, 30);
                            $insertSQL = "INSERT INTO PERMISSIONS_USER (idUser, idPermission) VALUES (?, ?);";
                            $returnData = addPermission_User($pdo, $generatedId, $coachPermissions, $insertSQL, "Treinador");
                            if (!$returnData[0]) {
                                echo json_encode(array("message" => $returnData[1]), JSON_PRETTY_PRINT);
                                http_response_code(500); // Internal Server Error
                                exit();
                            }

                            // Insere o treinador na tabela COACH
                            $insertCoachSQL = "INSERT INTO COACH (idUser, careerStartDate) VALUES (?, ?);";
                            $stmtCoach = $pdo->prepare($insertCoachSQL);
                            $stmtCoach->bindParam(1, $generatedId, PDO::PARAM_INT);
                            $stmtCoach->bindValue(2, $data['careerStartDate'], PDO::PARAM_STR);
                            $stmtCoach->execute();

                            break;

                        case 3: // Jogador
                            $playerPermissions = array(2, 3, 6, 7, 8, 9, 10, 19, 26, 28, 30);
                            $insertSQL = "INSERT INTO PERMISSIONS_USER (idUser, idPermission) VALUES (?, ?);";
                            $returnData = addPermission_User($pdo, $generatedId, $playerPermissions, $insertSQL, "Jogador");
                            if (!$returnData[0]) {
                                echo json_encode(array("message" => $returnData[1]), JSON_PRETTY_PRINT);
                                http_response_code(500); // Internal Server Error
                                exit();
                            }

                            // Insere o jogador na tabela PLAYER
                            $insertPlayerSQL = "INSERT INTO PLAYER (idUser, goals, assists) VALUES (?, ?, ?);";
                            $stmtPlayer = $pdo->prepare($insertPlayerSQL);
                            $stmtPlayer->bindParam(1, $generatedId, PDO::PARAM_INT);
                            $stmtPlayer->bindValue(2, 0, PDO::PARAM_INT);
                            $stmtPlayer->bindValue(3, 0, PDO::PARAM_INT);
                            $stmtPlayer->execute();

                            break;

                        case 4: // Staff
                            $staffPermissions = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 19, 20, 21, 22, 24, 26, 27, 30);
                            $insertSQL = "INSERT INTO PERMISSIONS_USER (idUser, idPermission) VALUES (?, ?);";
                            $returnData = addPermission_User($pdo, $generatedId, $staffPermissions, $insertSQL, "Staff");
                            if (!$returnData[0]) {
                                echo json_encode(array("message" => $returnData[1]), JSON_PRETTY_PRINT);
                                http_response_code(500); // Internal Server Error
                                exit();
                            }

                            // Insere o staff na tabela STAFF
                            $insertStaffSQL = "INSERT INTO STAFF (idUser, careerStartDate) VALUES (?, ?);";
                            $stmtStaff = $pdo->prepare($insertStaffSQL);
                            $stmtStaff->bindParam(1, $generatedId, PDO::PARAM_INT);
                            $stmtStaff->bindValue(2, $data['careerStartDate'], PDO::PARAM_STR);
                            $stmtStaff->execute();

                            break;
                    }

                    // Retorna sucesso
                    echo json_encode(array("message" => "Registado com sucesso!", "id" => $generatedId), JSON_PRETTY_PRINT);
                    http_response_code(201); // Created
                } else {
                    echo json_encode(array("message" => "Falha ao inserir o utilizador."), JSON_PRETTY_PRINT);
                    http_response_code(500); // Internal Server Error
                }
            }
        } catch (Exception $e) {
            echo json_encode(array("message" => $e->getMessage()), JSON_PRETTY_PRINT);
            http_response_code(500); // Internal Server Error
        }

    }else{
        echo json_encode(array("message" => "Dados incompletos. É necessário o preenchimento dos campos todos."), JSON_PRETTY_PRINT);
        http_response_code(400); // Internal Server Error
        die();
    }

}
