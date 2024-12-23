<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require '../../vendor/autoload.php';

// Charger le fichier .env
$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

// Informations sur le destinataire et le sujet
$recipient_email = "contact@kaelia-formacoach.com";
$subject = "Nouvelle candidature - Kaelia";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($_POST["phone"]), FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérification des champs obligatoires
    if (empty($name) || empty($email) || empty($message) || !isset($_FILES['cv']) || !isset($_FILES['cover_letter'])) {
        echo "Veuillez remplir tous les champs obligatoires.";
        exit;
    }

    // Validation de l'adresse email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "L'adresse email est invalide.";
        exit;
    }

    // Vérification du fichier CV
    $cv = $_FILES['cv'];
    if ($cv['error'] !== UPLOAD_ERR_OK || $cv['type'] !== 'application/pdf') {
        echo "Veuillez uploader un fichier PDF valide pour le CV.";
        exit;
    }

    // Vérification du fichier de la lettre de motivation
    $cover_letter = $_FILES['cover_letter'];
    if ($cover_letter['error'] !== UPLOAD_ERR_OK || $cover_letter['type'] !== 'application/pdf') {
        echo "Veuillez uploader un fichier PDF valide pour la lettre de motivation.";
        exit;
    }

    // PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Expéditeur et destinataire
        $mail->setFrom($_ENV['MAIL_USERNAME'], 'Kaelia Forma\'Coach');
        $mail->addAddress($recipient_email);

        // Pièces jointes
        $mail->addAttachment($cv['tmp_name'], $cv['name']);
        $mail->addAttachment($cover_letter['tmp_name'], $cover_letter['name']);

        // Contenu de l'email
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = "Nom et Prénom : $name\nEmail : $email\nTéléphone : $phone\n\nMessage :\n$message\n";

        // Envoi de l'email
        if ($mail->send()) {
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
