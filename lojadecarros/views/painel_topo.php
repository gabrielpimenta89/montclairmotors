<?php
// Cabeçalho e menu lateral do painel. Antes de incluir, defina $ativo: 'inicio', 'carros', 'entradas', 'vendas' ou 'relatorios'.
$ativo  = $ativo ?? 'inicio';
$h      = $h ?? fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$nome   = $_SESSION['nome'] ?? 'Usuário';
$perfil = $_SESSION['perfil'] ?? 'vendedor';
$inicial = function_exists('mb_substr') ? mb_strtoupper(mb_substr($nome, 0, 1)) : strtoupper(substr($nome, 0, 1));

$ic = [
    'inicio'     => '<path d="M3 11 12 3l9 8"></path><path d="M5 10v10h14V10"></path>',
    'carros'     => '<path d="M5 16h14l-1.5-5h-11z"></path><circle cx="8" cy="17.5" r="1.5"></circle><circle cx="16" cy="17.5" r="1.5"></circle>',
    'entradas'   => '<path d="M12 3v12"></path><path d="m7 10 5 5 5-5"></path><path d="M4 20h16"></path>',
    'vendas'     => '<circle cx="9" cy="20" r="1.5"></circle><circle cx="18" cy="20" r="1.5"></circle><path d="M3 3h2l2.6 12.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.5L21 8H6"></path>',
    'relatorios' => '<path d="M5 20v-9"></path><path d="M12 20V4"></path><path d="M19 20v-6"></path><path d="M3 20h18"></path>',
    'interessados' => '<circle cx="9" cy="8" r="3.5"></circle><path d="M2.5 20a6.5 6.5 0 0 1 13 0"></path><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8"></path><path d="M18 14.2a6.5 6.5 0 0 1 3.5 5.8"></path>',
    'sair'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="m16 17 5-5-5-5"></path><path d="M21 12H9"></path>',
];
$ico = fn($k) => '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">' . $ic[$k] . '</svg>';
$rotas = ['inicio' => ['auth', 'dashboard'], 'carros' => ['produto', 'index'], 'entradas' => ['entrada', 'index'], 'vendas' => ['venda', 'index'], 'interessados' => ['interessado', 'index'], 'relatorios' => ['relatorio', 'index']];
$url = fn($k) => 'index.php?controller=' . $rotas[$k][0] . '&amp;action=' . $rotas[$k][1];
$nomes = ['inicio' => 'Início', 'carros' => 'Carros', 'entradas' => 'Entradas', 'vendas' => 'Vendas', 'interessados' => 'Interessados', 'relatorios' => 'Relatórios'];
?>
<header class="topo">
    <a class="logo" href="<?= $url('inicio') ?>" aria-label="Montclair Motors"><img src="public/assets/uploads/logo2.png" alt="Montclair Motors"></a>
    <nav class="abas" aria-label="Atalhos principais">
        <?php foreach (['inicio' => 'Visão geral', 'vendas' => 'Vendas', 'relatorios' => 'Relatórios'] as $k => $t): ?>
            <a href="<?= $url($k) ?>"<?= $k === $ativo ? ' class="on" aria-current="page"' : '' ?>><?= $t ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="usuario">
        <span class="avatar" aria-hidden="true"><?= $h($inicial) ?></span>
        <span class="u-txt"><strong><?= $h($nome) ?></strong><small><?= $h($perfil) ?></small></span>
    </div>
</header>

<nav class="lateral" aria-label="Módulos do sistema">
    <div class="lateral-grupo">
        <?php foreach ($rotas as $k => $_): ?>
            <a href="<?= $url($k) ?>" title="<?= $nomes[$k] ?>" aria-label="<?= $nomes[$k] ?>"<?= $k === $ativo ? ' class="on" aria-current="page"' : '' ?>><?= $ico($k) ?></a>
        <?php endforeach; ?>
    </div>
    <a href="index.php?controller=auth&amp;action=logout" title="Sair" aria-label="Sair"><?= $ico('sair') ?></a>
</nav>