<?php

if (isset($_GET['id'])) {
    $errors = [];

    $id = $_GET['id'];

    if (!$gallery->deletePhoto($id)) {
        $errors[] = 'Ошибка удаления фотографии ( ID = ' . $id . ' ): ' . implode($gallery->lastErrors);
    }

    // перечитаем коллекцию фотографий
    $gallery->getPhotos(true);
} else {
    $errors[] = 'Ошибка передачи ID файла для удаления';
}


if (count($errors) > 0) {
    //header("Location: logon?error=1");
    echo '<div class="input-box file-box">';
    echo '<div class="login-box-title file-box">При загрузке файлов произошли следующие ошибки:</div><br>';
    echo '<div class="login-box-note" style="text-align: left;">';
    foreach ($errors as $error) {
        echo $error . '<br>';
    }
    echo '</div>';
    echo '</div>';
} else {
    //header("Location: /");
    //exit();
    echo '<div class="input-box file-box">';
    echo '<div class="login-box-title file-box">Фотография удалена</div><br>';
    echo '<div class="login-box-note"><a href="/">Перейти в галерею</a></div>';
    echo '</div>';
}
