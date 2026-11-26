<?php
require_once __DIR__ . '/../models/interessado.php';

class InteressadoController
{
    private function exigirLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?controller=auth&action=form');
            exit;
        }
    }

    public function index()
    {
        $this->exigirLogin();
        $_SESSION['csrf_painel'] = $_SESSION['csrf_painel'] ?? bin2hex(random_bytes(16));
        $token = $_SESSION['csrf_painel'];

        $status = $_GET['status'] ?? '';
        if (!isset(Interessado::STATUS[$status])) $status = '';

        $lista = [];
        $contagem = [];
        $erro = null;
        try {
            $m = new Interessado();
            $lista = $m->listar($status);
            $contagem = $m->contagem();
        } catch (Throwable $e) {
            error_log('Interessados: ' . $e->getMessage());
            $erro = 'Não foi possível carregar os interessados. Confira se o script interessados.sql foi executado.';
        }

        require __DIR__ . '/../views/interessados.php';
    }

    public function atualizar()
    {
        $this->exigirLogin();
        $voltar = $_POST['voltar'] ?? '';
        if (!isset(Interessado::STATUS[$voltar])) $voltar = '';
        $destino = 'index.php?controller=interessado&action=index' . ($voltar ? '&status=' . $voltar : '');

        $tokenOk = isset($_POST['csrf'], $_SESSION['csrf_painel']) && hash_equals($_SESSION['csrf_painel'], (string)$_POST['csrf']);
        $id     = (int)($_POST['id_interesse'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenOk && $id > 0 && isset(Interessado::STATUS[$status])) {
            $obs = function_exists('mb_substr') ? mb_substr(trim((string)($_POST['observacao'] ?? '')), 0, 255) : substr(trim((string)($_POST['observacao'] ?? '')), 0, 255);
            try {
                (new Interessado())->atualizar($id, $status, $obs);
            } catch (Throwable $e) {
                error_log('Interessados: ' . $e->getMessage());
            }
        }
        header('Location: ' . $destino);
        exit;
    }
}
