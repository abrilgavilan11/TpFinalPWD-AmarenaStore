<?php

namespace App\Controllers;

use App\Utils\Session;
use App\Utils\EmailSender;

class ContactController extends BaseController
{
    /**
     * Muestra el formulario de contacto
     */
    public function index()
    {
        // Muestra la vista del formulario de contacto y le pasa los datos de configuración.
        $this->view('vistas.tienda.contact', [
            'title' => 'Contacto - Amarena Store',
            'pageCss' => 'contact'
        ]);
    }

    /**
     * Procesa el envío del formulario de contacto
     */
    public function send()
    {
        // Validar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contact');
            return;
        }

        // Recolecta y limpia los datos del formulario
        $name = trim($_POST['nombre'] ?? '');
        $lastname = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['telefono'] ?? '');
        $countryCode = trim($_POST['codigo_area'] ?? '+54');
        $message = trim($_POST['comentarios'] ?? '');
        
        // Validaciones detalladas
        $errors = [];
        
        // Validar nombre
        if (empty($name)) {
            $errors[] = 'El nombre es requerido.';
        } elseif (strlen($name) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        } elseif (!preg_match('/^[A-Za-zÀ-ÿñÑ ]+$/', $name)) {
            $errors[] = 'El nombre solo puede contener letras y espacios.';
        }
        
        // Validar apellido
        if (empty($lastname)) {
            $errors[] = 'El apellido es requerido.';
        } elseif (strlen($lastname) < 2) {
            $errors[] = 'El apellido debe tener al menos 2 caracteres.';
        } elseif (!preg_match('/^[A-Za-zÀ-ÿñÑ ]+$/', $lastname)) {
            $errors[] = 'El apellido solo puede contener letras y espacios.';
        }
        
        // Validar email
        if (empty($email)) {
            $errors[] = 'El email es requerido.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido.';
        }
        
        // Validar teléfono
        if (empty($phone)) {
            $errors[] = 'El teléfono es requerido.';
        } elseif (!preg_match('/^[0-9]+$/', $phone)) {
            $errors[] = 'El teléfono solo puede contener números.';
        } elseif (strlen($phone) < 8) {
            $errors[] = 'El teléfono debe tener al menos 8 dígitos.';
        } elseif (strlen($phone) > 15) {
            $errors[] = 'El teléfono no puede tener más de 15 dígitos.';
        }
        
        // Validar código de área
        $validCountryCodes = ['+54', '+55', '+56', '+57', '+598', '+595', '+591', '+593', '+51', '+58', '+34', '+1'];
        if (!in_array($countryCode, $validCountryCodes)) {
            $errors[] = 'Código de país no válido.';
        }
        
        // Validar mensaje (opcional, pero si se proporciona debe ser razonable)
        if (!empty($message) && strlen($message) > 500) {
            $errors[] = 'El mensaje no puede exceder los 500 caracteres.';
        }
        
        // Procesa resultado
        if (!empty($errors)) {
            // Si hay errores, mostrar el primero
            Session::flash('error', $errors[0]);
            $this->redirect('/contact');
        } else {
            // Si todo está bien, enviar email usando EmailSender
            $fullPhone = $countryCode . ' ' . $phone;
            
            // Inicializar el mailer
            $emailSender = new EmailSender();
            
            // Preparar datos del email
            $to = 'amarenastore2244@gmail.com';
            $subject = 'Nuevo mensaje de contacto - Amarena Store';
            
            // Crear el mensaje HTML mejorado
            $emailBody = $this->buildEmailTemplate($name, $lastname, $email, $fullPhone, $message);
            
            // Intentar enviar el email con múltiples métodos
            $emailSent = false;
            
            // Método 1: SMTP mejorado
            $emailSent = $emailSender->sendViaAPI($to, $subject, $emailBody, $email);
            
            if (!$emailSent) {
                // Método 2: SMTP básico
                error_log("[ContactForm] Método API falló, intentando SMTP básico...");
                $emailSent = $emailSender->sendContactEmail($to, $subject, $emailBody, $email, "$name $lastname");
            }
            
            if (!$emailSent) {
                // Método 3: Respaldo con mail() nativo
                error_log("[ContactForm] SMTP falló, intentando método de respaldo...");
                $emailSent = $this->sendFallbackEmail($to, $subject, $emailBody, $email);
            }
            
            if ($emailSent) {
                error_log("[ContactForm] ✓ Email enviado exitosamente a $to desde $email ($name $lastname)");
                Session::flash('success', '¡Mensaje enviado correctamente! Nos comunicaremos contigo a la brevedad.');
            } else {
                // Guardar en archivo como último recurso
                $this->saveContactToFile($name, $lastname, $email, $fullPhone, $message);
                error_log("[ContactForm] ⚠️ FALLO TOTAL de envío. Datos guardados en archivo. Cliente: $name $lastname - $email - $fullPhone");
                Session::flash('success', '¡Mensaje recibido! Nos comunicaremos contigo a la brevedad.');
            }
            
            $this->redirect('/contact');
        }
    }
    
    /**
     * Crea el template HTML del email
     */
    private function buildEmailTemplate($name, $lastname, $email, $fullPhone, $message)
    {
        $messageHtml = '';
        if (!empty($message)) {
            $messageHtml = "
            <div class='field'>
                <div class='label'>💬 Mensaje:</div>
                <div class='value'>".nl2br(htmlspecialchars($message))."</div>
            </div>";
        }
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0;
            background-color: #f5f5f5;
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header { 
            background: linear-gradient(135deg, #d96a7e, #c55a6e); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content { 
            background: #ffffff; 
            padding: 30px 20px; 
        }
        .field { 
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #d96a7e;
        }
        .label { 
            font-weight: 700; 
            color: #d96a7e; 
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .value { 
            font-size: 16px;
            color: #333;
            line-height: 1.5;
        }
        .value a {
            color: #d96a7e;
            text-decoration: none;
            font-weight: 600;
        }
        .value a:hover {
            text-decoration: underline;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 5px;
            }
            .content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📧 Nuevo Mensaje de Contacto</h2>
            <p>Amarena Store - Sistema de Contacto</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Nombre Completo</div>
                <div class='value'>$name $lastname</div>
            </div>
            
            <div class='field'>
                <div class='label'>📧 Email de Contacto</div>
                <div class='value'><a href='mailto:$email'>$email</a></div>
            </div>
            
            <div class='field'>
                <div class='label'>📞 Teléfono</div>
                <div class='value'><a href='tel:$fullPhone'>$fullPhone</a></div>
            </div>
            
            $messageHtml
            
            <div class='field'>
                <div class='label'>📅 Fecha y Hora</div>
                <div class='value'>".date('d/m/Y H:i:s')." (Hora del servidor)</div>
            </div>
        </div>
        <div class='footer'>
            <p>📧 Email generado automáticamente por el sistema de contacto de Amarena Store</p>
            <p>Para responder, utiliza el botón Reply o responde directamente a: <strong>$email</strong></p>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Método de respaldo para envío de email
     */
    private function sendFallbackEmail($to, $subject, $htmlBody, $replyToEmail)
    {
        try {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: Amarena Store Contacto <noreply@amarenastore.com>',
                'Reply-To: ' . $replyToEmail,
                'X-Mailer: PHP/' . phpversion(),
                'X-Priority: 3'
            ];
            
            return mail($to, $subject, $htmlBody, implode("\r\n", $headers));
            
        } catch (\Exception $e) {
            error_log("[ContactForm] Error en método de respaldo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guarda el contacto en archivo como último recurso
     */
    private function saveContactToFile($name, $lastname, $email, $phone, $message)
    {
        try {
            $contactsDir = dirname(__DIR__) . '/../storage/contacts';
            if (!is_dir($contactsDir)) {
                mkdir($contactsDir, 0777, true);
            }
            
            $filename = $contactsDir . '/contacts_' . date('Y-m') . '.txt';
            $timestamp = date('Y-m-d H:i:s');
            
            $contactData = "\n" . str_repeat('=', 80) . "\n";
            $contactData .= "FECHA: $timestamp\n";
            $contactData .= "NOMBRE: $name $lastname\n";
            $contactData .= "EMAIL: $email\n";
            $contactData .= "TELÉFONO: $phone\n";
            if (!empty($message)) {
                $contactData .= "MENSAJE:\n$message\n";
            }
            $contactData .= str_repeat('=', 80) . "\n";
            
            file_put_contents($filename, $contactData, FILE_APPEND | LOCK_EX);
            error_log("[ContactForm] Contacto guardado en archivo: $filename");
            
        } catch (\Exception $e) {
            error_log("[ContactForm] Error guardando en archivo: " . $e->getMessage());
        }
    }
}
