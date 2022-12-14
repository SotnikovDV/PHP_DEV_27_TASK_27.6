<?php

require_once 'mysql.class.php';

class User
{

    public $lastErrors;

    public $user_id;
    public $user_login;
    public $user_password;
    public $user_hash;

    public $loged;

    function __construct()
    {
        $this->loged = false;
    }

    // проверяем логин
    public function checkLogin($userName)
    {
        $this->lastErrors = [];

        // Вариант для эл.почты
        if (!filter_var($userName, FILTER_VALIDATE_EMAIL)) {
            $this->lastErrors[] = "Логин не похож на адрес эл.почты";
        }

        /* Вариант для логина-не эл.почты
        if (!preg_match("/^[a-zA-Z0-9@.-]+$/", $userName)) {
            $this->lastErrors[] = "Логин может состоять только из букв английского алфавита, цифр и символов [@-.]";
        }
        if (strlen($userName) < 3 or strlen($userName) > 30) {
            $this->lastErrors[] = "Логин должен быть не меньше 3-х символов и не больше 30";
        }
        */

        if (count($this->lastErrors) == 0) {
            return true;
        } else {
            return false;
        }
    }

    // проверяем пароль
    public function checkPassword($userPassw)
    {
        $this->lastErrors = [];

        if (!preg_match("/^[a-zA-Z0-9]+$/", $userPassw)) {
            $this->lastErrors[] = "Пароль может состоять только из букв английского алфавита и цифр";
        }
        if (strlen($userPassw) < 3 or strlen($userPassw) > 30) {
            $this->lastErrors[] = "Пароль должен быть не меньше 3-х символов и не больше 30";
        }

        if (count($this->lastErrors) == 0) {
            return true;
        } else {
            return false;
        }
    }

