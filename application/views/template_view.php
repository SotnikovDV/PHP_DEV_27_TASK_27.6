<!DOCTYPE html>
<html lang="кг">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Пример авторизации через QAuth</title>
</head>

<body>
    <?php
    session_start();
    $user = new User();

    // Считываем пользователя сессии
    $usr = $user->logonBySession();

    // если в сессии нет - ищем в coockaх
    if (!$usr) {
        $usr = $user->logonByCookie();
    }



    if (!$user->loged) {
        $login = null;
    } else {
        $login = $usr['login'];
    }
    ?>
    <aside>
        <p style="margin: 0 auto; text-align: center;"><a href="/logon"><img src="/images/user7.png" width="70%" alt="Вход"></a>
            <?php
            if (!$login) {
                echo '<a href="/logon">Войти</a>';
            } else {
                $scr_name = screen_name($login);
                echo '<a href="/logoff">' . $scr_name . '</a>';
            }
            ?>
        </p>
        <hr>
        <a href="/profile">Профиль пользователя</a>
        <a href="/">Журнал ошибок</a>
    </aside>
    <main>
        <?php include $content_view; ?>
    </main>
</body>

</html>