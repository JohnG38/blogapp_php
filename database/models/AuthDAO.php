<?php 

    class AuthDAO {

        public PDOStatement $statementCreate;

        function __construct( public PDO $pdo)
        {
            $this->statementCreate = $this->pdo->prepare(
                'INSERT INTO user  VALUES (DEFAULT, :firstname, :lastname, :email, :password)'
            );
        }

        function create($user) {
            $hashPassword = password_hash($user['password'], PASSWORD_ARGON2I);

            $this->statementCreate->bindValue(':firstname', $user['firstname']);
            $this->statementCreate->bindValue(':lastname', $user['lastname']);
            $this->statementCreate->bindValue(':email', $user['email']);
            $this->statementCreate->bindValue(':password', $hashPassword);
            $this->statementCreate->execute();
        }
    }


    $pdo = require_once './database/database.php';
    return new AuthDAO($pdo);

?>