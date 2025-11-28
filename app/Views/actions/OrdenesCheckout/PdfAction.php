<?php

namespace App\Views\Actions;

use App\Models\Order;
use Mpdf\Mpdf;

class PdfAction
{
    /**
     * Genera el HTML para el comprobante PDF
     */
    public function generateReceiptHTML($order, $items, $currentStatus)
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
                <style>
                    body {
                        font-family: Raleway, sans-serif;
                        color: #333;
                        line-height: 1.6;
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
                        <div class='info-value'><span class='status-badge'>" . strtoupper($currentStatus['cetdescripcion'] ?? 'Procesando') . "</span></div>
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
                </div>
            </body>
            </html>
        ";

        return $html;
    }

    /**
     * Obtiene los datos de la orden para el PDF
     */
    public function getPdfData($orderId)
    {
        try {
            $orderId = intval($orderId);
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);

            if (!$order) {
                return null;
            }

            $items = $orderModel->getItems($orderId);
            $statusHistory = $orderModel->getStatusHistory($orderId);
            $currentStatus = $orderModel->getCurrentStatus($orderId);

            return [
                'order' => $order,
                'items' => $items,
                'statusHistory' => $statusHistory,
                'currentStatus' => $currentStatus
            ];
        } catch (\Exception $e) {
            error_log("Error al obtener datos PDF: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera el PDF y lo devuelve
     */
    public function generatePdf($orderId)
    {
        try {
            $pdfData = $this->getPdfData($orderId);

            if (!$pdfData) {
                return null;
            }

            $html = $this->generateReceiptHTML(
                $pdfData['order'],
                $pdfData['items'],
                $pdfData['currentStatus']
            );

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);

            $mpdf->WriteHTML($html);

            return [
                'success' => true,
                'mpdf' => $mpdf,
                'filename' => 'comprobante_orden_' . $orderId . '.pdf'
            ];
        } catch (\Exception $e) {
            error_log("Error al generar PDF: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al generar PDF'
            ];
        }
    }
}
