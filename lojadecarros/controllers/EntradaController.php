<?php
require_once __DIR__ . '/../models/entrada.php';

class EntradaController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?controller=auth&action=form');
            exit;
        }

        $entradas = [];
        $erro = null;
        try {
            $entradas = (new Entrada())->listar();
        } catch (Throwable $e) {
            error_log('Entradas: ' . $e->getMessage());
            $erro = 'Não foi possível carregar as entradas. Confira se o script entradas.sql foi executado.';
        }

        require __DIR__ . '/../views/entradas.php';
    }
}