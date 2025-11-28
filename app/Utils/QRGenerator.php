<?php

namespace App\Utils;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PngWriter;

class QRGenerator
{
    /**
     * Genera un código QR para pago
     * 
     * @param array $paymentData Datos del pago (order_id, total, customer_info)
     * @return array ['qr_path' => string, 'qr_data' => string]
     */
    public static function generatePaymentQR(array $paymentData): array
    {
        try {
            $orderId = $paymentData['temp_order_id'] ?? $paymentData['order_id'] ?? 'unknown';
            error_log("[QRGenerator] Starting QR generation for order: " . $orderId);
            
            // Crear datos del QR con formato para pago
            $qrData = self::createPaymentData($paymentData);
            error_log("[QRGenerator] QR data created: " . $qrData);
        
            // Generar el código QR
            error_log("[QRGenerator] Building QR code...");
            $result = Builder::create()
                ->writer(new SvgWriter())
                ->writerOptions([])
                ->data($qrData)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(400)
                ->margin(20)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->build();
            
            error_log("[QRGenerator] QR code built successfully");

            // Generar nombre único para el archivo
            $orderId = $paymentData['temp_order_id'] ?? $paymentData['order_id'] ?? 'unknown';
            $fileName = 'qr_payment_' . $orderId . '_' . time() . '.svg';
            $filePath = __DIR__ . '/../../public/uploads/qr/' . $fileName;
            error_log("[QRGenerator] File path: " . $filePath);
            
            // Crear directorio si no existe
            $uploadDir = dirname($filePath);
            if (!is_dir($uploadDir)) {
                error_log("[QRGenerator] Creating directory: " . $uploadDir);
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new \Exception("No se pudo crear el directorio QR: " . $uploadDir);
                }
            }
            
            // Verificar que el directorio sea escribible
            if (!is_writable($uploadDir)) {
                throw new \Exception("El directorio QR no es escribible: " . $uploadDir);
            }
            
            // Generar SVG en línea en lugar de archivo
            $svgString = $result->getString();
            error_log("[QRGenerator] SVG string generated, length: " . strlen($svgString));
            
            // Intentar guardar archivo pero no fallar si no se puede
            try {
                $bytesWritten = file_put_contents($filePath, $svgString);
                if ($bytesWritten !== false) {
                    error_log("[QRGenerator] QR file written successfully, bytes: " . $bytesWritten);
                    $qrPath = '/uploads/qr/' . $fileName;
                } else {
                    error_log("[QRGenerator] Could not write file, using inline SVG");
                    $qrPath = 'data:image/svg+xml;base64,' . base64_encode($svgString);
                }
            } catch (\Exception $fileError) {
                error_log("[QRGenerator] File write error: " . $fileError->getMessage() . ", using inline SVG");
                $qrPath = 'data:image/svg+xml;base64,' . base64_encode($svgString);
            }
            
            // Usar temp_order_id si existe, sino order_id
            $orderId = $paymentData['temp_order_id'] ?? $paymentData['order_id'];
            
            return [
                'qr_path' => $qrPath,
                'qr_data' => $qrData,
                'qr_url' => self::createPaymentVerificationUrl($orderId),
                'qr_svg' => $svgString
            ];
        } catch (\Exception $e) {
            error_log("[QRGenerator] Error generating QR: " . $e->getMessage());
            error_log("[QRGenerator] Exception trace: " . $e->getTraceAsString());
            throw new \Exception("Error al generar código QR: " . $e->getMessage());
        }
    }
    
    /**
     * Crea los datos estructurados para el QR de pago
     */
    private static function createPaymentData(array $paymentData): string
    {
        // Usar temp_order_id si existe, sino order_id
        $orderId = $paymentData['temp_order_id'] ?? $paymentData['order_id'];
        
        // URL que procesará el pago cuando se escanee el QR
        $verificationUrl = self::createPaymentVerificationUrl($orderId);
        
        // Datos adicionales para mostrar en el QR scanner
        $paymentInfo = [
            'store' => 'Amarena Store',
            'order_id' => $orderId,
            'total' => $paymentData['total'],
            'currency' => 'ARS',
            'customer' => $paymentData['customer_name'],
            'verification_url' => $verificationUrl
        ];
        
        // Retorna la URL que procesará el pago
        return $verificationUrl;
    }
    
    /**
     * Crea la URL de verificación de pago
     */
    private static function createPaymentVerificationUrl(string $orderId): string
    {
        // Determinar la URL base correcta
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $baseUrl = $protocol . '://' . $host;
        
        return $baseUrl . '/pagar/' . $orderId . '?token=' . self::generateSecureToken($orderId);
    }
    
    /**
     * Genera un token seguro para verificar el pago
     */
    private static function generateSecureToken(string $orderId): string
    {
        $secretKey = $_ENV['QR_SECRET_KEY'] ?? 'amarena_qr_secret_2025';
        return hash_hmac('sha256', $orderId . date('Y-m-d'), $secretKey);
    }
    
    /**
     * Verifica si un token de pago es válido
     */
    public static function verifyPaymentToken(string $orderId, string $token): bool
    {
        $expectedToken = self::generateSecureToken($orderId);
        return hash_equals($expectedToken, $token);
    }
    
    /**
     * Genera QR simple con texto personalizado
     */
    public static function generateSimpleQR(string $text, string $fileName = null): string
    {
        $fileName = $fileName ?? 'qr_' . uniqid() . '.png';
        
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($text)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(300)
            ->margin(10)
            ->build();
        
        $filePath = __DIR__ . '/../../public/uploads/qr/' . $fileName;
        
        // Crear directorio si no existe
        $uploadDir = dirname($filePath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        file_put_contents($filePath, $result->getString());
        
        return '/uploads/qr/' . $fileName;
    }
}