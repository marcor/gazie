<?php

/**
 * Handles the user registration
 * @author Panique
 * @link http://www.php-login.net
 * @link https://github.com/panique/php-login-advanced/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Registration
{
    /**
     * @var object $db_connection The database connection
     */
    private $db_connection            = null;
    /**
     * @var bool success state of registration
     */
    public  $registration_successful  = false;
    /**
     * @var bool success state of verification
     */
    public  $verification_successful  = false;
    /**
     * @var array collection of error messages
     */
    public  $errors                   = array();
    /**
     * @var array collection of success / neutral messages
     */
    public  $messages                 = array();

    /**
     * the function "__construct()" automatically starts whenever an object of this class is created,
     * you know, when you do "$login = new Login();"
     */
    public function __construct()
    {
        session_start();

        // if we have such a POST request, call the registerNewUser() method
        if (isset($_POST["register"])) {
            $this->registerNewUser($_POST['student_name'], $_POST['student_email'], $_POST['student_password_new'], $_POST['student_password_repeat'], $_POST["captcha"]);
        // if we have such a GET request, call the verifyNewUser() method
        } else if (isset($_GET["id"]) && isset($_GET["verification_code"])) {
            $this->verifyNewUser($_GET["id"], $_GET["verification_code"]);
        }
    }

    /**
     * Checks if database connection is opened and open it if not
     */
    private function databaseConnection()
    {
        // connection already opened
        if ($this->db_connection != null) {
            return true;
        } else {
            // create a database connection, using the constants from config/config.php
            try {
                // Generate a database connection, using the PDO connector
                // @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
                // Also important: We include the charset, as leaving it out seems to be a security issue:
                // @see http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers#Connecting_to_MySQL says:
                // "Adding the charset to the DSN is very important for security reasons,
                // most examples you'll see around leave it out. MAKE SURE TO INCLUDE THE CHARSET!"
                $this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
                return true;
            // If an error is catched, database connection failed
            } catch (PDOException $e) {
                $this->errors[] = MESSAGE_DATABASE_ERROR;
                return false;
            }
        }
    }

    /**
     * handles the entire registration process. checks all error possibilities, and creates a new user in the database if
     * everything is fine
     */
    private function registerNewUser($student_name, $student_email, $student_password, $student_password_repeat, $captcha)
    {
        // we just remove extra space on username and email
        $student_name  = trim($student_name);
        $student_email = trim($student_email);

        // check provided data validity
        // TODO: check for "return true" case early, so put this first
        if (strtolower($captcha) != strtolower($_SESSION['captcha'])) {
            $this->errors[] = MESSAGE_CAPTCHA_WRONG;
        } elseif (empty($student_name)) {
            $this->errors[] = MESSAGE_USERNAME_EMPTY;
        } elseif (empty($student_password) || empty($student_password_repeat)) {
            $this->errors[] = MESSAGE_PASSWORD_EMPTY;
        } elseif ($student_password !== $student_password_repeat) {
            $this->errors[] = MESSAGE_PASSWORD_BAD_CONFIRM;
        } elseif (strlen($student_password) < 6) {
            $this->errors[] = MESSAGE_PASSWORD_TOO_SHORT;
        } elseif (strlen($student_name) > 64 || strlen($student_name) < 2) {
            $this->errors[] = MESSAGE_USERNAME_BAD_LENGTH;
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $student_name)) {
            $this->errors[] = MESSAGE_USERNAME_INVALID;
        } elseif (empty($student_email)) {
            $this->errors[] = MESSAGE_EMAIL_EMPTY;
        } elseif (strlen($student_email) > 64) {
            $this->errors[] = MESSAGE_EMAIL_TOO_LONG;
        } elseif (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = MESSAGE_EMAIL_INVALID;

        // finally if all the above checks are ok
        } else if ($this->databaseConnection()) {
            // check if username or email already exists
            $query_check_student_name = $this->db_connection->prepare('SELECT student_name, student_email FROM gaz_students WHERE student_name=:student_name OR student_email=:student_email');
            $query_check_student_name->bindValue(':student_name', $student_name, PDO::PARAM_STR);
            $query_check_student_name->bindValue(':student_email', $student_email, PDO::PARAM_STR);
            $query_check_student_name->execute();
            $result = $query_check_student_name->fetchAll();

            // if username or/and email find in the database
            // TODO: this is really awful!
            if (count($result) > 0) {
                for ($i = 0; $i < count($result); $i++) {
                    $this->errors[] = ($result[$i]['student_name'] == $student_name) ? MESSAGE_USERNAME_EXISTS : MESSAGE_EMAIL_ALREADY_EXISTS;
                }
            } else {
                // check if we have a constant HASH_COST_FACTOR defined (in config/hashing.php),
                // if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
                $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

                // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
                // the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
                // compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
                // want the parameter: as an array with, currently only used with 'cost' => XX.
                $student_password_hash = password_hash($student_password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
                // generate random hash for email verification (40 char string)
                $student_activation_hash = sha1(uniqid(mt_rand(), true));

                // write new gaz_students data into database
                $query_new_student_insert = $this->db_connection->prepare('INSERT INTO gaz_students (student_name, student_password_hash, student_email, student_activation_hash, student_registration_ip, student_registration_datetime) VALUES(:student_name, :student_password_hash, :student_email, :student_activation_hash, :student_registration_ip, now())');
                $query_new_student_insert->bindValue(':student_name', $student_name, PDO::PARAM_STR);
                $query_new_student_insert->bindValue(':student_password_hash', $student_password_hash, PDO::PARAM_STR);
                $query_new_student_insert->bindValue(':student_email', $student_email, PDO::PARAM_STR);
                $query_new_student_insert->bindValue(':student_activation_hash', $student_activation_hash, PDO::PARAM_STR);
                $query_new_student_insert->bindValue(':student_registration_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
                $query_new_student_insert->execute();

                // id of new user
                $student_id = $this->db_connection->lastInsertId();

                if ($query_new_student_insert) {
                    // send a verification email
                    if ($this->sendVerificationEmail($student_id, $student_email, $student_activation_hash)) {
                        // when mail has been send successfully
                        $this->messages[] = MESSAGE_VERIFICATION_MAIL_SENT;
                        $this->registration_successful = true;
                    } else {
                        // delete this gaz_students account immediately, as we could not send a verification email
                        $query_delete_user = $this->db_connection->prepare('DELETE FROM gaz_students WHERE student_id=:student_id');
                        $query_delete_user->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                        $query_delete_user->execute();

                        $this->errors[] = MESSAGE_VERIFICATION_MAIL_ERROR;
                    }
                } else {
                    $this->errors[] = MESSAGE_REGISTRATION_FAILED;
                }
            }
        }
    }

    /*
     * sends an email to the provided email address
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    public function sendVerificationEmail($student_id, $student_email, $student_activation_hash)
    {
        $mail = new PHPMailer;

        // please look into the config/config.php for much more info on how to use this!
        // use SMTP or use mail()
        if (EMAIL_USE_SMTP) {
            // Set mailer to use SMTP
            $mail->IsSMTP();
            //useful for debugging, shows full SMTP errors
            //$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
            // Enable SMTP authentication
            $mail->SMTPAuth = EMAIL_SMTP_AUTH;
            // Enable encryption, usually SSL/TLS
            if (defined(EMAIL_SMTP_ENCRYPTION)) {
                $mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
            }
            // Specify host server
            $mail->Host = EMAIL_SMTP_HOST;
            $mail->Username = EMAIL_SMTP_USERNAME;
            $mail->Password = EMAIL_SMTP_PASSWORD;
            $mail->Port = EMAIL_SMTP_PORT;
        } else {
            $mail->IsMail();
        }

        $mail->From = EMAIL_VERIFICATION_FROM;
        $mail->FromName = EMAIL_VERIFICATION_FROM_NAME;
        $mail->AddAddress($student_email);
        $mail->Subject = EMAIL_VERIFICATION_SUBJECT;

        $link = EMAIL_VERIFICATION_URL.'?id='.urlencode($student_id).'&verification_code='.urlencode($student_activation_hash);

        // the link to your register.php, please set this value in config/email_verification.php
        $mail->Body = EMAIL_VERIFICATION_CONTENT.' '.$link;

        if(!$mail->Send()) {
            $this->errors[] = MESSAGE_VERIFICATION_MAIL_NOT_SENT . $mail->ErrorInfo;
            return false;
        } else {
            return true;
        }
    }

    /**
     * checks the id/verification code combination and set the user's activation status to true (=1) in the database
     */
    public function verifyNewUser($student_id, $student_activation_hash)
    {
        // if database connection opened
        if ($this->databaseConnection()) {
            // try to update user with specified information
            $query_update_user = $this->db_connection->prepare('UPDATE gaz_students SET student_active = 1, student_activation_hash = NULL WHERE student_id = :student_id AND student_activation_hash = :student_activation_hash');
            $query_update_user->bindValue(':student_id', intval(trim($student_id)), PDO::PARAM_INT);
            $query_update_user->bindValue(':student_activation_hash', $student_activation_hash, PDO::PARAM_STR);
            $query_update_user->execute();

            if ($query_update_user->rowCount() > 0) {
                $this->verification_successful = true;
                $this->messages[] = MESSAGE_REGISTRATION_ACTIVATION_SUCCESSFUL;
            } else {
                $this->errors[] = MESSAGE_REGISTRATION_ACTIVATION_NOT_SUCCESSFUL;
            }
        }
    }
}
