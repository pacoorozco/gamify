<?php

require_once dirname(__FILE__) . '/Database.class.php';
require_once dirname(__FILE__) . '/PassHash.class.php';

define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);

class User {

    private $database;

    function __construct()
    {
        $this->database = new Database();
    }

    public function create($username, $email, $password)
    {

        // First check if user already existed in db
        if ( ! $this->isUserExists($username)) {
            // insert query
            $this->database->query('INSERT INTO members (uuid, username, email, password, api_key) VALUES (:uuid, :username, :email, :password, :api_key)');
            $this->database->bind(':uuid', getNewUUID());
            $this->database->bind(':username', $username);
            $this->database->bind(':email', $email);
            $this->database->bind(':password', PassHash::hash($password));
            $this->database->bind(':api_key', $this->generateApiKey());

            $this->database->execute();

            // Check for successful insertion
            if ($this->database->lastInsertId()) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
    }

    /**
     * Checking for duplicate user by username address
     * @param String $username to check in db
     * @return boolean
     */
    private function isUserExists($username)
    {
        $this->database->query('SELECT id FROM members WHERE username = :username');
        $this->database->bind(':username', $username);
        $this->database->fetchOne();

        return ($this->database->rowCount() > 0);
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Checking user login
     * @param String $username User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($username, $password)
    {

        global $CONFIG;

        $password_hash = null;

        // fetching user by username
        $this->database->query('SELECT password FROM members WHERE username = :username AND disabled = 0 LIMIT 1');
        $this->database->bind(':username', $username);
        $row = $this->database->fetchOne();

        if ($this->database->rowCount() > 0) {
            // Found user with the username
            // Now verify the password

            switch ($CONFIG['authentication']['type']) {
                case 'LDAP':
                    // we will use LDAP authentication
                    if (getLDAPAuth(
                        $username,
                        $password,
                        $CONFIG['LDAP']['host'],
                        $CONFIG['LDAP']['basedn'],
                        $CONFIG['LDAP']['filter']
                    )) {
                        // User password is correct
                        return true;
                    } else {
                        // user password is incorrect
                        return false;
                    }
                    break;
                default:
                    // we will use LOCAL authentication
                    // if (PassHash::check_password($password_hash, $password)) {
                    if (md5($password) == $row['password']) {
                        // User password is correct
                        return true;
                    } else {
                        // user password is incorrect
                        return false;
                    }
            }
        } else {
            // user not existed with the username
            return false;
        }
    }

    /**
     * Fetching user by username
     * @param String $email User email id
     */
    public function getByUsername($username)
    {
        $this->database->query('SELECT uuid, username, api_key, disabled, register_time FROM members WHERE username = :username LIMIT 1');
        $this->database->bind(':username', $username);
        $user = $this->database->fetchOne();

        if ($this->database->rowCount() > 0) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id)
    {
        $this->database->query('SELECT api_key FROM members WHERE id = :id');
        $this->database->bind(':id', $user_id);
        $row = $this->database->fetchOne();

        if ($this->database->rowCount() > 0) {
            return $row['api_key'];
        } else {
            return null;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key)
    {
        $this->database->query('SELECT id FROM members WHERE api_key = :api_key');
        $this->database->bind(':api_key', $api_key);
        $row = $this->database->fetchOne();

        if ($this->database->rowCount() > 0) {
            return $row['id'];
        } else {
            return null;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key)
    {
        $this->database->query('SELECT id FROM members WHERE api_key = :api_key');
        $this->database->bind(':api_key', $api_key);
        $this->database->fetchOne();

        return ($this->database->rowCount() > 0);
    }
}
