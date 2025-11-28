<?php

namespace App\Controllers;

use App\Models\Order;
use App\Utils\Auth;

class PdfController extends BaseController
{
    /**
     * Genera y muestra el comprobante HTML de una orden
     */
    public function downloadReceipt($orderId)
    {
        Auth::requireLogin();

        try {
            $orderId = intval($orderId);
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);

            // Verificar que la orden existe y pertenece al usuario o es admin
            if (!$order || ($order['idusuario'] != Auth::getUserId() && !Auth::isAdmin())) {
                $this->redirect('/');
                return;
            }

            $items = $orderModel->getItems($orderId);
            $statusHistory = $orderModel->getStatusHistory($orderId);

            // Genera HTML del comprobante
            $html = $this->generateReceiptHTML($order, $items);

            // Enviar headers para indicar que es un documento imprimible
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit;
        } catch (\Exception $e) {
            error_log("Error al generar comprobante: " . $e->getMessage());
            $this->redirect('/');
        }
    }

    /**
     * Genera el HTML para el comprobante imprimible
     */
    private function generateReceiptHTML($order, $items)
    {
        $total = 0;
        $itemsHTML = '';

        foreach ($items as $item) {
            $subtotal = $item['ciprecio'] * $item['cicantidad'];
            $total += $subtotal;
            $itemsHTML .= "
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['pronombre']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>{$item['cicantidad']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>\$" . number_format($item['ciprecio'], 2) . "</td>
                    <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>\$" . number_format($subtotal, 2) . "</td>
                </tr>
            ";
        }

        $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Comprobante Orden #{$order['idcompra']} - Amarena Store</title>
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        color: #333;
                        line-height: 1.6;
                        margin: 20px;
                        background: #f9f9f9;
                    }
                    .receipt-container {
                        background: white;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 30px;
                        border-radius: 10px;
                        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    }
                    .no-print {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .print-btn {
                        background: #E91E63;
                        color: white;
                        padding: 12px 25px;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        margin-right: 10px;
                    }
                    .print-btn:hover {
                        background: #C2185B;
                    }
                    .back-btn {
                        background: #666;
                        color: white;
                        padding: 12px 25px;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        text-decoration: none;
                        display: inline-block;
                    }
                    .back-btn:hover {
                        background: #555;
                    }
                    @media print {
                        body {
                            background: white;
                            margin: 0;
                        }
                        .receipt-container {
                            box-shadow: none;
                            margin: 0;
                            padding: 20px;
                        }
                        .no-print {
                            display: none;
                        }
                    }
                    .header {
                        text-align: center;
                        border-bottom: 3px solid #E91E63;
                        padding-bottom: 20px;
                        margin-bottom: 30px;
                    }
                    .store-name {
                        font-size: 28px;
                        font-weight: bold;
                        color: #E91E63;
                    }
                    .section {
                        margin-bottom: 25px;
                    }
                    .section-title {
                        font-size: 14px;
                        font-weight: bold;
                        color: #fff;
                        background-color: #E91E63;
                        padding: 10px;
                        margin-bottom: 10px;
                    }
                    .info-row {
                        display: flex;
                        padding: 8px 0;
                        border-bottom: 1px solid #eee;
                    }
                    .info-label {
                        font-weight: bold;
                        width: 40%;
                        color: #E91E63;
                    }
                    .info-value {
                        width: 60%;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    th {
                        background-color: #F2B6B6;
                        color: #333;
                        padding: 10px;
                        text-align: left;
                        font-weight: bold;
                        border-bottom: 2px solid #E91E63;
                    }
                    .total-section {
                        text-align: right;
                        margin-top: 20px;
                        padding: 15px;
                        background-color: #F2F2F2;
                        border-radius: 5px;
                    }
                    .total-row {
                        font-size: 16px;
                        font-weight: bold;
                        color: #E91E63;
                        padding: 10px 0;
                    }
                    .status-badge {
                        display: inline-block;
                        padding: 5px 15px;
                        background-color: #E91E63;
                        color: white;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: bold;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 40px;
                        padding-top: 20px;
                        border-top: 1px solid #ddd;
                        color: #999;
                        font-size: 12px;
                    }
                </style>
            </head>
            <body>
                <div class='receipt-container'>
                    <div class='no-print'>
                        <button class='print-btn' onclick='window.print()'>🖨️ Imprimir Comprobante</button>
                        <a href='/customer/orders' class='back-btn'>← Volver a Mis Órdenes</a>
                    </div>
                    
                <div class='header'>
                    <div class='store-name'>AMARENA STORE</div>
                    <p style='margin: 5px 0; color: #999;'>Moda Inclusiva Para Todos</p>
                </div>

                <div class='section'>
                    <div class='section-title'>Información de la Orden</div>
                    <div class='info-row'>
                        <div class='info-label'>Número de Orden:</div>
                        <div class='info-value'>#" . str_pad($order['idcompra'], 6, '0', STR_PAD_LEFT) . "</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>Fecha:</div>
                        <div class='info-value'>" . date('d/m/Y H:i', strtotime($order['cofecha'])) . "</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>Estado:</div>
                        <div class='info-value'><span class='status-badge'>" . strtoupper($order['estado_actual'] ?? 'Procesando') . "</span></div>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>Datos del Cliente</div>
                    <div class='info-row'>
                        <div class='info-label'>Nombre:</div>
                        <div class='info-value'>" . htmlspecialchars($order['usnombre']) . "</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>Email:</div>
                        <div class='info-value'>" . htmlspecialchars($order['usmail']) . "</div>
                    </div>
                </div>

                <div class='section'>
                    <div class='section-title'>Productos</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style='text-align: center; width: 15%;'>Cantidad</th>
                                <th style='text-align: right; width: 20%;'>Precio Unitario</th>
                                <th style='text-align: right; width: 20%;'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            $itemsHTML
                        </tbody>
                    </table>
                </div>

                <div class='total-section'>
                    <div class='total-row'>Total: \$" . number_format($total, 2) . "</div>
                    <p style='font-size: 11px; color: #999; margin-top: 10px;'>Pago: Mercado Pago</p>
                </div>

                <div class='footer'>
                    <p>Comprobante de compra - Amarena Store</p>
                    <p>Generado el " . date('d/m/Y a las H:i') . "</p>
                </div>                </div>            </body>
            </html>
        ";

        return $html;
    }
}
