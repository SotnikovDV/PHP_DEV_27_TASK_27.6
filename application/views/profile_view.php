<?php

$errors = [];

// Берем токен и id из сессии
if (isset($_SESSION['oauth'])){

    $serv = $_SESSION['oauth'];
    $token = $_SESSION['token'];
    $userId = $_SESSION['user_id'];
    $fields = 'uid,first_name,last_name,screen_name,sex,bdate,photo_big';

    $oauth = OAuth::getInstance($serv);

    // Запрашиваем данные пользователя
    $userInfo = $oauth->getUserInfo($userId, $token, $fields);

    $first_name = $userInfo->first_name;
    $last_name = $userInfo->last_name;
    $screen_name = $userInfo->screen_name;
    $photo_big = $userInfo->photo_big;
    $bdate  = $userInfo->bdate;

    /* foreach ($userInfo as $key => $element) {
        echo $key . ' = ' . $element . '<br>';
    }*/

} else {
    $first_name = '';
    $last_name = '';
    $screen_name = '';
    $photo_big = null;
    $bdate  = null;
}
/*    
    header("Location: /logon?errors=Для просмотра профиля пользователя, авторизуйтесь через VK");
}
*/
?>
    <div class="tab-dialog">
        <!-- Профиль -->
        <div id="profile" class="tabcontent" style="display: block">
            <form class="logon-frm" action="/logon" method="post">

                <div class="container">
                    <table><tr>
            <td width="70%">
                    <h2 style="text-align: center;">Профиль<br>пользователя</h2>
                    </td>
                    <td width="30%">
                        <?php if (isset($_SESSION['oauth'])){
                            echo '<img src="'.$photo_big .'" alt="Фото пользователя">';
                        } else {
                            echo '<span>Это фото пользователя VK. Но вы не авторизованы через VK</span>';
                        } ?>   
                    </td>
                    </tr></table>
                    <hr>
                    <label for="first_name"><b>Имя</b></label>
                    <input type="text" placeholder="Иван" name="first_name" required class="logon-frm-input" value="<?=$first_name?>">

                    <label for="last_name"><b>Фамилия</b></label>
                    <input type="text" placeholder="Иванов" name="last_name" required class="logon-frm-input" value="<?=$last_name?>">

                    <label for="screen_name"><b>Ник-нейм</b></label>
                    <input type="text" placeholder="Ktulhu" name="screen_name" required class="logon-frm-input" value="<?=$screen_name?>">

                </div>

                <div class="container" style="background-color:#f1f1f1; text-align: center;">
                    <button type="submit" name="save" class="logon-frm-btn signupbtn" style="margin-top: 20px;">Сохранить</button>
                </div>

            </form>
        </div>
