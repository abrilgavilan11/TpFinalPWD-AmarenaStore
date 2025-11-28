<?php
namespace App\Controllers;

use App\Models\Menu;
use App\Utils\Auth;
use App\Utils\Session;

class ManagementMenuController extends BaseController
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    public function index()
    {
        $menuModel = new Menu();
        $menus = $menuModel->getAll();
        $this->view('vistas.admin.menus', [
            'title' => 'Gestionar Menús',
            'menus' => $menus
        ]);
    }
    public function create()
    {
        $menuModel = new Menu();
        $menusPadre = $menuModel->getAll();
        $this->view('vistas.admin.menus_form', [
            'title' => 'Nuevo Menú',
            'menusPadre' => $menusPadre
        ]);
    }

    public function store()
    {
        $menuModel = new Menu();
        $data = [
            'menombre' => trim($_POST['menombre'] ?? ''),
            'medescripcion' => trim($_POST['medescripcion'] ?? ''),
            'meurl' => trim($_POST['meurl'] ?? ''),
            'idpadre' => $_POST['idpadre'] !== '' ? intval($_POST['idpadre']) : null,
            'meorden' => intval($_POST['meorden'] ?? 0)
        ];
        if (empty($data['menombre'])) {
            Session::flash('error', 'El nombre es obligatorio.');
            $this->redirect('/management/menus/create');
            return;
        }
        $ok = $menuModel->create($data);
        if ($ok) {
            // Obtener el último id insertado
            $lastMenu = $menuModel->getLastInserted();
            $idMenu = $lastMenu ? $lastMenu['idmenu'] : null;
            if ($idMenu) {
                // Asignar rol automáticamente según el tipo de menú
                $nombre = strtolower($data['menombre']);
                $idRol = null;
                if (strpos($nombre, 'admin') !== false) {
                    $idRol = 1; // Admin
                } elseif (strpos($nombre, 'cliente') !== false) {
                    $idRol = 2; // Cliente
                }
                if ($idRol) {
                    $menuModel->assignToRole($idMenu, $idRol);
                }
            }
            Session::flash('success', 'Menú creado correctamente.');
        } else {
            Session::flash('error', 'No se pudo crear el menú.');
        }
        $this->redirect('/management/menus');
    }

    public function edit($id)
    {
        $menuModel = new Menu();
        $menu = $menuModel->findById($id);
        if (!$menu) {
            Session::flash('error', 'Menú no encontrado.');
            $this->redirect('/management/menus');
            return;
        }
        $menusPadre = $menuModel->getAll();
        $this->view('vistas.admin.menus_form', [
            'title' => 'Editar Menú',
            'menu' => $menu,
            'menusPadre' => $menusPadre
        ]);
    }

    public function update($id)
    {
        $menuModel = new Menu();
        $menu = $menuModel->findById($id);
        if (!$menu) {
            Session::flash('error', 'Menú no encontrado.');
            $this->redirect('/management/menus');
            return;
        }
        $data = [
            'menombre' => trim($_POST['menombre'] ?? ''),
            'medescripcion' => trim($_POST['medescripcion'] ?? ''),
            'meurl' => trim($_POST['meurl'] ?? ''),
            'idpadre' => $_POST['idpadre'] !== '' ? intval($_POST['idpadre']) : null,
            'meorden' => intval($_POST['meorden'] ?? 0)
        ];
        $ok = $menuModel->update($id, $data);
        if ($ok) {
            // Asignar rol automáticamente si corresponde
            $nombre = strtolower($data['menombre']);
            $idRol = null;
            if (strpos($nombre, 'admin') !== false) {
                $idRol = 1;
            } elseif (strpos($nombre, 'cliente') !== false) {
                $idRol = 2;
            }
            if ($idRol) {
                $menuModel->assignToRole($id, $idRol);
            }
            Session::flash('success', 'Menú actualizado correctamente.');
        } else {
            Session::flash('error', 'No se pudo actualizar el menú.');
        }
        $this->redirect('/management/menus');
    }

    public function delete($id)
    {
        $menuModel = new Menu();
        $ok = $menuModel->delete($id);
        if ($ok) {
            Session::flash('success', 'Menú eliminado correctamente.');
        } else {
            Session::flash('error', 'No se pudo eliminar el menú.');
        }
        $this->redirect('/management/menus');
    }
}
