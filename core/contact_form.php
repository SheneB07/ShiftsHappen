<?php

function handleContactForm(mysqli $con): array
{
    $naam = trim($_POST['naam'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bericht = trim($_POST['bericht'] ?? '');

    if ($naam === '' || $email === '' || $bericht === '') {
        return ['message' => 'Vul alle velden in.', 'error' => true];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['message' => 'Vul een geldig e-mailadres in.', 'error' => true];
    }

    $stmt = $con->prepare('INSERT INTO contactberichten (naam, email, bericht) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $naam, $email, $bericht);

    if ($stmt->execute()) {
        $stmt->close();
        return ['message' => 'Bedankt! Je bericht is verstuurd.', 'error' => false];
    }

    $stmt->close();
    return ['message' => 'Versturen mislukt. Probeer het later opnieuw.', 'error' => true];
}

function renderContactForm(array $feedback = []): void
{
    $message = $feedback['message'] ?? '';
    $isError = $feedback['error'] ?? false;
    ?>
    <section class="contact-section">
        <h2>Contact formulier</h2>

        <?php if ($message !== ''): ?>
            <div class="pop-up <?= $isError ? 'pop-up--error' : 'pop-up--success' ?>">
                <p><?= testInput($message) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" class="contact-form">
            <div class="contact-row">
                <div class="inputField">
                    <input type="text" name="naam" id="contact-naam" required placeholder="Naam (verplicht)"
                        value="<?= testInput($_POST['naam'] ?? '') ?>">
                </div>

                <div class="inputField">
                    <input type="text" name="organisatie" id="contact-organisatie" placeholder="Organisatie"
                        value="<?= testInput($_POST['organisatie'] ?? '') ?>">
                </div>

                <div class="inputField">
                    <input type="email" name="email" id="contact-email" required placeholder="E-mailadres (verplicht)"
                        value="<?= testInput($_POST['email'] ?? '') ?>">
                </div>

                <div class="inputField">
                    <input type="tel" name="telefoonnummer" id="contact-telefoonnummer" placeholder="Telefoonnummer"
                        value="<?= testInput($_POST['telefoonnummer'] ?? '') ?>">
                </div>
            </div>
            
            <div class="contact-column">
                <div class="inputField">
                    <textarea name="bericht" id="contact-bericht" rows="5" placeholder="Bericht/vraag (verplicht)" required><?= testInput($_POST['bericht'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="checkbox-column">
                <input type="checkbox" id="checkbox-email" name="checkbox" value="checkbox-email">
                <label for="checkbox">Ik ontvang graag updates of inspiratie via e-mail</label><br>
            </div>
            
            <div class="verstuur-row">
                <input type="hidden" name="contact_submit" value="1">
                <button id="verstuur-button" type="submit">Verstuur bericht</button>
            </div>
        </form>
    </section>
    <?php
}
