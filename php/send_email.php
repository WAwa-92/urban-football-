<?php

function sendConfirmationEmail($email, $firstName, $lastName, $sport, $terrain, $date, $time, $price, $players, $reservationId) {
    $to = $email;
    $subject = 'Confirmation de votre réservation — Urban Center';

    $dateFormatted = formatDateFR($date);
    $endTime = addOneHour($time);

    $body = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 1.8rem; }
        .content { padding: 40px 30px; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 1.1rem; font-weight: 800; color: #1e3c72; margin-bottom: 12px; border-bottom: 2px solid #ff6b35; padding-bottom: 8px; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row .label { color: #666; font-weight: 600; }
        .detail-row .value { color: #1e3c72; font-weight: 700; }
        .total-row { display: flex; justify-content: space-between; padding: 15px; background: linear-gradient(135deg, rgba(30,60,114,.08), rgba(255,107,53,.08)); border-radius: 8px; margin: 20px 0; font-weight: 800; }
        .total-row .label { color: #1e3c72; }
        .total-row .value { color: #ff6b35; font-size: 1.2rem; }
        .button { display: inline-block; background: linear-gradient(135deg, #0f172a 0%, #1e3c72 58%, #ff7a18 100%); color: #fff; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-weight: 800; margin-top: 20px; text-align: center; }
        .footer { background: #f9f9f9; padding: 20px 30px; border-top: 1px solid #eee; font-size: 0.85rem; color: #666; text-align: center; }
        .footer p { margin: 5px 0; }
        .footer a { color: #ff6b35; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Réservation Confirmée</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>$firstName $lastName</strong>,</p>
            <p>Votre réservation a bien été enregistrée dans nos systèmes. Vous trouverez ci-dessous le récapitulatif complet.</p>

            <div class="section">
                <div class="section-title">📋 Détails de votre réservation</div>
                <div class="detail-row">
                    <span class="label">N° de réservation:</span>
                    <span class="value">#{$reservationId}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Activité:</span>
                    <span class="value">{$sport}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Terrain:</span>
                    <span class="value">{$terrain}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date:</span>
                    <span class="value">{$dateFormatted}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Horaire:</span>
                    <span class="value">{$time} — {$endTime} (1h)</span>
                </div>
                <div class="detail-row">
                    <span class="label">Nombre de joueurs:</span>
                    <span class="value">{$players}</span>
                </div>
            </div>

            <div class="total-row">
                <span class="label">Montant à payer:</span>
                <span class="value">${price} DT</span>
            </div>

            <div class="section">
                <div class="section-title">📞 Prochaines étapes</div>
                <p>Notre équipe vous contactera très rapidement au numéro fourni pour confirmer votre réservation et discuter des modalités de paiement.</p>
                <p><strong>En attendant:</strong></p>
                <ul>
                    <li>Conservez ce numéro de réservation <strong>#$reservationId</strong></li>
                    <li>Arrivez 10-15 minutes avant l'horaire convenu</li>
                    <li>Contactez-nous au +216 XX XXX XXX en cas de changement</li>
                </ul>
            </div>

            <div class="section">
                <div class="section-title">ℹ️ Conditions d'annulation</div>
                <p>Les annulations doivent être effectuées 24 heures avant la réservation pour obtenir un remboursement complet.</p>
            </div>

            <p style="text-align: center;">
                <a href="../pages/my-reservations.html" class="button">Voir votre historique</a>
            </p>
        </div>
        <div class="footer">
            <p><strong>Urban Center Hassi Djerbi</strong></p>
            <p>Complexe sportif | Football · Padel · Tennis · Fitness</p>
            <p><a href="../Urban Center.html">www.urbancenter.tn</a></p>
            <p>© 2026 Tous droits réservés</p>
        </div>
    </div>
</body>
</html>
HTML;

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
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
