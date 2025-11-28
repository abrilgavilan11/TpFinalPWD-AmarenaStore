<?php
namespace App\Controllers;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\Session;

class AdminClientesController extends BaseController
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    public function index()
    {
        $userModel = new User();
        $clientes = $userModel->getAll();
        require_once VIEWS_PATH . '/vistas/admin/clients.php';
    }
    public function create()
    {
        $this->view('vistas.admin.cliente_form', [
            'title' => 'Nuevo Cliente'
        ]);
    }

    public function store()
    {
        $userModel = new User();
        $data = [
            'usnombre' => trim($_POST['usnombre'] ?? ''),
            'email' => trim($_POST['usmail'] ?? ''),
            'password' => $_POST['uspass'] ?? '',
            'ustelefono' => trim($_POST['ustelefono'] ?? ''),
            'usdireccion' => trim($_POST['usdireccion'] ?? '')
        ];
        if (empty($data['usnombre']) || empty($data['email']) || empty($data['password'])) {
            Session::flash('error', 'Nombre, email y contraseña son obligatorios.');
            $this->redirect('/management/clientes/create');
            return;
        }
        $userId = $userModel->create($data);
        if ($userId) {
            Session::flash('success', 'Cliente creado correctamente.');
        } else {
            Session::flash('error', 'No se pudo crear el cliente.');
        }
        $this->redirect('/management/clientes');
    }

    public function edit($id)
    {
        $userModel = new User();
        $cliente = $userModel->findById($id);
        require_once VIEWS_PATH . '/vistas/admin/clients_edit.php';
    }

    public function update($id)
    {
        $userModel = new User();
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;
        $userModel->updateProfile($id, $nombre, $email);
        $userModel->setEstado($id, $estado);
        header('Location: ' . BASE_URL . '/management/clientes');
        exit;
    }

    public function delete($id)
    {
        $userModel = new User();
        $userModel->disable($id);
        header('Location: ' . BASE_URL . '/management/clientes');
        exit;
    }
}
