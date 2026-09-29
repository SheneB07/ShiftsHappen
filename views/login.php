<?php
require_once __DIR__ . '/../core/db_connect.php';

$error = '';

if (isset($_POST['submit'])) {
    $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if ($gebruikersnaam === '' || $wachtwoord === '') {
        $error = 'Vul zowel gebruikersnaam als wachtwoord in.';
    } else {
        $stmt = $con->prepare('SELECT gebruikersnaam, wachtwoord FROM logininfo WHERE gebruikersnaam = ?');
        $stmt->bind_param('s', $gebruikersnaam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($wachtwoord, $row['wachtwoord'])) {
                $_SESSION['gebruikersnaam'] = $gebruikersnaam;
                header('Location: ' . view('admin.php'));
                exit();
            }

            $error = 'Fout wachtwoord of gebruikersnaam. Probeer het nog eens.';
        } else {
            $error = 'Geen account gevonden.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <title>Login pagina</title>
</head>

<body>
    <div class="container">
        <div class="loginForm">
            <?php if ($error !== ''): ?>
                <div class="pop-up pop-up--error">
                    <p><?= testInput($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <header>Login</header>

                <div class="inputField">
                    <label for="gebruikersnaam">Gebruikersnaam</label>
                    <input type="text" name="gebruikersnaam" id="gebruikersnaam" required
                        value="<?= testInput($_POST['gebruikersnaam'] ?? '') ?>">
                </div>

                <div class="inputField">
                    <label for="wachtwoord">Wachtwoord</label>
                    <input type="password" name="wachtwoord" id="wachtwoord" required>
                </div>

                <div class="inputField">
                    <input type="submit" name="submit" value="Inloggen">
                </div>

                <div class="link">
                    Heb je nog geen account? <a href="<?= view('registreren.php') ?>">Registreer jezelf hier!</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
