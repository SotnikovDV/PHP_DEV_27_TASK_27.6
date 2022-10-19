<?php
$errors = [];
/* ------------------ запрос комментариев для photo через XMLHttpRequest ------------ */
if (key_exists('act', $_GET)) {
    require_once './application/core/class/gallery.class.php';

    $gallery = new Gallery;

    $pid = $_GET['pid'];
    $login = $_GET['login'];

    $comments = $gallery->getCommentsByPhoto($pid);

    foreach ($comments as $coment) {
        echo '<div class="card-comment photo'.$coment['image'].'" id=com' . $coment['id'] . '>';
        echo '<p><img src="/images/avatar.png" alt="Avatar" style="width:30px">';
        echo ' <span style="margin-left: 5px;"><b>' . $coment['username'] . '</b></span>';
        if ($login) { 
            echo '<a href="/comment?pid='.$coment['image'].'&action=delete&id='.$coment['id'].'" class="card-comment-btn"> X </a>';
        }
    
        echo '</p>';
        echo '<p>';
        if ($login) {
            echo '<a href="/comment?pid='.$coment['image'].'&action=edit&id='.$coment['id'].'">' . $coment['comment'] . '</a>';
        } else {
            echo $coment['comment'];
        }
        echo '</p>';
        echo '<hr>';
        echo '</div>';
    }
    exit;
} elseif (isset($_POST['submit'])) {
    /* ------------------------ Обработка формы ------------------ */
    $mode = 0;
    $action = $_POST['act'];
    $pid = $_POST['pid'];
    $id = $_POST['id'];
    $comment = $_POST['comment'];

    switch ($action) {
        case 'add':
            $result = $gallery->addComment($pid, $comment, $user->user_id);
            if (!$result) {
                $error[] = 'Ошибка добавления комментария в базу данных: ' . implode($gallery->lastErrors);
            }
            $title = 'Добавление ';
            $title2 = 'добавлен';
            $btn   = 'Добавить';
            $text = $comment;
            break;
        case 'edit':
            $result = $gallery->editComment($id, $comment);
            if (!$result) {
                $error[] = 'Ошибка сохранения комментария в базу данных: ' . implode($gallery->lastErrors);
            }
            $title = 'Исправление';
            $title2 = 'исправлен';
            $text = $comment;
            $btn   = 'Сохранить';
            break;
        case 'delete':
            $result = $gallery->deleteComment($id);
            if (!$result) {
                $error[] = 'Ошибка удаления комментария из базы данных: ' . implode($gallery->lastErrors);
            }
            $title = 'Удаление';
            $title2 = 'удален';
            $text = $comment;
            $btn   = 'Удалить';
            break;
    }

    if (count($errors) > 0) {
        //header("Location: logon?error=1");
        echo '<div class="input-box file-box">';
        echo '<div class="login-box-title file-box">При добавлении/изменении/удалении комментария произошли следующие ошибки:</div><br>';
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
        echo '<div class="login-box-title file-box">Комментарий успешно ' . $title2 . '</div><br>';
        echo '<div class="login-box-note"><a href="/">Перейти в галерею</a></div>';
        echo '</div>';
    }
} else {
    /* ------------------------ Обработка параметров GET ------------------ */
    if (key_exists('action', $_GET)) {
        $mode = 1;
        $action = $_GET['action'];
        $pid = $_GET['pid'];

        if (key_exists('id', $_GET)) {
            $id = $_GET['id'];
            $comment = $gallery->getCommentByID($id);
        } else {
            $id = null;
        }
        switch ($action) {
            case 'add':
                $title = 'Добавление ';
                $btn   = 'Добавить';
                $text = null;
                break;
            case 'delete':
                $title = 'Удаление';
                $text = $comment['comment'];
                $btn   = 'Удалить';
                break;
            case 'edit':
                $title = 'Исправление';
                $text = $comment['comment'];
                $btn   = 'Сохранить';
                break;
        }
    }
}
//if ($action !== 'delete') {
if (($mode === 1) or ($action !== 'delete')) {
?>
    <div class="input-box file-box">
        <div class="login-box-title"><?= $title ?> комментария</div>
        <?php
        if (key_exists('error', $_REQUEST)) {
        ?>
            <div class="login-box-error">Ошибка комментирования. Попробуйте еще раз</div>
            <?php
        } else {
            $photo = $gallery->getPhotoByID($pid);
            echo '<div class="login-box-note">';
            echo '<img class="demo cursor" src="' . Config::PHOTO_DIR . '/' . $photo['id'] . '.' . $photo['type'] . '" style="width:90%" alt="' . $photo['title'] . '">';
            echo '</div>';
            if ($action !== 'delete') {
            ?>

                <div class="login-box-note">
                    Максимальный размер комментария: <b><?= Config::MAX_COMMENT_SIZE ?> символов</b><br>
                </div>

            <?php
            }

            ?>
            <form action="comment" method="post" class="login-form">
                <input type="hidden" name="act" value="<?= $action ?>">
                <input type="hidden" name="pid" value="<?= $pid ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <label for="comment">Комментарий:</label>
                <input name="comment" type="text" placeholder="..." class="inpt" value="<?= $text ?>" required autofocus>
                <!-- <p><textarea name="comment" cols="40" rows="3" class="inpt"></textarea></p> -->
                <input name="submit" type="submit" value="<?= $btn ?>" class="btn">
                <input name="button" type="button" value="Отмена" onclick="location='/'" class="btn">
            </form>
    <?php
        }
    }
    ?>
    </div>