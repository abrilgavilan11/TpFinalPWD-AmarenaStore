<?php
namespace App\Controllers;

use App\Models\Menu;
use App\Utils\Auth;
use App\Utils\Session;

class AdminMenuController extends BaseController
{
    // Cambia la visibilidad del menú en la navbar vía AJAX
    public function toggleNavbar($id)
    {
        header('Content-Type: application/json');
        $menuModel = new \App\Models\Menu();
        $menu = $menuModel->findById($id);
        if (!$menu) {
            echo json_encode(['success' => false, 'message' => 'Menú no encontrado.']);
            return;
        }
        $newStatus = empty($menu['visible_navbar']) || $menu['visible_navbar'] ? 0 : 1;
        $ok = $menuModel->update($id, ['visible_navbar' => $newStatus]);
        if ($ok) {
            echo json_encode(['success' => true, 'new_status' => $newStatus]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar.']);
        }
    }

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
        $this->view('vistas.admin.menu_form', [
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
            $this->redirect('/admin/menus/create');
            return;
        }
        $ok = $menuModel->create($data);
        if ($ok) {
            Session::flash('success', 'Menú creado correctamente.');
        } else {
            Session::flash('error', 'No se pudo crear el menú.');
        }
        $this->redirect('/admin/menus');
    }

    public function edit($id)
    {
        $menuModel = new Menu();
        $menu = $menuModel->findById($id);
        if (!$menu) {
            Session::flash('error', 'Menú no encontrado.');
            $this->redirect('/admin/menus');
            return;
        }
        $menusPadre = $menuModel->getAll();
        $this->view('vistas.admin.menu_edit', [
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
            $this->redirect('/admin/menus');
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
            Session::flash('success', 'Menú actualizado correctamente.');
        } else {
            Session::flash('error', 'No se pudo actualizar el menú.');
        }
        $this->redirect('/admin/menus');
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
        $this->redirect('/admin/menus');
    }
}
