<?php

namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Actualizado: Usar configuración centralizada de mail.php
 */
class Email
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->config = require BASE_PATH . '/config/mail.php';
        $this->mailer = new PHPMailer(true);
        
        // Configuración SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->Port = $this->config['port'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['encryption'];
        $this->mailer->CharSet = 'UTF-8';
        
        $this->mailer->setFrom($this->config['from']['email'], $this->config['from']['name']);
    }

    /**
     * Envía un email de notificación de cambio de estado de compra
     */
    public function sendOrderStatusNotification(string $customerEmail, string $customerName, int $orderId, string $statusDescription, string $statusDetail): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customerEmail, $customerName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Actualización de tu pedido #$orderId - Amarena Store";
            $this->mailer->Body = $this->buildOrderStatusEmailBody($orderId, $customerName, $statusDescription, $statusDetail);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar email de estado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía un email de confirmación de nueva orden
     */
    public function sendOrderConfirmation(string $customerEmail, string $customerName, int $orderId, float $total): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customerEmail, $customerName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Confirmación de tu pedido #$orderId - Amarena Store";
            $this->mailer->Body = $this->buildOrderConfirmationEmailBody($orderId, $customerName, $total);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar confirmación de orden: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía un email de confirmación de registro
     * Nuevo método para notificar nuevos usuarios
     */
    public function sendWelcomeEmail(string $customerEmail, string $customerName): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($customerEmail, $customerName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Bienvenido a Amarena Store";
            $this->mailer->Body = $this->buildWelcomeEmailBody($customerName);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar email de bienvenida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Construye el cuerpo HTML para notificación de estado
     */
    private function buildOrderStatusEmailBody(int $orderId, string $customerName, string $status, string $detail): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Raleway, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; }
                    .header { background-color: #f5a623; color: white; padding: 20px; text-align: center; }
                    .header h1 { margin: 0; }
                    .content { padding: 30px; }
                    .status-badge { display: inline-block; padding: 8px 16px; background-color: #e8f4f8; border-left: 4px solid #0066cc; margin: 20px 0; }
                    .footer { background-color: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Amarena Store</h1>
                    </div>
                    <div class='content'>
                        <p>Hola <strong>$customerName</strong>,</p>
                        <p>Tu pedido ha cambió de estado:</p>
                        <div class='status-badge'>
                            <strong>Estado:</strong> <em>$status</em>
                        </div>
                        <p><strong>Detalles:</strong></p>
                        <p>$detail</p>
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                            Número de pedido: <strong>#$orderId</strong>
                        </p>
                        <p>Si tienes preguntas, no dudes en contactarnos.</p>
                        <p>Gracias por confiar en Amarena Store.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2025 Amarena Store. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Construye el cuerpo HTML para confirmación de orden
     */
    private function buildOrderConfirmationEmailBody(int $orderId, string $customerName, float $total): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Raleway, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; }
                    .header { background-color: #f5a623; color: white; padding: 20px; text-align: center; }
                    .header h1 { margin: 0; }
                    .content { padding: 30px; }
                    .order-info { background-color: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    .footer { background-color: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Amarena Store</h1>
                    </div>
                    <div class='content'>
                        <p>Hola <strong>$customerName</strong>,</p>
                        <p>Tu pedido ha sido recibido correctamente. Estamos procesándolo en este momento.</p>
                        <div class='order-info'>
                            <p><strong>Número de Pedido:</strong> #$orderId</p>
                            <p><strong>Total:</strong> \$$total</p>
                            <p><strong>Estado:</strong> Iniciada</p>
                        </div>
                        <p>Pronto recibirás una confirmación de aceptación de tu pedido.</p>
                        <p>Puedes ver el estado de tu pedido en cualquier momento iniciando sesión en tu cuenta.</p>
                        <p>Gracias por tu compra en Amarena Store.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2025 Amarena Store. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Construye el cuerpo HTML para email de bienvenida
     * Nuevo template de bienvenida
     */
    private function buildWelcomeEmailBody(string $customerName): string
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Raleway, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; }
                    .header { background-color: #f5a623; color: white; padding: 20px; text-align: center; }
                    .content { padding: 30px; }
                    .footer { background-color: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Bienvenido a Amarena Store</h1>
                    </div>
                    <div class='content'>
                        <p>Hola <strong>$customerName</strong>,</p>
                        <p>Tu cuenta ha sido creada exitosamente. Bienvenido a nuestra comunidad.</p>
                        <p>Ahora puedes:</p>
                        <ul>
                            <li>Explorar nuestro catálogo de productos</li>
                            <li>Agregar productos a tu carrito</li>
                            <li>Realizar compras</li>
                            <li>Ver el historial de tus órdenes</li>
                        </ul>
                        <p>Si necesitas ayuda, no dudes en contactarnos.</p>
                        <p>Gracias por elegir Amarena Store.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2025 Amarena Store. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}