    // генерация случайной строки для хэша
    private function generateCode($length = 6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }
        return $code;
    }

    // добавление пользователя в БД
    // возвращает запись из таблицы users или null
    // пароль передается нехешированный
    public function addUser($userName, $userPassw)
    {

        $this->lastErrors = [];

        // проверка имени пользователя
        if (!$this->checkLogin($userName)) {
            return null;
        }

        // проверка существования пользователя с таким именем
        if ($this->userExists($userName)) {
            $this->lastErrors[] = 'Пользователь ' . $userName . ' уже зарегистрирован на сайте :)';
            return null;
        }

        // проверка пароля
        if (!$this->checkPassword($userPassw)) {
            return null;
        }

        $ushash = $this->generateCode();
        $pass = password_hash($userPassw, PASSWORD_DEFAULT);


        // подключаемся к БД
        $db = new MySQL(DB_SERVER, DB_NAME, DB_USER, DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        $sql = 'INSERT INTO users (login, PASSWORD, HASH ) VALUES ("'
            . mysqli_real_escape_string($db->db_connect, $userName) . '", "'
            . mysqli_real_escape_string($db->db_connect, $pass) . '", "'
            . mysqli_real_escape_string($db->db_connect, $ushash) . '");';

        $result = $db->update($sql);

        // Закрываем БД
        $db->closeConnection();

        if (!$result) {
            $this->lastErrors[] = 'Ошибка добавления пользователя: ' . $db->getError()['message'];
            return null;
        } else {
            // Ищем пользователя
            $usr = $this->userExists($userName);
            return $usr;
        }
    }

    // регистрация нового пользователя по OAuth 2.0
    public function addUserOAuth($login, $userID, $token, $oauthServ)
    {
        // регистрируем как обычного пользователя
        // в качестве логина - email
        // в качестве пароля - userID
        if (!$usr = $this->addUser($login, $userID)) {
            return null;
        }

        // сохраним доп.поля из OAuth
        // подключаемся к БД
        $db = new MySQL(DB_SERVER, DB_NAME, DB_USER, DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        switch ($oauthServ) {
            case 'vk':
                $sql = 'UPDATE users SET vkuser =  "' . mysqli_real_escape_string($db->db_connect, $token) . '" WHERE id = ' . $usr['id'];
                break;
        }

        $result = $db->update($sql);

        if ($db->getError()) {
            $this->lastErrors[] = 'Ошибка сохранения данных OAuth авторизации пользователя: ' . $db->getError()['message'];
        }

        // Закрываем БД
        $db->closeConnection();

        // перечитываем пользователя из БД что бы подтянуть новые поля
        if (!$result or !$usr2 = $this->userExists($login)) {
            return $usr;
        } else {
            return $usr2;
        }
    }

    // проверка на существование такого пользователя в БД
    // возвращает запись из таблицы users или null;
    public function userExists($userName)
    {
        $this->lastErrors = [];

        // подключаемся к БД
        $db = new MySQL(DB_SERVER, DB_NAME, DB_USER, DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        // запрашиваем пользователя в БД
        $usr = $db->select_row('SELECT * FROM users WHERE login = "' . mysqli_real_escape_string($db->db_connect, $userName) . '" LIMIT 1');
        //print_r($usr);
        //print_r($db->getError());
        // Закрываем БД
        $db->closeConnection();

        //if (!$usr) {  -- ЭТО НЕ ОШИБКА! ЭТО НОРМАЛЬНО
        if ($db->getError()) {
            $this->lastErrors[] = 'Ошибка проверки существования пользователя: ' . $db->getError()['message'];
            return null;
        } else {
            return $usr;
        }
    }

    // считывание пользователя из БД по ID
    // возвращает запись из таблицы users или null;
    public function getUserByID($id)
    {
        $this->lastErrors = [];

        // подключаемся к БД
        $db = new MySQL(DB_SERVER, DB_NAME, DB_USER, DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        // запрашиваем пользователя в БД
        $usr = $db->select_row('SELECT * FROM users WHERE id = ' . mysqli_real_escape_string($db->db_connect, $id));

        // Закрываем БД
        $db->closeConnection();

        if (!$usr) {
            $this->lastErrors[] = 'Ошибка считывания пользователя по ID: ';
            return null;
        } else {
            return $usr;
        }
    }

    // вход пользователя по имени и паролю
    // возвращает запись из таблицы users или null
    // пароль передается нехешированный
    public function logon($userName, $userPassw, $remember)
    {
        $_SESSION['loged'] = null;

        $pass = password_hash($userPassw, PASSWORD_DEFAULT);

        $this->lastErrors = [];

        // Ищем пользователя
        $usr = $this->userExists($userName);

        if (!$usr) {
            $this->lastErrors[] = 'Нет такого пользователя в базе данных:';
            // надо бы на ошибки проверить
            return null;
        }

        // проверяем совпадение пароля с хэшем
        if (password_verify($userPassw, $usr['password'])) {
            //var_dump($usr);
            $this->user_id = $usr['id'];
            $this->user_login = $usr['login'];
            $this->user_password = $usr['password'];
            $this->user_hash = $usr['hash'];            // теоретически надо каждый раз перезаписывать в БД и в cookie

            $this->loged = true;
            $_SESSION['loged'] = $usr['login'];

            // Ставим куки
            if ($remember) {
                setcookie("id", $usr['id'], time() + 60 * 60 * 24 * 30, "/");
                setcookie("hash", $usr['hash'], time() + 60 * 60 * 24 * 30, "/", null, null, true); // httponly !!! 
            } else {
                // Удаляем куки, если пользователь указал не помнить о нем
                setcookie("id", "", time() - 3600 * 24 * 30 * 12, "/");
                setcookie("hash", "", time() - 3600 * 24 * 30 * 12, "/", null, null, true); // httponly !!!    
            }


            return $usr;
        } else {
            $this->lastErrors[] = 'Указан неверный пароль пользователя ' . $usr['login'];
            return null;
        }
    }

    // попытка автоматического входа пользователя по ID в COOKIE
    // возвращает запись из таблицы users или null
    public function logonByCookie()
    {

        $this->lastErrors = [];
        $_SESSION['loged'] = null;


        if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) {

            $id = $_COOKIE['id'];
            $hash = $_COOKIE['hash'];

            // запрашиваем пользователя в БД по ID
            $usr = $this->getUserByID($id);

            if (!$usr) {
                $this->lastErrors[] = 'Ошибка считывания пользователя по ID';
                return null;
            } else {
                if (($usr['id'] === $id) and ($usr['hash'] === $hash)) {
                    $this->user_id = $usr['id'];
                    $this->user_login = $usr['login'];
                    $this->user_password = $usr['password'];
                    $this->user_hash = $usr['hash'];

                    $this->loged = true;
                    $_SESSION['loged'] = $usr['login'];
                } else {
                    $this->lastErrors[] = 'Hash или ID пользователя в cookie не совпадают с базой данных';
                    // Удаляем куки
                    setcookie("id", "", time() - 3600 * 24 * 30 * 12, "/");
                    setcookie("hash", "", time() - 3600 * 24 * 30 * 12, "/", null, null, true); // httponly !!!
                    return null;
                }

                return $usr;
            }
        } else {
            $this->lastErrors[] = 'Включите cookie';
        }
    }

    // попытка автоматического входа пользователя по $_SESSION
    // возвращает запись из таблицы users или null
    public function logonBySession()
    {

        $this->lastErrors = [];
        if (isset($_SESSION['loged'])) {
            $login = $_SESSION['loged'];
        } else {
            return null;
        }
        if (!$login) {
            return null;
        }

        // запрашиваем пользователя в БД по имени
        $usr = $this->userExists($login);

        if (!$usr) {
            $this->lastErrors[] = 'Ошибка считывания пользователя по ID';
            return null;
        } else {
            $this->user_id = $usr['id'];
            $this->user_login = $usr['login'];
            $this->user_password = $usr['password'];
            $this->user_hash = $usr['hash'];

            $this->loged = true;

            return $usr;
        }
    }

    // процедура выхода
    public function logoff()
    {
        $this->user_id = null;
        $this->user_login = null;
        $this->user_password = null;
        $this->user_hash = null;

        $this->loged = false;

        $_SESSION['loged'] = null;

        // Удаляем куки
        setcookie("id", "", time() - 3600 * 24 * 30 * 12, "/");
        setcookie("hash", "", time() - 3600 * 24 * 30 * 12, "/", null, null, true); // httponly !!!
    }
}

// получение отображаемой части логина пользователя
function screen_name($email)
{
    $i = strpos($email, '@');
    return ($i > 0) ? substr($email, 0, $i) : $email;
}
