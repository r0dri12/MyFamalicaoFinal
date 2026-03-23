<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

/**
 * Função para enviar o email de verificação.
 * Inclui um sistema alternativo "fácil" para testar no computador:
 * Se não quiseres configurar o Gmail, ele guarda o link num ficheiro (emails_teste.txt).
 */
function sendVerificationEmail($toEmail, $verificationCode)
{
    // URL base do teu site. Altera se for diferente noutra máquina.
    $verifyLink = "http://localhost/MyFamalicaoFinal/verify?code=" . $verificationCode;

    // --- MODO FÁCIL (Simulação de Email) ---
    // Se a variável $usarGmail for false, gravamos o link num ficheiro
    // de texto em vez de estar a tentar enviar realmente um email via Google.
    $usarGmail = true;

    if (!$usarGmail) {
        $modoFacilLogs = __DIR__ . "/../emails_teste.txt";
        $conteudo = "\n[" . date('Y-m-d H:i:s') . "] NOVO REGISTO -> Email para: " . $toEmail . "\n";
        $conteudo .= "Código de Verificação: " . $verificationCode . "\n";
        file_put_contents($modoFacilLogs, $conteudo, FILE_APPEND);
        return true;
    }
    // ----------------------------------------

    // --- MODO REAL (Servidor SMTP do Gmail) ---
    // Para usares isto muda $usarGmail = true;
    // 1º Vai à tua Conta Google -> Segurança -> Validação em 2 Passos
    // 2º No final da página clica em "Palavras-passe de aplicações" (App Passwords)
    // 3º Gera uma para "MyFamalicao"
    // 4º Copia o código de 16 letras e cola abaixo na variável $passwordSMTP

    $emailSMTP = 'myfamalicao@gmail.com'; // Por favor, altera isto para o email com o qual criaste o código de 16 letras
    $passwordSMTP = 'urvt gtil unaw jsqt';

    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $emailSMTP;
        $mail->Password = $passwordSMTP;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        // $mail->SMTPDebug  = SMTP::DEBUG_SERVER; // (Descomentar para ver erros detalhados no ecrã)

        // Destinatários
        $mail->setFrom($emailSMTP, 'MyFamalicão');
        $mail->addAddress($toEmail);

        // Conteúdo do Email
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Verificação de Conta - MyFamalicão';
        $mail->Body = "<h2>Bem-vindo(a) ao MyFamalicão!</h2>
                          <p>O teu código de verificação é: <b>{$verificationCode}</b></p>
                          <p>Introduz este código na página de verificação para ativares a conta.</p>";
        $mail->AltBody = "O teu código de verificação é: {$verificationCode}";

        $mail->send();
        return true;
    }
    catch (Exception $e) {
        // Erro no envio
        return false;
    }
}
?>
