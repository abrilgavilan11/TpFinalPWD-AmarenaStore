<?php

namespace App\Controllers;

use App\Views\Actions\Autenticacion\VerificarLogin;
use App\Views\Actions\Autenticacion\RegistrarUsuario;
use App\Views\Actions\Autenticacion\CerrarSesion;
use App\Models\User;
use App\Utils\Session;
use App\Utils\Email;

class AuthController extends BaseController
{
    /**
     * Login usando Action VerificarLogin
     */
    public function login()
    {
        try {
            $verificarAction = new VerificarLogin();
            $result = $verificarAction->execute(
                $_POST['email'] ?? '',
                $_POST['password'] ?? ''
            );
            
            if ($result['success']) {
                Session::flash('success', $result['message']);
                $this->redirect($result['redirect']);
            } else {
                Session::flash('login_error', $result['message']);
                $this->redirect('/?login_error=1');
            }
        } catch (\Exception $e) {
            error_log("[AuthController] Error en login: " . $e->getMessage());
            Session::flash('login_error', 'Error interno del servidor');
            $this->redirect('/?login_error=1');
        }
    }

    /**
     * Registro de nuevo usuario
     */
    public function register()
    {
        $name = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordConfirm = trim($_POST['password_confirm'] ?? '');
        
        // Validaciones
        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('register_error', 'Todos los campos son requeridos.');
            $this->redirect('/?register=1');
            return;
        }
        
        if ($password !== $passwordConfirm) {
            Session::flash('register_error', 'Las contraseñas no coinciden.');
            $this->redirect('/?register=1');
            return;
        }
        
        if (strlen($password) < 8) {
            Session::flash('register_error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redirect('/?register=1');
            return;
        }
        
        // Crear el usuario
        $userModel = new User();
        $userId = $userModel->create([
            'usnombre' => $name,
            'email' => $email,
            'password' => $password
        ]);
        
        if ($userId) {
            // Asignar rol de Cliente por defecto
            $userModel->assignRole($userId, 2); // 2 = Cliente
            
            $emailer = new Email();
            $emailer->sendWelcomeEmail($email, $name);
            
            Session::flash('success', 'Usuario registrado exitosamente. Revisa tu correo y ahora puedes iniciar sesión.');
            $this->redirect('/?login=1');
        } else {
            Session::flash('register_error', 'Error al registrar el usuario. El email ya está en uso.');
            $this->redirect('/?register=1');
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        try {
            $cerrarAction = new CerrarSesion();
            $result = $cerrarAction->execute();
            
            Session::flash('success', $result['message']);
            $this->redirect('/');
        } catch (\Exception $e) {
            error_log("[AuthController] Error en logout: " . $e->getMessage());
            Session::flash('error', 'Error al cerrar sesión');
            $this->redirect('/');
        }
    }

    /**
     * Muestra el formulario de login
     */
    public function loginForm()
    {
        $this->view('vistas.autenticacion.login', [
            'title' => 'Iniciar Sesión'
        ]);
    }

    /**
     * Muestra el formulario de registro
     */
    public function registerForm()
    {
        $this->view('vistas.autenticacion.register', [
            'title' => 'Crear Cuenta'
        ]);
    }
}
