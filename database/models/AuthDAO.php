<?php 

    class AuthDAO {

        public PDOStatement $statementCreate; // create user
        public PDOStatement $statementRead; // read user from user name
        public PDOStatement $statementReadFromId; // read user from id
        public PDOStatement $statementCreateSession; 
        public PDOStatement $statementReadSession; // read session by id
        public PDOStatement $statementDeleteSession;

        function __construct( public PDO $pdo)
        {
            ///user
            $this->statementCreate = $this->pdo->prepare(
                'INSERT INTO user  VALUES (DEFAULT, :firstname, :lastname, :email, :password)'
            );

            $this->statementRead = $this->pdo->prepare(
                'SELECT * FROM user WHERE email=:email'
            );

            $this->statementReadFromId = $this->pdo->prepare(
                'SELECT * FROM user WHERE id=:id'
            );

            // session
            $this->statementCreateSession = $this->pdo->prepare(
                'INSERT INTO session VALUES(:sessionId, :userid)'
            );

            $this->statementReadSession = $this->pdo->prepare(
                'SELECT * FROM session WHERE id=:id'
            );

            $this->statementDeleteSession = $this->pdo->prepare(
                'DELETE FROM session WHERE id=:id'
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

        function getUser($email) {
            $this->statementRead->bindValue(':email', $email);
            $this->statementRead->execute();

            return $this->statementRead->fetch();
        }

        function createSession($userId) {

            $sessionId = bin2hex(random_bytes(32));

            $this->statementCreateSession->bindValue(':sessionId', $sessionId);
            $this->statementCreateSession->bindValue(':userid', $userId);
            $this->statementCreateSession->execute();

            $signature = hash_hmac('sha256', $sessionId, 'formation dwwm');

            // on crée notre cookie
            setcookie('session', $sessionId, time() + 60 * 60 * 24 * 14, "", "", false, true);
            setcookie('signature', $signature, time() + 60 * 60 * 24 * 14, "", "", false, true);
        }

        function getUserById($userId) {
            $this->statementReadFromId->bindValue(':id', $userId);
            $this->statementReadFromId->execute();

            return $this->statementReadFromId->fetch();
        }

        function getSessionById($sessionId) {
            $this->statementReadSession->bindValue(':id', $sessionId);
            $this->statementReadSession->execute();

            return $this->statementReadSession->fetch();
        }

        function isLoggedIn() {
    
            $sessionId = $_COOKIE["session"] ?? '';
            $signature = $_COOKIE["signature"] ?? '';
    
            if($sessionId && $signature) {
                $hash = hash_hmac('sha256', $sessionId, 'formation dwwm');
                $session = $this->getSessionById($sessionId);

                if(hash_equals($signature, $sessionId)) {
                    $session = $this->getSessionById(($sessionId));

                    if($session) {
                        $user = $this->getUserById($session['userid']);
                    }
                }
            }
    
            return $user ?? false;
        }
    
        function logout($sessionId) {
            $this->statementDeleteSession->bindValue(':id', $sessionId);
            $this->statementDeleteSession->execute();
            setcookie('session', '', time() -1);
            setcookie('signature', '', time() -1);
        }
    }


    global $pdo;
    return new AuthDAO($pdo);

?>