<?php
require_once __DIR__ . '/../core/db_connect.php';

$message = '';
$isError = false;

if (isset($_POST['submit'])) {
    $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if ($gebruikersnaam === '' || $wachtwoord === '') {
        $message = 'Vul alle velden in.';
        $isError = true;
    } elseif (strlen($wachtwoord) < 6) {
        $message = 'Je wachtwoord moet minimaal 6 tekens zijn.';
        $isError = true;
    } else {
        $check = $con->prepare('SELECT id FROM logininfo WHERE gebruikersnaam = ?');
        $check->bind_param('s', $gebruikersnaam);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = 'Deze gebruikersnaam is al in gebruik!';
            $isError = true;
        } else {
            $hash = password_hash($wachtwoord, PASSWORD_BCRYPT);
            $insert = $con->prepare('INSERT INTO logininfo (gebruikersnaam, wachtwoord) VALUES (?, ?)');
            $insert->bind_param('ss', $gebruikersnaam, $hash);

            if ($insert->execute()) {
                $message = 'De registratie is compleet! Je kunt nu inloggen.';
            } else {
                $message = 'Registratie mislukt. Probeer het opnieuw.';
                $isError = true;
            }

            $insert->close();
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <title>Registratie pagina</title>
</head>

<body>
    <div class="container">
        <div class="loginForm">
            <?php if ($message !== ''): ?>
                <div class="pop-up <?= $isError ? 'pop-up--error' : 'pop-up--success' ?>">
                    <p><?= testInput($message) ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <header>Registreer</header>

                <div class="inputField">
                    <label for="gebruikersnaam">Gebruikersnaam</label>
                    <input type="text" name="gebruikersnaam" id="gebruikersnaam" required
                        value="<?= testInput($_POST['gebruikersnaam'] ?? '') ?>">
                </div>

                <div class="inputField">
                    <label for="wachtwoord">Wachtwoord</label>
                    <input type="password" name="wachtwoord" id="wachtwoord" required minlength="6">
                </div>

                <div class="inputField">
                    <input type="submit" name="submit" value="Registreer nu!">
                </div>

                <div class="link">
                    Heb je al een account? <a href="<?= view('login.php') ?>">Log hier in.</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
