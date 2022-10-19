<?php
$maxFileSize = Config::MAX_FILE_SIZE;
//define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/tiff']);
$allowedTypes = Config::ALLOWED_TYPES;
$dirUpload = Config::UPLOAD_DIR;
$dirPhoto = Config::PHOTO_DIR;

if (isset($_POST['submit'])) {
    $errors = [];

    if (!empty($_FILES)) {

        // цикл по загружаемым файлам
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {

            $fileName = $_FILES['files']['name'][$i];
            
            // проверка на допустимый максимальный размер
            if ($_FILES['files']['size'][$i] > $maxFileSize) {
                $errors[] = 'Недопустимый размер файла ' . $fileName;
                continue;
            }

            // проверка на допустимый тип файла
            // доделать - $_FILES['files']['type'][$i] может быть пустым
            if (!in_array($_FILES['files']['type'][$i], $allowedTypes)) {
                $errors[] = 'Недопустимый формат файла ' . $fileName;
                continue;
            }

            $filePath = $dirUpload . '/' . basename($fileName);
            // Загружаем в папку для загрузок
            if (!move_uploaded_file($_FILES['files']['tmp_name'][$i], $filePath)) {
                $errors[] = 'Ошибка загрузки файла ' . $fileName;
                continue;
            }

            $title = $_POST['title'];

            if (!$title){
                $title = 'Загрузил '.Date('dd.mm.yy').' '.$user->user_login;
            }

            // Добавляем запись в БД
            // и перемещаем файл в папку с фотографиями
            $id = $gallery->addPhoto($filePath, $title, $user->user_id);

            // если добавили успешно, 
            if (!$id){
                $errors[] = 'Ошибка добавления файла ' . $fileName . ' в базу данных: ' . implode($gallery->lastErrors);
            }
        }
        // перечитаем коллекцию фотографий
        $gallery->getPhotos(true);
    }


    if (count($errors) > 0) {
        //header("Location: logon?error=1");
        echo '<div class="input-box file-box">';
        echo '<div class="login-box-title file-box">При загрузке файлов произошли следующие ошибки:</div><br>';
        echo '<div class="login-box-note" style="text-align: left;">';
        foreach ($errors as $error){
            echo $error.'<br>';
        }
        echo '</div>';
        echo '</div>';
    } else {
        //header("Location: /");
        //exit();
        echo '<div class="input-box file-box">';
        echo '<div class="login-box-title file-box">Файлы успешно загружены</div><br>';
        echo '<div class="login-box-note">Вы можете загрузить еще фотографии или <a href="/">перейти в галерею</a></div>';
        echo '</div>';

    }
}
?>
<div class="input-box file-box">
    <div class="login-box-title">Загрузить фото</div>
    <?php
    if (key_exists('error', $_REQUEST)) {
    ?>
        <div class="login-box-error">Ошибка загрузки. Попробуйте еще раз</div>
    <?php
    } else {
    ?>
        <div class="login-box-note">
            Максимальный размер файла: <b><?= $maxFileSize  / 1000000 ?> Mb</b><br>
            Допустимые форматы: <b><?= implode(', ', $allowedTypes) ?></b>
        </div>

    <?php
    }
    ?>
    <form action="download" method="post" class="login-form" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxFileSize ?>">
        <label for="files">Выберите фотографии для загрузки:</label>
        <input type="file" name="files[]" id="file-drop" multiple required accept=".jpg, .jpeg, .png, .tiff">
        <label for="title">Описание:</label>
        <input name="title" type="text" placeholder="..." class="inpt" autofocus>
        <input name="submit" type="submit" value="Загрузить" class="btn">
        <input name="button" type="button" value="Отмена" onclick="location='/'" class="btn">
    </form>
</div>