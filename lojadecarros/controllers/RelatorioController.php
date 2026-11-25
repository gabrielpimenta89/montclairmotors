<?php
require_once __DIR__ . '/../models/relatorio.php';

class RelatorioController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?controller=auth&action=form');
            exit;
        }

        // período escolhido (padrão: últimos 12 meses)
        $hoje = date('Y-m-d');
        $ok = fn($d) => is_string($d) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m) && checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
        $de  = $_GET['de'] ?? '';
        $ate = $_GET['ate'] ?? '';
        if (!$ok($de) || !$ok($ate)) {
            $de  = date('Y-m-d', strtotime('first day of -11 months'));
            $ate = $hoje;
        }
        if ($de > $ate) [$de, $ate] = [$ate, $de];

        $atalhos = [
            'Este mês'         => [date('Y-m-01'), $hoje],
            'Últimos 3 meses'  => [date('Y-m-d', strtotime('first day of -2 months')), $hoje],
            'Últimos 12 meses' => [date('Y-m-d', strtotime('first day of -11 months')), $hoje],
            'Tudo'             => ['2000-01-01', $hoje],
        ];

        $dados = [];
        $erro  = null;
        try {
            $dados = (new Relatorio())->gerar($de, $ate);
        } catch (Throwable $e) {
            error_log('Relatórios: ' . $e->getMessage());
            $erro = 'Não foi possível gerar os relatórios. Confira a conexão com o banco no arquivo models/relatorio.php.';
        }

        require __DIR__ . '/../views/relatorios.php';
    }
}