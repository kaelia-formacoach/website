<?php
// submit_program.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require '../../vendor/autoload.php';

// Charger le fichier .env
$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

// Informations sur le destinataire et le sujet
$recipient_email = "contact@kaelia-formacoach.com";
$subject = "Nouvelle demande de programme personnalisé - Kaelia";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = isset($_POST["phone"]) ? filter_var(trim($_POST["phone"]), FILTER_SANITIZE_SPECIAL_CHARS) : '';
    $message = isset($_POST["message"]) ? filter_var(trim($_POST["message"]), FILTER_SANITIZE_SPECIAL_CHARS) : '';
    $program = isset($_POST["program"]) ? $_POST["program"] : [];

    // Validation des champs obligatoires
    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo "Veuillez remplir tous les champs obligatoires.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "L'adresse email est invalide.";
        exit;
    }

    // Préparer la liste des modules sélectionnés
    if (!is_array($program)) {
        $program = [$program];
    }
    $programList = !empty($program) ? implode("\n", array_map('htmlspecialchars', $program)) : "Aucun module sélectionné.";

    // Créer une instance de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP en utilisant les variables d’environnement
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Expéditeur et Destinataire
        $mail->setFrom($_ENV['MAIL_USERNAME'], 'Kaelia Forma\'Coach');
        $mail->addAddress($recipient_email);

        // Contenu de l'email
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = "Nom et Prénom : $name\n";
        $mail->Body .= "Email : $email\n";
        $mail->Body .= "Téléphone : $phone\n\n";
        $mail->Body .= "Message :\n$message\n\n";
        $mail->Body .= "Modules sélectionnés :\n$programList";

        // Envoyer l'email
        if ($mail->send()) {
            // Redirection vers une page de confirmation
            header("Location: http://localhost:4321/confirmation");
            exit;
        } else {
            http_response_code(500);
            echo "Erreur : le message n'a pas pu être envoyé.";
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "Le message n'a pas pu être envoyé. Erreur de Mailer : {$mail->ErrorInfo}";
    }
} else {
    http_response_code(405);
    echo "Méthode de requête non valide.";
}
?>
