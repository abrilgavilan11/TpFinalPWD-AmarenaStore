<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Utils\Session;
use App\Utils\Auth;
use App\Utils\EmailSender;

class CustomerDashboardController extends BaseController
{
    public function __construct()
    {
        Auth::requireLogin();
    }

    /**
     * Dashboard principal del cliente
     */
    public function index()
    {
        $userId = Auth::getUserId();
        $userModel = new User();
        $orderModel = new Order();
        
        // Obtener datos del usuario
        $user = $userModel->findById($userId);
        
        // Obtener estadísticas de órdenes
        $orders = $orderModel->findByUserId($userId);
        $totalOrders = count($orders);
        $completedOrders = count(array_filter($orders, function($order) {
            return in_array($order['estado_actual'], ['entregada']);
        }));
        $pendingOrders = count(array_filter($orders, function($order) {
            return in_array($order['estado_actual'], ['iniciada', 'aceptada', 'enviada']);
        }));
        
        // Calcular total gastado
        $totalSpent = 0;
        foreach ($orders as $order) {
            $items = $orderModel->getItems($order['idcompra']);
            foreach ($items as $item) {
                $totalSpent += $item['cicantidad'] * $item['ciprecio'];
            }
        }
        
        $this->view('vistas.customer.dashboard', [
            'title' => 'Mi Panel - Amarena Store',
            'user' => $user,
            'orders' => $orders,
            'stats' => [
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'pending_orders' => $pendingOrders,
                'total_spent' => $totalSpent
            ]
        ]);
    }

    /**
     * Mostrar órdenes del cliente
     */
    public function orders()
    {
        $userId = Auth::getUserId();
        $orderModel = new Order();
        
        $orders = $orderModel->findByUserId($userId);
        
        $this->view('vistas.customer.orders', [
            'title' => 'Mis Órdenes - Amarena Store',
            'orders' => $orders
        ]);
    }

    /**
     * Mostrar detalles de una orden específica
     */
    public function orderDetail($orderId)
    {
        $userId = Auth::getUserId();
        $orderModel = new Order();
        
        $order = $orderModel->findById($orderId);
        
        // Verificar que la orden pertenezca al usuario
        if (!$order || $order['idusuario'] != $userId) {
            Session::flash('error', 'No tienes permiso para ver esta orden.');
            $this->redirect('/customer/dashboard');
            return;
        }
        
        $items = $orderModel->getItems($orderId);
        $rawStatusHistory = $orderModel->getStatusHistory($orderId);
        // Mapear los campos para la vista
        $statusHistory = array_map(function($row) {
            return [
                'estado' => $row['cetdescripcion'] ?? 'desconocido',
                'fecha' => $row['cefechaini'] ?? null,
                'comentario' => $row['cecomentario'] ?? ''
            ];
        }, $rawStatusHistory);
        $this->view('vistas.customer.order_detail', [
            'title' => 'Orden #' . $orderId . ' - Amarena Store',
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory
        ]);
    }

    /**
     * Mostrar formulario de edición del perfil
     */
    public function profile()
    {
        $userId = Auth::getUserId();
        $userModel = new User();
        
        $user = $userModel->findById($userId);
        
        $this->view('vistas.customer.profile', [
            'title' => 'Mi Perfil - Amarena Store',
            'user' => $user
        ]);
    }

    /**
     * Actualizar perfil del usuario
     */
    public function updateProfile()
    {
        try {
            $userId = Auth::getUserId();
            $userModel = new User();
            
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            // Validaciones
            if (empty($name) || empty($email)) {
                Session::flash('error', 'Nombre y email son obligatorios.');
                $this->redirect('/customer/profile');
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::flash('error', 'El formato del email no es válido.');
                $this->redirect('/customer/profile');
                return;
            }
            
            // Verificar que el email no esté en uso por otro usuario
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['idusuario'] != $userId) {
                Session::flash('error', 'Ese email ya está en uso por otro usuario.');
                $this->redirect('/customer/profile');
                return;
            }
            
            // Actualizar datos
            if ($userModel->updateProfile($userId, $name, $email, $phone, $address)) {
                Session::flash('success', 'Perfil actualizado correctamente.');
            } else {
                Session::flash('error', 'Error al actualizar el perfil.');
            }
            
        } catch (\Exception $e) {
            Session::flash('error', 'Error interno del servidor.');
        }
        
        $this->redirect('/customer/profile');
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function changePassword()
    {
        try {
            $userId = Auth::getUserId();
            $userModel = new User();
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validaciones
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                Session::flash('error', 'Todos los campos son obligatorios.');
                $this->redirect('/customer/profile');
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                Session::flash('error', 'Las contraseñas no coinciden.');
                $this->redirect('/customer/profile');
                return;
            }
            
            if (strlen($newPassword) < 6) {
                Session::flash('error', 'La contraseña debe tener al menos 6 caracteres.');
                $this->redirect('/customer/profile');
                return;
            }
            
            // Verificar contraseña actual
            $user = $userModel->findById($userId);
            if (!password_verify($currentPassword, $user['uspassword'])) {
                Session::flash('error', 'La contraseña actual es incorrecta.');
                $this->redirect('/customer/profile');
                return;
            }
            
            // Actualizar contraseña
            if ($userModel->updatePassword($userId, $newPassword)) {
                // Enviar email de confirmación
                $emailSender = new EmailSender();
                $emailSender->sendPasswordChangeNotification($user['usmail'], $user['usnombre']);
                
                Session::flash('success', 'Contraseña actualizada correctamente.');
            } else {
                Session::flash('error', 'Error al actualizar la contraseña.');
            }
            
        } catch (\Exception $e) {
            Session::flash('error', 'Error interno del servidor.');
        }
        
        $this->redirect('/customer/profile');
    }

    /**
     * Solicitar cambio de contraseña por email
     */
    public function requestPasswordReset()
    {
        try {
            $userId = Auth::getUserId();
            $userModel = new User();
            $user = $userModel->findById($userId);
            
            // Generar token de recuperación
            $resetToken = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            if ($userModel->createPasswordResetToken($userId, $resetToken, $expiry)) {
                // Enviar email con enlace de recuperación
                $emailSender = new EmailSender();
                $resetLink = BASE_URL . '/reset-password?token=' . $resetToken;
                
                $emailSender->sendPasswordResetEmail($user['usmail'], $user['usnombre'], $resetLink);
                
                $this->json(['success' => true, 'message' => 'Se ha enviado un enlace de recuperación a tu email.']);
            } else {
                $this->json(['success' => false, 'message' => 'Error al procesar la solicitud.']);
            }
            
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }
}