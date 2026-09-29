<?php
require_once __DIR__ . '/../core/admin_header.php';

$message = '';
$isError = false;
$currentUser = $_SESSION['gebruikersnaam'];

if (isset($_POST['submit'])) {
    $newUsername = trim($_POST['gebruikersnaam'] ?? '');
    $currentPassword = $_POST['huidig_wachtwoord'] ?? '';
    $newPassword = $_POST['nieuw_wachtwoord'] ?? '';
    $confirmPassword = $_POST['bevestig_wachtwoord'] ?? '';

    if ($newUsername === '' || $currentPassword === '') {
        $message = 'Gebruikersnaam en huidig wachtwoord zijn verplicht.';
        $isError = true;
    } else {
        $stmt = $con->prepare('SELECT id, wachtwoord FROM logininfo WHERE gebruikersnaam = ?');
        $stmt->bind_param('s', $currentUser);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($currentPassword, $user['wachtwoord'])) {
            $message = 'Huidig wachtwoord is onjuist.';
            $isError = true;
        } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
            $message = 'Nieuw wachtwoord moet minimaal 6 tekens zijn.';
            $isError = true;
        } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
            $message = 'Nieuwe wachtwoorden komen niet overeen.';
            $isError = true;
        } else {
            if ($newUsername !== $currentUser) {
                $check = $con->prepare('SELECT id FROM logininfo WHERE gebruikersnaam = ? AND id != ?');
                $check->bind_param('si', $newUsername, $user['id']);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $message = 'Deze gebruikersnaam is al in gebruik.';
                    $isError = true;
                    $check->close();
                } else {
                    $check->close();
                }
            }

            if (!$isError) {
                if ($newPassword !== '') {
                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $update = $con->prepare('UPDATE logininfo SET gebruikersnaam = ?, wachtwoord = ? WHERE id = ?');
                    $update->bind_param('ssi', $newUsername, $hash, $user['id']);
                } else {
                    $update = $con->prepare('UPDATE logininfo SET gebruikersnaam = ? WHERE id = ?');
                    $update->bind_param('si', $newUsername, $user['id']);
                }

                if ($update->execute()) {
                    $_SESSION['gebruikersnaam'] = $newUsername;
                    $currentUser = $newUsername;
                    $message = 'Accountgegevens bijgewerkt.';
                } else {
                    $message = 'Opslaan mislukt. Probeer het opnieuw.';
                    $isError = true;
                }

                $update->close();
            }
        }
    }
}
?>

<div class="admin-panel">
    <h1>Account aanpassen</h1>
    <p>Wijzig je gebruikersnaam en/of wachtwoord.</p>

    <?php if ($message !== ''): ?>
        <div class="pop-up <?= $isError ? 'pop-up--error' : 'pop-up--success' ?>">
            <p><?= testInput($message) ?></p>
        </div>
    <?php endif; ?>

    <form method="post" class="admin-form">
        <div class="inputField">
            <label for="gebruikersnaam">Nieuwe gebruikersnaam</label>
            <input type="text" name="gebruikersnaam" id="gebruikersnaam" required value="<?= testInput($currentUser) ?>">
        </div>

        <div class="inputField">
            <label for="huidig_wachtwoord">Huidig wachtwoord</label>
            <input type="password" name="huidig_wachtwoord" id="huidig_wachtwoord" required>
        </div>

        <div class="inputField">
            <label for="nieuw_wachtwoord">Nieuw wachtwoord (optioneel)</label>
            <input type="password" name="nieuw_wachtwoord" id="nieuw_wachtwoord" minlength="6">
        </div>

        <div class="inputField">
            <label for="bevestig_wachtwoord">Bevestig nieuw wachtwoord</label>
            <input type="password" name="bevestig_wachtwoord" id="bevestig_wachtwoord" minlength="6">
        </div>

        <button type="submit" name="submit" value="1">Opslaan</button>
    </form>
</div>

<?php require_once __DIR__ . '/../core/admin_footer.php'; ?>
