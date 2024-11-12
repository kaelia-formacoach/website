<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

// Informations sur le destinataire et le sujet
$recipient_email = "m.barre@kaelia-formacoach.com"; // Remplacez par votre email de contact
$subject = "Nouveau message de contact - Kaelia Forma'Coach";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($_POST["phone"]), FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($name) || empty($email) || empty($message)) {
        echo "Veuillez remplir tous les champs obligatoires.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "L'adresse email est invalide.";
        exit;
    }

    // Initialisation de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'm.barre@kaelia-formacoach.com'; // Remplacez par votre adresse email
        $mail->Password = 'ebre cibg edyh jwmt'; // Remplacez par votre mot de passe
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // ou PHPMailer::ENCRYPTION_SMTPS pour SSL
        $mail->Port = 587; // Utilisez 465 pour SSL ou 587 pour TLS

        // Expéditeur et Destinataire
        $mail->setFrom('m.barre@kaelia-formacoach.com', 'Kaelia Forma\'Coach'); // Adresse et nom de l'expéditeur
        $mail->addAddress($recipient_email); // Envoyer à l'adresse de contact elle-même

        // Contenu de l'email
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = "Nom et Prénom : $name\nEmail : $email\nTéléphone : $phone\n\nMessage :\n$message\n";

        // Envoyer l'email
        if ($mail->send()) {
            // Redirection vers la page de confirmation
            header("Location: http://localhost:4321/confirmation");
            exit;
        } else {
            echo "Erreur : le message n'a pas pu être envoyé.";
        }
    } catch (Exception $e) {
        echo "Le message n'a pas pu être envoyé. Erreur de Mailer : {$mail->ErrorInfo}";
    }
} else {
    echo "Méthode de requête non valide.";
}
?>
