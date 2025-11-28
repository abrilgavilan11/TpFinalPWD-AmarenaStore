<?php

namespace App\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Utils\Auth;
use App\Utils\Session;
use App\Utils\QRGenerator;

class CheckoutFlowController extends BaseController
{
    /**
     * Etapa 1: Mostrar carrito con botón "Finalizar Compra"
     */
    public function cart()
    {
        Auth::requireLogin();
        
        Session::start();
        $cartData = Session::get('cart', []);
        
        if (empty($cartData)) {
            Session::flash('error', 'Tu carrito está vacío.');
            $this->redirect('/productos');
            return;
        }
        
        // Procesar items del carrito
        $cartItems = [];
        $total = 0;
        
        foreach ($cartData as $itemId => $item) {
            $item['itemTotal'] = $item['price'] * $item['quantity'];
            $item['id'] = $itemId;
            $cartItems[] = $item;
            $total += $item['itemTotal'];
        }
        
        $this->view('vistas.checkout.step1_cart', [
            'title' => 'Tu Carrito - Amarena Store',
            'pageCss' => 'checkout-flow',
            'cartItems' => $cartItems,
            'total' => $total,
            'step' => 1
        ]);
    }
    
    /**
     * Etapa 2: Formulario de datos del cliente
     */
    public function customerData()
    {
        Auth::requireLogin();
        
        // Verificar que hay items en el carrito
        Session::start();
        $cartData = Session::get('cart', []);
        
        if (empty($cartData)) {
            Session::flash('error', 'Tu carrito está vacío.');
            $this->redirect('/productos');
            return;
        }
        
        // Obtener datos del usuario actual si los tiene
        $userId = Session::get('user_id');
        $userData = [
            'name' => Session::get('user_name', ''),
            'email' => Session::get('user_email', ''),
        ];
        
        $this->view('vistas.checkout.step2_customer_data', [
            'title' => 'Tus Datos - Amarena Store',
            'pageCss' => 'checkout-flow',
            'userData' => $userData,
            'step' => 2
        ]);
    }
    
    /**
     * Procesar datos del cliente (POST desde step2)
     */
    public function processCustomerData()
    {
        Auth::requireLogin();
        
        error_log("[CheckoutFlow] processCustomerData called, method: " . $_SERVER['REQUEST_METHOD']);
        error_log("[CheckoutFlow] POST data: " . json_encode($_POST));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/checkout/customer-data');
            return;
        }
        
        // Validar datos del formulario
        $customerData = $this->validateCustomerData($_POST);
        error_log("[CheckoutFlow] Validation result: " . json_encode($customerData));
        
        if (!$customerData['valid']) {
            Session::flash('error', $customerData['error']);
            error_log("[CheckoutFlow] Validation failed: " . $customerData['error']);
            $this->redirect(BASE_URL . '/checkout/customer-data');
            return;
        }
        
        // Guardar datos del cliente en sesión
        Session::start();
        Session::set('checkout_customer_data', $customerData['data']);
        error_log("[CheckoutFlow] Customer data saved to session");
        
