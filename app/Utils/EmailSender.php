<?php

namespace App\Utils;

use Exception;

class EmailSender
{
    private $smtpConfig;
    
    public function __construct()
    {
        // Configuración SMTP - Puedes cambiar estos valores según tu proveedor
        $this->smtpConfig = [
            'host' => 'smtp.gmail.com', 
            'port' => 587,              
            'username' => 'amarenastore2244@gmail.com',
            'password' => 'e;ojf;wkej', 
            'encryption' => 'tls',      
            'from_email' => 'amarenastore2244@gmail.com',
            'from_name' => 'Amarena Store - Contacto'
        ];
    }
    
    /**
     * Envía un email usando SMTP real con autenticación
     */
    public function sendContactEmail($to, $subject, $htmlBody, $replyToEmail = null, $replyToName = null)
    {
        try {
            // Intentar primero con SMTP real
            $smtpResult = $this->sendViaSMTP($to, $subject, $htmlBody, $replyToEmail, $replyToName);
            
            if ($smtpResult) {
                error_log("[EmailSender] Email enviado correctamente via SMTP a: $to");
                return true;
            }
            
            // Si falla SMTP, intentar con mail() básico
            error_log("[EmailSender] SMTP falló, intentando con mail() básico...");
            return $this->sendViaBasicMail($to, $subject, $htmlBody, $replyToEmail, $replyToName);
            
        } catch (Exception $e) {
            error_log("[EmailSender] Excepción al enviar email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envía email usando SMTP con autenticación real
     */
    private function sendViaSMTP($to, $subject, $htmlBody, $replyToEmail = null, $replyToName = null)
    {
        try {
            // Crear conexión socket
            $socket = fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 30);
            
            if (!$socket) {
                error_log("[EmailSender] No se pudo conectar a Gmail SMTP: $errstr ($errno)");
                return false;
            }
            
            // Leer respuesta inicial
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("[EmailSender] Respuesta inicial incorrecta: $response");
                fclose($socket);
                return false;
            }
            
            // HELO
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 515);
            
            // AUTH LOGIN
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            
            // Username
            fputs($socket, base64_encode($this->smtpConfig['username']) . "\r\n");
            $response = fgets($socket, 515);
            
            // Password
            fputs($socket, base64_encode($this->smtpConfig['password']) . "\r\n");
            $response = fgets($socket, 515);
            
            if (substr($response, 0, 3) != '235') {
                error_log("[EmailSender] Autenticación falló: $response");
                fclose($socket);
                return false;
            }
            
            // MAIL FROM
            fputs($socket, "MAIL FROM: <{$this->smtpConfig['from_email']}>\r\n");
            $response = fgets($socket, 515);
            
            // RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            
            // DATA
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            
            // Headers y contenido
            $replyTo = $replyToEmail ? ($replyToName ? "$replyToName <$replyToEmail>" : $replyToEmail) : '';
            
            $headers = "From: {$this->smtpConfig['from_name']} <{$this->smtpConfig['from_email']}>\r\n";
            $headers .= "To: <$to>\r\n";
            $headers .= "Subject: $subject\r\n";
            if ($replyTo) {
                $headers .= "Reply-To: $replyTo\r\n";
            }
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "X-Mailer: Amarena Store PHP Mailer\r\n";
            $headers .= "\r\n";
            
            fputs($socket, $headers . $htmlBody . "\r\n.\r\n");
            $response = fgets($socket, 515);
            
            // QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            if (substr($response, 0, 3) == '250') {
                return true;
            } else {
                error_log("[EmailSender] Error al enviar mensaje: $response");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("[EmailSender] Error en SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método de respaldo usando mail() básico
     */
    private function sendViaBasicMail($to, $subject, $htmlBody, $replyToEmail = null, $replyToName = null)
    {
        try {
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
            $headers[] = 'From: ' . $this->smtpConfig['from_name'] . ' <' . $this->smtpConfig['from_email'] . '>';
            
            if ($replyToEmail) {
                $replyName = $replyToName ? $replyToName : $replyToEmail;
                $headers[] = 'Reply-To: ' . $replyName . ' <' . $replyToEmail . '>';
            }
            
            $headers[] = 'X-Mailer: PHP/' . phpversion();
            $headers[] = 'X-Priority: 3';
            
            $result = mail($to, $subject, $htmlBody, implode("\r\n", $headers));
            
            if ($result) {
                error_log("[EmailSender] Email enviado correctamente con mail() básico a: $to");
                return true;
            } else {
                error_log("[EmailSender] Error con mail() básico a: $to");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("[EmailSender] Error en mail() básico: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método usando SMTP via cURL (más confiable)
     */
    public function sendViaAPI($to, $subject, $htmlBody, $replyToEmail = null)
    {
        try {
            // Usar servicio gratuito de EmailJS o similar
            // Por ahora, implementar un método SMTP mejorado
            return $this->sendViaImprovedSMTP($to, $subject, $htmlBody, $replyToEmail);
            
        } catch (Exception $e) {
            error_log("[EmailSender] Error en API: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método SMTP mejorado usando stream context
     */
    private function sendViaImprovedSMTP($to, $subject, $htmlBody, $replyToEmail = null)
    {
        try {
            // Configurar context para SSL
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            
            // Conectar a Gmail SMTP
            $smtp = stream_socket_client(
                'ssl://smtp.gmail.com:465',
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$smtp) {
                error_log("[EmailSender] Error conectando SMTP: $errstr ($errno)");
                return false;
            }
            
            // Función para enviar comando y leer respuesta
            $sendCommand = function($command) use ($smtp) {
                fwrite($smtp, $command . "\r\n");
                $response = fread($smtp, 1024);
                error_log("[EmailSender] CMD: $command | RESP: " . trim($response));
                return $response;
            };
            
            // Leer banner inicial
            $response = fread($smtp, 1024);
            error_log("[EmailSender] Banner: " . trim($response));
            
            // EHLO
            $response = $sendCommand('EHLO localhost');
            if (strpos($response, '250') === false) {
                fclose($smtp);
                return false;
            }
            
            // AUTH LOGIN
            $response = $sendCommand('AUTH LOGIN');
            if (strpos($response, '334') === false) {
                fclose($smtp);
                return false;
            }
            
            // Username
            $response = $sendCommand(base64_encode($this->smtpConfig['username']));
            if (strpos($response, '334') === false) {
                fclose($smtp);
                return false;
            }
            
            // Password
            $response = $sendCommand(base64_encode($this->smtpConfig['password']));
            if (strpos($response, '235') === false) {
                error_log("[EmailSender] Autenticación fallida: " . trim($response));
                fclose($smtp);
                return false;
            }
            
            // MAIL FROM
            $response = $sendCommand('MAIL FROM:<' . $this->smtpConfig['from_email'] . '>');
            if (strpos($response, '250') === false) {
                fclose($smtp);
                return false;
            }
            
            // RCPT TO
            $response = $sendCommand('RCPT TO:<' . $to . '>');
            if (strpos($response, '250') === false) {
                fclose($smtp);
                return false;
            }
            
            // DATA
            $response = $sendCommand('DATA');
            if (strpos($response, '354') === false) {
                fclose($smtp);
                return false;
            }
            
            // Construir mensaje completo
            $message = "From: {$this->smtpConfig['from_name']} <{$this->smtpConfig['from_email']}>\r\n";
            $message .= "To: <$to>\r\n";
            $message .= "Subject: $subject\r\n";
            
            if ($replyToEmail) {
                $message .= "Reply-To: <$replyToEmail>\r\n";
            }
            
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Date: " . date('r') . "\r\n";
            $message .= "\r\n";
            $message .= $htmlBody . "\r\n";
            $message .= ".";
            
            // Enviar mensaje
            $response = $sendCommand($message);
            
            // QUIT
            $sendCommand('QUIT');
            fclose($smtp);
            
            if (strpos($response, '250') !== false) {
                error_log("[EmailSender] Email enviado exitosamente via SMTP mejorado");
                return true;
            } else {
                error_log("[EmailSender] Error enviando mensaje: " . trim($response));
                return false;
            }
            
        } catch (Exception $e) {
            error_log("[EmailSender] Error en SMTP mejorado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Configurar SMTP para servidores locales (XAMPP, WAMP, etc.)
     */
    public function configureSMTPForLocal()
    {
        // Para XAMPP en Windows, configurar sendmail
        $sendmailPath = 'C:\\xampp\\sendmail\\sendmail.exe';
        if (file_exists($sendmailPath)) {
            ini_set('sendmail_path', $sendmailPath . ' -t');
            ini_set('SMTP', 'localhost');
            ini_set('smtp_port', '25');
            return true;
        }
        return false;
    }
    
    /**
     * Valida la configuración de email
     */
    public function validateConfig()
    {
        $issues = [];
        
        if (empty($this->smtpConfig['host'])) {
            $issues[] = 'Host SMTP no configurado';
        }
        
        if (empty($this->smtpConfig['username'])) {
            $issues[] = 'Usuario SMTP no configurado';
        }
        
        if (empty($this->smtpConfig['password'])) {
            $issues[] = 'Contraseña SMTP no configurada (requerida para producción)';
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Enviar notificación de cambio de contraseña
     */
    public function sendPasswordChangeNotification($toEmail, $userName)
    {
        $subject = 'Contraseña actualizada - Amarena Store';
        
        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #d96a7e, #c1577a); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0;'>Amarena Store</h1>
            </div>
            
            <div style='padding: 30px; background: #f9f9f9;'>
                <h2 style='color: #d96a7e;'>¡Contraseña actualizada!</h2>
                
                <p>Hola <strong>{$userName}</strong>,</p>
                
                <p>Te confirmamos que tu contraseña ha sido actualizada exitosamente.</p>
                
                <p>Si no realizaste este cambio, por favor contacta con nosotros inmediatamente.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . BASE_URL . "/customer/dashboard' 
                       style='background: #d96a7e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>
                        Acceder a mi cuenta
                    </a>
                </div>
                
                <p style='color: #666; font-size: 14px;'>
                    Si tienes alguna pregunta, no dudes en contactarnos.
                </p>
            </div>
            
            <div style='background: #333; color: white; padding: 20px; text-align: center; font-size: 12px;'>
                © " . date('Y') . " Amarena Store. Todos los derechos reservados.
            </div>
        </div>";
        
        $textBody = "
        AMARENA STORE
        
        ¡Contraseña actualizada!
        
        Hola {$userName},
        
        Te confirmamos que tu contraseña ha sido actualizada exitosamente.
        
        Si no realizaste este cambio, por favor contacta con nosotros inmediatamente.
        
        Accede a tu cuenta: " . BASE_URL . "/customer/dashboard
        
        Si tienes alguna pregunta, no dudes en contactarnos.
        ";
        
        return $this->send($toEmail, $subject, $htmlBody, $textBody);
    }

    /**
     * Enviar email de recuperación de contraseña
     */
    public function sendPasswordResetEmail($toEmail, $userName, $resetLink)
    {
        $subject = 'Recuperar contraseña - Amarena Store';
        
        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #d96a7e, #c1577a); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0;'>Amarena Store</h1>
            </div>
            
            <div style='padding: 30px; background: #f9f9f9;'>
                <h2 style='color: #d96a7e;'>Recuperar contraseña</h2>
                
                <p>Hola <strong>{$userName}</strong>,</p>
                
                <p>Recibimos una solicitud para cambiar tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' 
                       style='background: #d96a7e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block;'>
                        Cambiar mi contraseña
                    </a>
                </div>
                
                <p style='color: #666; font-size: 14px;'>
                    Este enlace expirará en 1 hora por razones de seguridad.<br>
                    Si no solicitaste este cambio, puedes ignorar este email.
                </p>
                
                <p style='color: #999; font-size: 12px; word-break: break-all;'>
                    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                    {$resetLink}
                </p>
            </div>
            
            <div style='background: #333; color: white; padding: 20px; text-align: center; font-size: 12px;'>
                © " . date('Y') . " Amarena Store. Todos los derechos reservados.
            </div>
        </div>";
        
        $textBody = "
        AMARENA STORE
        
        Recuperar contraseña
        
        Hola {$userName},
        
        Recibimos una solicitud para cambiar tu contraseña. Usa este enlace para crear una nueva contraseña:
        
        {$resetLink}
        
        Este enlace expirará en 1 hora por razones de seguridad.
        Si no solicitaste este cambio, puedes ignorar este email.
        ";
        
        return $this->send($toEmail, $subject, $htmlBody, $textBody);
    }
}