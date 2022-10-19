<?php

require_once 'mysql.class.php';
require_once 'config.class.php';
require_once 'user.class.php';

class Gallery
{

    public $lastErrors;

    private $photos;  // коллекция фотографий
    private $comments;  // комментарии

    // ------------------- Фотографии -----------------------

    // загрузка таблицы фотографий из БД
    public function getPhotos($reload = false)
    {

        $this->lastErrors = [];

        if (!$this->photos or $reload) {

            // подключаемся к БД
            $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
            if (!$db) {
                $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
                return null;
            }

            // запрашиваем список фотографий
            $this->photos = $db->select('SELECT p.*, u.login username FROM users u, photos p where p.owner = u.id');

            //var_dump($this->photos);

            if ($db->getError()) {
                $this->lastErrors[] = $db->getError()['message'];
            }

            // Закрываем БД
            $db->closeConnection();

            return $this->photos;
        } else {
            return $this->photos;
        }
    }

    //Добавление новой фотографии
    // $fileName - путь к файлу в папке загрузки
    // $photoTitle - описание фотографии
    // При успехе возвращает ID файла в БД
    // в случае неудачи - false
    public function addPhoto($fileName, $photoTitle, $user_id)
    {

        $this->lastErrors = [];

        $fileParts = pathinfo($fileName);
        $fileExt = $fileParts['extension'];

        // подключаемся к БД
        $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return false;
        }

        // запись в БД
        $sql = 'INSERT INTO photos (filename, title, owner, type) values ("' . mysqli_real_escape_string($db->db_connect, basename($fileName)) . '", "' . mysqli_real_escape_string($db->db_connect, $photoTitle) . '", ' . $user_id . ', "' . $fileExt . '");';
        $result = $db->update($sql);

        // запрос последнего ID
        if ($result) {
            $sql = 'SELECT MAX(id) max_id FROM photos;';
            $query = $db->select_row($sql);

            if (!$query) {
                $this->lastErrors[] = $db->getError()['message'];
                return false;
            } else {
                $id = $query['max_id'];
            }
        } else {
            if ($db->getError()) {
                $this->lastErrors[] = $db->getError()['message'];
                return false;
            }
        }

        // Закрываем БД
        $db->closeConnection();

        if (!$result) {
            return false;
        } else {

            $fileNewName = Config::PHOTO_DIR . '/' . $id . '.' . $fileExt;

            // Переносим файл в папку для фотографий с переименованием в ID
            if (!copy($fileName, $fileNewName)) {
                $this->lastErrors[] = $db->getError()['message'];
                return false;
            }

            return $id;
        }
    }

    // поиск фото в масиве по ID
    public function getPhotoByID($id)
    {
        $this->lastErrors = [];

        // подключаемся к БД
        $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        // запрашиваем фотографию
        $photo = $db->select_row('SELECT p.*, u.login username FROM users u, photos p where p.owner = u.id and p.id = ' . $id);

        // Закрываем БД
        $db->closeConnection();

        if (!$photo) {
            $this->lastErrors[] = $db->getError()['message'];
            return null;
        } else {
            return $photo;
        }
    }

    // Удаление фотографии
    public function deletePhoto($id)
    {
        $this->lastErrors = [];

        $photo = $this->getPhotoByID($id);

        // Удаляем в БД
        // подключаемся к БД
        $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return false;
        }

        // запись в БД
        $sql = 'DELETE FROM photos WHERE id = ' . $id . ';';
        $result = $db->update($sql);

        if (!$result) {
            $this->lastErrors[] = 'Проблема удаления в БД. ' . $db->getError()['message'];
            return false;
        }


        // Удаляем файл
        $fileName = Config::PHOTO_DIR . '/' . $id . '.' . $photo['type'];

        if (file_exists($fileName)) {
            $result = unlink($fileName);
            if (!$result) {
                $this->lastErrors[] = 'Ошибка удаления файла с ресурса [ ' . $fileName . ' ]';
            }
        }

        return true;
    }

    // ------------------- Комментарии -----------------------

    // загрузка таблицы комментариев из БД
    public function getComments($reload = false)
    {
        $this->lastErrors = [];

        if (!$this->comments or $reload) {

            // подключаемся к БД
            $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
            if (!$db) {
                $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
                return null;
            }

            // запрашиваем список фотографий
            $this->comments = $db->select('SELECT c.*, u.login username FROM users u, comments c where c.user = u.id');


            if ($db->getError()) {
                $this->lastErrors[] = $db->getError()['message'];
            }

            // Закрываем БД
            $db->closeConnection();

            return $this->comments;
        } else {
            return $this->comments;
        }
    }
    // получение комментария из БД по id
    public function getCommentByID($id)
    {
        $this->lastErrors = [];

        // подключаемся к БД
        $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        // запрашиваем список фотографий
        $comment = $db->select_row('SELECT c.*, u.login username FROM users u, comments c where c.user = u.id and c.id=' . $id);

        // Закрываем БД
        $db->closeConnection();

        if (!$comment) {
            $this->lastErrors[] = $db->getError()['message'];
            return null;
        }
        return $comment;
    }

    // Отбор комментариев для фотографии
    public function getCommentsByPhoto($pid)
    {

        $this->lastErrors = [];

        $coms = $this->getComments();
        $photoComments = [];

        foreach ($coms as $com) {
            if ($com['image'] == $pid) {
                $photoComments[] = $com;
            }
        }
        return $photoComments;
    }
    // Добавление комментария для фотографии
    public function addComment($pid, $comment, $user_id)
    {

        $this->lastErrors = [];

        if (empty($comment)) {
            $this->lastErrors[] = 'Невозможно добавить пустой комментарий';
            return false;
        }

        // подключаемся к БД
        $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        $sql = 'INSERT INTO comments (image, loaddate, comment, user) VALUES (' . $pid . ', Now(), "' . $comment . '", ' . $user_id . ');';

        $result = $db->update($sql);

        // запрос последнего ID
        if ($result) {
            $sql = 'SELECT MAX(id) max_id FROM comments;';
            $query = $db->select_row($sql);

            if (!$query) {
                $this->lastErrors[] = $db->getError()['message'];
                return false;
            } else {
                $id = $query['max_id'];
            }
        } else {
            if ($db->getError()) {
                $this->lastErrors[] = $db->getError()['message'];
                return false;
            }
        }

        // Закрываем БД
        $db->closeConnection();

        if (!$result) {
            return false;
        } else {

            return $id;
        }
    }


    // Изменение комментария для фотографии
    public function editComment($id, $comment)
    {

        $this->lastErrors = [];

        if (empty($comment)) {
            $this->lastErrors[] = 'Невозможно сохранить пустой комментарий';
            return false;
        }

        // подключаемся к БД
        $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        $sql = 'UPDATE comments SET comment = "'.$comment. '" where id = '.$id.';';

        $result = $db->update($sql);

        // Закрываем БД
        $db->closeConnection();

        if (!$result) {
            return false;
        } else {

            return $id;
        }
    }

    // Удаление комментария для фотографии
    public function deleteComment($id)
    {

        $this->lastErrors = [];

        // подключаемся к БД
        $db = new MySQL(Config::DB_SERVER, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        if (!$db) {
            $this->lastErrors[] = 'Ошибка подключения к базе данных: ';
            return null;
        }

        $sql = 'DELETE FROM comments WHERE id=' . $id . ';';

        $result = $db->update($sql);

        // Закрываем БД
        $db->closeConnection();

        if (!$result) {
            return false;
        } else {

            return $id;
        }
    }
}