        // Redirigir al resumen
        $this->redirect(BASE_URL . '/checkout/summary');
    }
    
    /**
     * Etapa 3: Resumen de compra
     */
    public function summary()
    {
        Auth::requireLogin();
        
        Session::start();
        $cartData = Session::get('cart', []);
        
        if (empty($cartData)) {
            Session::flash('error', 'Tu carrito está vacío.');
            $this->redirect('/productos');
            return;
        }
        
        // Verificar que existan datos del cliente en sesión
        $customerData = Session::get('checkout_customer_data');
        if (!$customerData) {
            Session::flash('error', 'Faltan datos del cliente. Por favor completa el formulario.');
            $this->redirect(BASE_URL . '/checkout/customer-data');
            return;
        }
        
        // Procesar items del carrito para mostrar
        $cartItems = [];
        $total = 0;
        
        foreach ($cartData as $itemId => $item) {
            $item['itemTotal'] = $item['price'] * $item['quantity'];
            $item['id'] = $itemId;
            $cartItems[] = $item;
            $total += $item['itemTotal'];
        }
        
        $this->view('vistas.checkout.step3_summary', [
            'title' => 'Resumen de Compra - Amarena Store',
            'pageCss' => 'checkout-flow',
            'cartItems' => $cartItems,
            'totals' => [
                'subtotal' => $total,
                'shipping' => 0, // Envío gratis por ahora
                'total' => $total,
                'total_items' => count($cartItems)
            ],
            'customerData' => $customerData,
            'step' => 3
        ]);
    }
    
    /**
     * Etapa 4: Generar QR y procesar pago
     */
    public function generateQR()
    {
        Auth::requireLogin();
        
        error_log("[CheckoutFlow] generateQR called");
        error_log("[CheckoutFlow] Headers: " . json_encode(getallheaders()));
        error_log("[CheckoutFlow] REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        
        // Si es request AJAX, responder con JSON
        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || 
                  (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json');
        
        error_log("[CheckoutFlow] isAjax: " . ($isAjax ? 'true' : 'false'));
        
        Session::start();
        $cartData = Session::get('cart', []);
        $customerData = Session::get('checkout_customer_data');
        
        if (empty($cartData) || empty($customerData)) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Datos de checkout incompletos.']);
                return;
            }
            Session::flash('error', 'Datos de checkout incompletos.');
            $this->redirect('/checkout/customer-data');
            return;
        }
        
        try {
            // NO crear la orden aún, solo validar datos y generar QR con datos temporales
            error_log("[CheckoutFlow] Validating cart data...");
            
            // Validar stock antes de generar QR (sin reducir stock)
            $validationResult = $this->validateCartStock($cartData);
            if (!$validationResult['success']) {
                if ($isAjax) {
                    $this->json(['success' => false, 'message' => $validationResult['message']]);
                    return;
                }
                
                Session::flash('error', $validationResult['message']);
                $this->redirect(BASE_URL . '/checkout/summary');
                return;
            }
            
            // Generar número de orden real
            $orderModel = new Order();
            $orderNumber = $orderModel->generateOrderNumber();
            error_log("[CheckoutFlow] Generated order number: #" . $orderNumber);
            
            // Calcular subtotal y total
            $subtotal = 0;
            foreach ($cartData as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = 0; // Envío gratis por ahora
            $discount = 0; // Descuento no implementado aún
            $total = $subtotal + $shipping - $discount;

            // Generar QR con datos de orden
            $qrData = [
                'order_id' => $orderNumber,
                'total' => $total,
                'customer_name' => $customerData['full_name']
            ];
            
            error_log("[CheckoutFlow] Generating QR with data: " . json_encode($qrData));
            
            try {
                $qrResult = QRGenerator::generatePaymentQR($qrData);
                error_log("[CheckoutFlow] QR generation successful: " . json_encode($qrResult));
            } catch (\Exception $qrError) {
                error_log("[CheckoutFlow] QR generation failed: " . $qrError->getMessage());
                throw new \Exception("Error generando QR: " . $qrError->getMessage());
            }
            
            // Guardar datos para la vista QR (sin crear orden aún)
            Session::set('checkout_qr_data', $qrResult);
            Session::set('checkout_temp_order', [
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'cart_data' => $cartData,
                'customer_data' => $customerData
            ]);
            
            // NO limpiar el carrito aún - se limpiará cuando se confirme el pago
            
            if ($isAjax) {
                $this->json(['success' => true, 'redirect_url' => BASE_URL . '/checkout/qr-payment']);
            } else {
                // Redirigir a la vista QR
                $this->redirect(BASE_URL . '/checkout/qr-payment');
            }
            
        } catch (\Exception $e) {
            error_log("[CheckoutFlow] Exception in generateQR: " . $e->getMessage());
            error_log("[CheckoutFlow] Exception trace: " . $e->getTraceAsString());
            
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Error al generar el código QR. Inténtalo nuevamente.']);
            } else {
                Session::flash('error', 'Error al generar el código QR. Inténtalo nuevamente.');
                $this->redirect(BASE_URL . '/checkout/summary');
            }
        }
    }
    
    /**
     * Mostrar página de pago QR (GET)
     */
    public function qrPayment()
    {
        Auth::requireLogin();
        
        Session::start();
        $qrData = Session::get('checkout_qr_data');
        $tempOrderData = Session::get('checkout_temp_order');
        $customerData = Session::get('checkout_customer_data');
        
        if (empty($qrData) || empty($tempOrderData)) {
            Session::flash('error', 'Sesión de pago expirada.');
            $this->redirect('/checkout/customer-data');
            return;
        }
        
        // Usar datos con número de orden y totales completos
        $orderData = [
            'order_id' => $tempOrderData['order_number'] ?? 'PENDING',
            'total' => $tempOrderData['total'] ?? 0,
            'subtotal' => $tempOrderData['subtotal'] ?? 0,
            'shipping' => $tempOrderData['shipping'] ?? 0,
            'discount' => $tempOrderData['discount'] ?? 0
        ];
        
        $this->view('vistas.checkout.step4_qr_payment', [
            'title' => 'Pago con QR - Amarena Store',
            'pageCss' => 'checkout-flow',
            'qrData' => $qrData,
            'orderData' => $orderData,
            'customerData' => $customerData,
            'step' => 4
        ]);
    }
    
    /**
     * Verificar estado del pago (llamado desde JavaScript o QR scan)
     */
    public function verifyPayment($orderId = null)
    {
        // Debug: Log básico
        error_log("verifyPayment: Method=" . $_SERVER['REQUEST_METHOD'] . ", orderId=" . ($orderId ?? 'null'));
        error_log("verifyPayment: REQUEST_URI=" . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        
        // Si es GET con orderId (desde QR scan), manejar diferente
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $orderId) {
            return $this->handleQRScan($orderId);
        }
        
        // Verificar método POST para AJAX
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("verifyPayment: Method not allowed: " . $_SERVER['REQUEST_METHOD']);
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        try {
            // Obtener datos JSON del body
            $rawInput = file_get_contents('php://input');
            error_log("verifyPayment: Raw input: " . $rawInput);
            
            $input = json_decode($rawInput, true);
            error_log("verifyPayment: Decoded input: " . json_encode($input));
            
            $orderNumber = $input['order_number'] ?? $input['order_id'] ?? null;
            $paymentToken = $input['payment_token'] ?? null;
            
            error_log("verifyPayment: orderNumber='$orderNumber', paymentToken='$paymentToken'");
            
            if (!$orderNumber) {
                $this->json(['success' => false, 'message' => 'Order number requerido'], 400);
                return;
            }
            
            if (!$paymentToken) {
                error_log("verifyPayment: Payment token missing, but continuing with order verification");
                // Continuar sin token para permitir verificación básica
            }
            
            // Verificar token solo si existe
            if ($paymentToken) {
                try {
                    if (!QRGenerator::verifyPaymentToken($orderNumber, $paymentToken)) {
                        $this->json(['success' => false, 'message' => 'Token inválido'], 403);
                        return;
                    }
                } catch (\Exception $e) {
                    error_log("verifyPayment: Token verification failed: " . $e->getMessage());
                    // Continuar sin token si hay error en la verificación
                }
            }
            
            // Obtener orden por número
            $orderModel = new Order();
            error_log("verifyPayment: Looking for order number: $orderNumber");
            $order = $orderModel->findByOrderNumber($orderNumber);
            
            if (!$order) {
                error_log("verifyPayment: Order not found for number: $orderNumber");
                $this->json(['success' => false, 'message' => "Orden #$orderNumber no encontrada"], 404);
                return;
            }
            
            error_log("verifyPayment: Found order with ID: " . $order['idcompra']);
            
            $orderId = $order['idcompra'];
            
            // Debug: Obtener todos los estados de la orden
            $allStatuses = $orderModel->debugOrderStatuses($orderId);
            error_log("verifyPayment: All statuses for order $orderId: " . json_encode($allStatuses));
            
            // Verificar si el pago ha sido completado usando el método isPaid
            if ($orderModel->isPaid($orderId)) {
                $this->json([
                    'success' => true,
                    'payment_status' => 'completed',
                    'message' => 'Pago completado',
                    'debug_statuses' => $allStatuses
                ]);
                return;
            }
            
            // Verificar si el QR ha expirado (más de 30 minutos)
            $created = strtotime($order['cofecha']);
            $now = time();
            $timeLimit = 30 * 60; // 30 minutos
            
            if (($now - $created) > $timeLimit) {
                $this->json([
                    'success' => false,
                    'expired' => true,
                    'message' => 'El código QR ha expirado'
                ]);
                return;
            }
            
            // Pago pendiente
            $this->json([
                'success' => false,
                'payment_status' => 'pending',
                'message' => 'Pago pendiente',
                'debug_statuses' => $allStatuses,
                'current_status' => $orderModel->getCurrentStatus($orderId)
            ]);
            
        } catch (\Exception $e) {
            error_log("[CheckoutFlowController] Error verificando estado de pago: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Procesar confirmación de pago por QR (desde URL externa)
     */
    public function confirmPayment($orderId)
    {
        // Verificar si es POST (desde JavaScript) o GET (desde URL)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Manejar confirmación desde JavaScript
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['confirm_payment']) || !$input['confirm_payment']) {
                    $this->json(['success' => false, 'message' => 'Confirmación requerida'], 400);
                    return;
                }
                
                // Obtener orden y marcar como pagada
                $orderModel = new Order();
                $order = $orderModel->findByOrderNumber($orderId);
                
                if (!$order) {
                    $this->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
                    return;
                }
                
                $result = $orderModel->markAsPaid($order['idcompra']);
                
                if ($result) {
                    $this->json([
                        'success' => true, 
                        'message' => 'Pago confirmado correctamente'
                    ]);
                } else {
                    $this->json(['success' => false, 'message' => 'Error al confirmar pago'], 500);
                }
                
            } catch (\Exception $e) {
                error_log("[CheckoutFlowController] Error confirmando pago: " . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Error interno'], 500);
            }
        } else {
            // Manejar GET request con token (método original)
            $token = $_GET['token'] ?? '';
            
            if (!QRGenerator::verifyPaymentToken($orderId, $token)) {
                $this->json(['success' => false, 'message' => 'Token inválido'], 403);
                return;
            }
            
            try {
                // Obtener orden y marcar como pagada
                $orderModel = new Order();
                $order = $orderModel->findByOrderNumber($orderId);
                
                if (!$order) {
                    $this->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
                    return;
                }
                
                $result = $orderModel->markAsPaid($order['idcompra']);
                
                if ($result) {
                    $this->json([
                        'success' => true, 
                        'message' => 'Pago verificado correctamente',
                        'redirect_url' => '/checkout/payment-success/' . $orderId
                    ]);
                } else {
                    $this->json(['success' => false, 'message' => 'Error al procesar pago'], 500);
                }
                
            } catch (\Exception $e) {
                error_log("[CheckoutFlowController] Error confirmando pago: " . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Error interno'], 500);
            }
        }
    }
    
    /**
     * Cancelar proceso de checkout
     */
    public function cancel()
    {
        Session::start();
        Session::remove('checkout_customer_data');
        Session::flash('info', 'Proceso de compra cancelado.');
        $this->redirect('/carrito');
    }
    
    /**
     * Validar datos del cliente
     */
    private function validateCustomerData($data): array
    {
        $errors = [];
        
        if (empty($data['full_name'])) {
            $errors[] = 'El nombre completo es requerido';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido es requerido';
        }
        
        if (empty($data['phone'])) {
            $errors[] = 'El teléfono es requerido';
        }
        
        if (empty($data['dni'])) {
            $errors[] = 'El DNI es requerido';
        }
        
        if (empty($data['address'])) {
            $errors[] = 'La dirección es requerida';
        }
        
        if (empty($data['city'])) {
            $errors[] = 'La ciudad es requerida';
        }
        
        if (empty($data['province'])) {
            $errors[] = 'La provincia es requerida';
        }
        
        if (!empty($errors)) {
            return [
                'valid' => false,
                'error' => implode(', ', $errors)
            ];
        }
        
        return [
            'valid' => true,
            'data' => [
                'full_name' => trim($data['full_name']),
                'email' => trim($data['email']),
                'phone' => trim($data['phone']),
                'dni' => trim($data['dni']),
                'address' => trim($data['address']),
                'city' => trim($data['city']),
                'province' => trim($data['province']),
                'postal_code' => trim($data['postal_code'] ?? ''),
                'notes' => trim($data['notes'] ?? '')
            ]
        ];
    }
    
    /**     * Valida el stock disponible sin reducirlo
     */
    private function validateCartStock($cartData): array
    {
        try {
            $productModel = new Product();
            
            foreach ($cartData as $itemId => $item) {
                error_log("[CheckoutFlow] Validating stock for item: " . json_encode($item));
                
                // Verificar stock disponible
                $product = $productModel->findById($item['product_id']);
                if (!$product || $product['procantstock'] < $item['quantity']) {
                    $productName = $item['name'] ?? $item['product_name'] ?? 'Producto desconocido';
                    return [
                        'success' => false,
                        'message' => "Stock insuficiente para {$productName}. Stock disponible: " . ($product['procantstock'] ?? 0)
                    ];
                }
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log("[CheckoutFlowController] Error validando stock: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error validando disponibilidad de productos'];
        }
    }

    /**     * Crear orden en base de datos
     */
    private function createOrder($cartData, $customerData): array
    {
        try {
            $orderModel = new Order();
            $productModel = new Product();
            
            // Calcular total
            $total = 0;
            $orderItems = [];
            
            foreach ($cartData as $itemId => $item) {
                error_log("[CheckoutFlow] Processing cart item: " . json_encode($item));
                
                // Verificar stock disponible
                $product = $productModel->findById($item['product_id']);
                if (!$product || $product['procantstock'] < $item['quantity']) {
                    $productName = $item['name'] ?? $item['product_name'] ?? 'Producto desconocido';
                    return [
                        'success' => false,
                        'message' => "Stock insuficiente para {$productName}"
                    ];
                }
                
                $itemTotal = $item['price'] * $item['quantity'];
                $total += $itemTotal;
                
                $orderItems[] = [
                    'idproducto' => $item['product_id'],
                    'cantidad' => $item['quantity'],
                    'precio' => $item['price'],
                    'size' => $item['size'] ?? 'M',
                    'color' => $item['color'] ?? 'default'
                ];
            }
            
            // Crear la orden
            $userId = Session::get('user_id');
            
            $orderId = $orderModel->create($userId, $orderItems);
            
            if ($orderId) {
                // Actualizar stock de productos
                foreach ($orderItems as $item) {
                    $productModel->decreaseStock($item['idproducto'], $item['cantidad']);
                }
                
                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'total' => $total
                ];
            }
            
            return ['success' => false, 'message' => 'Error al crear la orden'];
            
        } catch (\Exception $e) {
            error_log("[CheckoutFlowController] Error creando orden: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al crear la orden'];
        }
    }
    
    /**
     * Manejar escaneo de QR (GET request)
     */
    private function handleQRScan(string $orderId): void
    {
        try {
            // Obtener token de la query string
            $token = $_GET['token'] ?? null;
            
            if (!$token) {
                $this->showPaymentError('Token de seguridad faltante');
                return;
            }
            
            // Verificar token
            if (!QRGenerator::verifyPaymentToken($orderId, $token)) {
                $this->showPaymentError('Token de seguridad inválido');
                return;
            }
            
            // Obtener orden
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);
            
            if (!$order) {
                $this->showPaymentError('Orden no encontrada');
                return;
            }
            
            // Verificar si ya está pagado
            if ($orderModel->isPaid($orderId)) {
                $this->showPaymentSuccess($order, 'Pago ya completado');
                return;
            }
            
            // Verificar si ha expirado (más de 30 minutos)
            $created = strtotime($order['cofecha']);
            $now = time();
            $timeLimit = 30 * 60; // 30 minutos
            
            if (($now - $created) > $timeLimit) {
                $this->showPaymentError('El código QR ha expirado. Por favor, genera uno nuevo.');
                return;
            }
            
            // Mostrar página de confirmación de pago
            $this->showPaymentConfirmation($order);
            
        } catch (\Exception $e) {
            error_log("[CheckoutFlow] Error in handleQRScan: " . $e->getMessage());
            $this->showPaymentError('Error interno del servidor');
        }
    }
    
    /**
     * Mostrar página de confirmación de pago
     */
    private function showPaymentConfirmation(array $order): void
    {
        $data = [
            'title' => 'Confirmar Pago - Amarena Store',
            'pageCss' => 'checkout-flow',
            'order' => $order,
            'step' => 5
        ];
        
        $this->view('vistas.checkout.payment_confirmation', $data);
    }
    
    /**
     * Mostrar página de éxito de pago
     */
    private function showPaymentSuccess(array $order, string $message = 'Pago completado exitosamente'): void
    {
        $data = [
            'title' => 'Pago Exitoso - Amarena Store',
            'pageCss' => 'checkout-flow',
            'order' => $order,
            'message' => $message,
            'step' => 6
        ];
        
        $this->view('vistas.checkout.payment_success', $data);
    }
    
    /**
     * Mostrar página de error de pago
     */
    private function showPaymentError(string $message): void
    {
        $data = [
            'title' => 'Error de Pago - Amarena Store',
            'pageCss' => 'checkout-flow',
            'error_message' => $message,
            'step' => 0
        ];
        
        $this->view('vistas.checkout.payment_error', $data);
    }
    
    /**
     * Procesar pago desde QR (ruta pública simplificada)
     */
    public function processPayment($orderId): void
    {
        try {
            error_log("[ProcessPayment] Starting for order ID: " . $orderId);
            
            // Obtener token de la query string
            $token = $_GET['token'] ?? null;
            // Token validation
            
            if (!$token) {
                    error_log("[ProcessPayment] No token provided");
                $this->showSimplePaymentPage($orderId, 'Token de seguridad faltante', 'error');
                return;
            }
            
            // Verificar token
            $tokenValid = QRGenerator::verifyPaymentToken($orderId, $token);
            // Verify token validity
            
            if (!$tokenValid) {
                error_log("[ProcessPayment] Invalid token");
                $this->showSimplePaymentPage($orderId, 'Código QR inválido o expirado', 'error');
                return;
            }
            
            // Verificar si es un número de orden (crear orden real si no existe)
            $orderModel = new Order();
            $order = $orderModel->findByOrderNumber($orderId);
            
            if (!$order) {
                error_log("[ProcessPayment] Order not found, creating from session: $orderId");
                
                // Crear la orden real desde datos de sesión
                Session::start();
                $tempOrderData = Session::get('checkout_temp_order');
                
                if (!$tempOrderData || $tempOrderData['order_number'] !== $orderId) {
                    error_log("[ProcessPayment] Temp order data not found or mismatch");
                    $this->showSimplePaymentPage($orderId, 'Sesión expirada. Genera un nuevo código QR.', 'error');
                    return;
                }
                
                // Crear la orden real ahora
                $orderData = $this->createOrder($tempOrderData['cart_data'], $tempOrderData['customer_data']);
                
                if (!$orderData['success']) {
                    error_log("[ProcessPayment] Failed to create real order: " . $orderData['message']);
                    $this->showSimplePaymentPage($orderId, $orderData['message'], 'error');
                    return;
                }
                
                $realOrderId = $orderData['order_id'];
                error_log("[ProcessPayment] Real order created: $realOrderId");
                
                // Obtener la orden recién creada
                $order = $orderModel->findById($realOrderId);
                
                // El orderId (número de orden) se mantiene igual
                
                // Limpiar el carrito ahora que se creó la orden
                Session::remove('cart');
                Session::remove('checkout_temp_order');
                
            } else {
                // La orden ya existe
                error_log("[ProcessPayment] Processing existing order: $orderId");
            }
            
            // Verificar si ya está pagado (usar ID de la BD)
            $realOrderId = $order['idcompra'];
            $isPaid = $orderModel->isPaid($realOrderId);
            
            if ($isPaid) {
                $this->showSimplePaymentPage($orderId, 'Pago completado exitosamente. ¡Gracias por tu compra!', 'success', $order);
                return;
            }
            
            // Verificar si ha expirado (más de 30 minutos)
            $created = strtotime($order['cofecha']);
            $now = time();
            $timeLimit = 30 * 60; // 30 minutos
            // Check expiration time
            
            if (($now - $created) > $timeLimit) {
                $this->showSimplePaymentPage($orderId, 'El código QR ha expirado. Contacta al vendedor para generar uno nuevo.', 'error');
                return;
            }
            
            // Mostrar página de pago
            $this->showSimplePaymentPage($orderId, null, 'pending', $order);
            
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../../debug_payment.log', "[CheckoutFlow] Error in processPayment: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/../../debug_payment.log', "[CheckoutFlow] Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            $this->showSimplePaymentPage($orderId, 'Error interno del servidor: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Mostrar página simple de pago
     */
    private function showSimplePaymentPage($orderId, $message = null, $status = 'pending', $order = null): void
    {
        // Si no se pasó la orden, intentar obtenerla de la base de datos
        if (!$order && $orderId) {
            try {
                $orderModel = new Order();
                $order = $orderModel->findById($orderId);
                if ($order) {
                    error_log("showSimplePaymentPage: Retrieved order data for ID $orderId, total: " . ($order['cototal'] ?? 'N/A'));
                } else {
                    error_log("showSimplePaymentPage: No order found for ID $orderId");
                }
            } catch (\Exception $e) {
                error_log("showSimplePaymentPage: Error retrieving order: " . $e->getMessage());
            }
        }
        
        $data = [
            'title' => 'Procesar Pago - Amarena Store',
            'pageCss' => 'checkout-flow',
            'order_id' => $orderId,
            'order' => $order,
            'message' => $message,
            'status' => $status
        ];
        
        error_log("showSimplePaymentPage: Final data - Order total: " . (isset($order['cototal']) ? $order['cototal'] : 'NULL'));
        
        $this->view('vistas.checkout.simple_payment', $data);
    }
    
    /**
     * Mostrar página de éxito de pago (ruta pública)
     */
    public function paymentSuccess($orderId): void
    {
        try {
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);
            
            if (!$order) {
                $this->showPaymentError('Orden no encontrada');
                return;
            }
            
            $this->showPaymentSuccess($order, 'Pago completado exitosamente');
            
        } catch (\Exception $e) {
            error_log("[CheckoutFlow] Error in paymentSuccess: " . $e->getMessage());
            $this->showPaymentError('Error interno del servidor');
        }
    }
    
    /**
     * Debug: Verificar datos completos de una orden
     */
    public function debugOrderData($orderId)
    {
        try {
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);
            
            $this->json([
                'order_id' => $orderId,
                'order_data' => $order,
                'fields' => $order ? array_keys($order) : [],
                'cototal_field' => $order['cototal'] ?? 'NOT_FOUND'
            ]);
            
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Debug: Verificar estados de una orden
     */
    public function debugOrderStates($orderId)
    {
        try {
            $orderModel = new Order();
            
            // Obtener todos los estados disponibles
            $availableStates = $orderModel->getAvailableStatuses();
            
            // Obtener estados de la orden específica
            $orderStates = $orderModel->debugOrderStatuses($orderId);
            
            // Obtener estado actual
            $currentStatus = $orderModel->getCurrentStatus($orderId);
            
            // Verificar si está pagado
            $isPaid = $orderModel->isPaid($orderId);
            
            $this->json([
                'order_id' => $orderId,
                'available_states' => $availableStates,
                'order_states' => $orderStates,
                'current_status' => $currentStatus,
                'is_paid' => $isPaid
            ]);
            
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Test endpoint para verificar ruteo
     */
    public function testVerify()
    {
        $this->json([
            'success' => true,
            'message' => 'Ruta funcionando correctamente',
            'method' => $_SERVER['REQUEST_METHOD'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
}