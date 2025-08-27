<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

// Required fields
$required_fields = ['client_email', 'client_name', 'appointment_number', 'appointment_date', 'appointment_time', 'service_name'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
        exit;
    }
}

// Email configuration
$to = $input['client_email'];
$subject = "Confirmation de Rendez-vous #{$input['appointment_number']} - CHU Hassan II";
$from_email = "vol.bader500@gmail.com"; // Replace with your domain email
$from_name = "CHU Hassan II";

// Format date
$date = new DateTime($input['appointment_date']);
$formatted_date = $date->format('l j F Y'); // e.g., "lundi 15 janvier 2025"

// Translate day names to French
$days_fr = [
    'Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi',
    'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche'
];
$months_fr = [
    'January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril',
    'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août',
    'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'
];

$formatted_date = strtr($formatted_date, $days_fr);
$formatted_date = strtr($formatted_date, $months_fr);

// Email HTML content
$html_content = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Rendez-vous</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #007bff; margin-bottom: 10px;">CHU Hassan II</h1>
        <h2 style="color: #6c757d; font-weight: normal;">Confirmation de Rendez-vous</h2>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="font-size: 16px; margin-bottom: 20px;">
            Bonjour <strong>' . htmlspecialchars($input['client_name']) . '</strong>,
        </p>
        
        <p>Votre rendez-vous au CHU Hassan II a été confirmé avec succès.</p>
    </div>
    
    <div style="background: #e7f3ff; padding: 25px; border-radius: 8px; border-left: 5px solid #007bff; margin: 25px 0;">
        <h3 style="color: #007bff; margin-top: 0; margin-bottom: 20px;">📋 Détails du rendez-vous</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; width: 30%;">🎫 Numéro :</td>
                <td style="padding: 8px 0; color: #007bff; font-weight: bold;">' . htmlspecialchars($input['appointment_number']) . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">🏥 Service :</td>
                <td style="padding: 8px 0;">' . htmlspecialchars($input['service_name']) . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">👨‍⚕️ Médecin :</td>
                <td style="padding: 8px 0;">' . htmlspecialchars($input['doctor_name'] ?? 'À déterminer') . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">📅 Date :</td>
                <td style="padding: 8px 0; color: #28a745; font-weight: bold;">' . $formatted_date . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">⏰ Heure :</td>
                <td style="padding: 8px 0; color: #28a745; font-weight: bold;">' . htmlspecialchars($input['appointment_time']) . '</td>
            </tr>';

if (!empty($input['notes'])) {
    $html_content .= '
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">📝 Motif :</td>
                <td style="padding: 8px 0;">' . htmlspecialchars($input['notes']) . '</td>
            </tr>';
}

$html_content .= '
        </table>
    </div>
    
    <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 5px solid #ffc107; margin: 25px 0;">
        <h4 style="color: #856404; margin-top: 0;">⚠️ Instructions importantes</h4>
        <ul style="color: #856404; margin: 0;">
            <li>Présentez-vous <strong>15 minutes avant</strong> l\'heure du rendez-vous</li>
            <li>Apportez votre <strong>carte d\'identité</strong> et votre <strong>carte de sécurité sociale</strong></li>
            <li>Apportez vos <strong>examens médicaux antérieurs</strong> si vous en avez</li>
            <li>En cas d\'empêchement, contactez le service au <strong>05 35 XX XX XX</strong></li>
        </ul>
    </div>
    
    <div style="background: #d1ecf1; padding: 15px; border-radius: 8px; text-align: center; margin: 25px 0;">
        <p style="margin: 0; color: #0c5460;">
            <strong>📍 Adresse :</strong> CHU Hassan II, Avenue Mohammed VI, Fès<br>
            <strong>📞 Contact :</strong> 05 35 XX XX XX
        </p>
    </div>
    
    <hr style="border: none; border-top: 1px solid #dee2e6; margin: 30px 0;">
    
    <div style="text-align: center; color: #6c757d; font-size: 14px;">
        <p>
            Cordialement,<br>
            <strong style="color: #007bff;">L\'équipe du CHU Hassan II</strong><br>
            Service des rendez-vous
        </p>
        
        <p style="margin-top: 20px; font-size: 12px;">
            Cet email a été envoyé automatiquement, merci de ne pas y répondre.<br>
            Pour toute question, contactez-nous au 05 35 XX XX XX
        </p>
    </div>
    
</body>
</html>';

// Email headers
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . $from_name . ' <' . $from_email . '>',
    'Reply-To: ' . $from_email,
    'X-Mailer: PHP/' . phpversion()
];

// Send email
try {
    $mail_sent = mail($to, $subject, $html_content, implode("\r\n", $headers));
    
    if ($mail_sent) {
        echo json_encode([
            'success' => true, 
            'message' => 'Email sent successfully to ' . $to
        ]);
    } else {
        throw new Exception('Mail function returned false');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to send email: ' . $e->getMessage()
    ]);
}
?>