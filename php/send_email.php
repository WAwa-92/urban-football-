<?php

function sendConfirmationEmail($email, $firstName, $lastName, $sport, $terrain, $date, $time, $price, $players, $reservationId) {
    $to = $email;
    $subject = 'Confirmation de votre réservation — Urban Center';

    $dateFormatted = formatDateFR($date);
    $endTime = addOneHour($time);

    $body = "Bonjour $firstName $lastName,\n\n"
        . "Votre réservation est confirmée.\n\n"
        . "Récapitulatif :\n"
        . "- Référence : #$reservationId\n"
        . "- Activité : $sport\n"
        . "- Terrain : $terrain\n"
        . "- Date : $dateFormatted\n"
        . "- Horaire : $time — $endTime (1h)\n"
        . "- Joueurs : $players\n"
        . "- Montant : $price DT\n\n"
        . "Infos pratiques :\n"
        . "- Merci d'arriver 10 à 15 minutes avant le début\n"
        . "- Conservez ce message avec votre référence\n"
        . "- En cas de changement, contactez-nous depuis le site\n\n"
        . "Annulation : prévenir au moins 24 heures avant la réservation.\n\n"
        . "Cordialement,\n"
        . "Urban Center Hessi Djerbi\n"
        . "Football · Padel · Fitness\n"
        . "https://urbancenter.tn/\n";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: noreply@urbancenter.tn\r\n";
    $headers .= "Reply-To: contact@urbancenter.tn\r\n";

    return mail($to, $subject, $body, $headers);
}

function formatDateFR($dateString) {
    $date = new DateTime($dateString);
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    $dayName = $days[$date->format('N') - 1];
    $day = (int)$date->format('d');
    $month = $months[(int)$date->format('m') - 1];
    $year = $date->format('Y');

    return ucfirst("$dayName $day $month $year");
}

function addOneHour($time) {
    $parts = explode(':', $time);
    $hour = (int)$parts[0];
    $minute = (int)$parts[1];

    $hour = ($hour + 1) % 24;

    return sprintf("%02d:%02d", $hour, $minute);
}

?>
