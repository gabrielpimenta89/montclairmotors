<?php
$nome   = $_SESSION['nome'] ?? 'Usuário';
$perfil = $_SESSION['perfil'] ?? 'vendedor';

// Números dos cards. Se algo falhar, mostra 0 e o dashboard continua abrindo.
$resumo = ['vendas_mes' => 0, 'produtos' => 0];
$modeloVenda = __DIR__ . '/../models/venda.php';
if (is_file($modeloVenda)) {
    try {
        require_once $modeloVenda;
        $resumo = (new Venda())->resumoDashboard();
    } catch (Throwable $e) {
        error_log('Dashboard: ' . $e->getMessage());
    }
}
$entradasMes  = 0; // será alimentado quando o módulo de entradas estiver pronto
$novosInteressados = 0;
try {
    require_once __DIR__ . '/../models/interessado.php';
    $novosInteressados = (new Interessado())->totalNovos();
} catch (Throwable $e) {
    error_log('Dashboard (interessados): ' . $e->getMessage());
}

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$inicial = function_exists('mb_substr') ? mb_strtoupper(mb_substr($nome, 0, 1)) : strtoupper(substr($nome, 0, 1));

$ic = [
    'inicio'     => '<path d="M3 11 12 3l9 8"></path><path d="M5 10v10h14V10"></path>',
    'carros'     => '<path d="M5 16h14l-1.5-5h-11z"></path><circle cx="8" cy="17.5" r="1.5"></circle><circle cx="16" cy="17.5" r="1.5"></circle>',
    'entradas'   => '<path d="M12 3v12"></path><path d="m7 10 5 5 5-5"></path><path d="M4 20h16"></path>',
    'vendas'     => '<circle cx="9" cy="20" r="1.5"></circle><circle cx="18" cy="20" r="1.5"></circle><path d="M3 3h2l2.6 12.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.5L21 8H6"></path>',
    'relatorios' => '<path d="M5 20v-9"></path><path d="M12 20V4"></path><path d="M19 20v-6"></path><path d="M3 20h18"></path>',
    'interessados' => '<circle cx="9" cy="8" r="3.5"></circle><path d="M2.5 20a6.5 6.5 0 0 1 13 0"></path><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8"></path><path d="M18 14.2a6.5 6.5 0 0 1 3.5 5.8"></path>',
    'sair'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="m16 17 5-5-5-5"></path><path d="M21 12H9"></path>',
    'alerta'     => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path>',
    'seta'       => '<path d="M7 17 17 7"></path><path d="M8 7h9v9"></path>',
    'raio'       => '<path d="m13 2-9 12h7l-1 8 9-12h-7z"></path>',
];
$ico = fn($k) => '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">' . $ic[$k] . '</svg>';
$rotas = ['inicio' => ['auth', 'dashboard'], 'carros' => ['produto', 'index'], 'entradas' => ['entrada', 'index'], 'vendas' => ['venda', 'index'], 'interessados' => ['interessado', 'index'], 'relatorios' => ['relatorio', 'index']];
$url = fn($k) => 'index.php?controller=' . $rotas[$k][0] . '&amp;action=' . $rotas[$k][1];
$nomes = ['inicio' => 'Início', 'carros' => 'Carros', 'entradas' => 'Entradas', 'vendas' => 'Vendas', 'interessados' => 'Interessados', 'relatorios' => 'Relatórios'];
$descs = ['interessados' => 'Clientes para contatar', 'carros' => 'Catálogo e cadastro', 'entradas' => 'Reposição de estoque', 'vendas' => 'Registro e acompanhamento', 'relatorios' => 'Indicadores e históricos'];
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Dashboard | Montclair Motors</title>
    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="public/assets/css/painel.css?v=<?= (int)@filemtime(__DIR__ . '/../public/assets/css/painel.css') ?>">
</head>

<body>
<div class="moldura">
<div class="app">

    <header class="topo">
        <a class="logo" href="<?= $url('inicio') ?>" aria-label="Montclair Motors"><img src="public/assets/uploads/logo2.png" alt="Montclair Motors"></a>
        <nav class="abas" aria-label="Atalhos principais">
            <a class="on" href="<?= $url('inicio') ?>" aria-current="page">Visão geral</a>
            <a href="<?= $url('vendas') ?>">Vendas</a>
            <a href="<?= $url('relatorios') ?>">Relatórios</a>
        </nav>
        <div class="usuario">
            <span class="avatar" aria-hidden="true"><?= $h($inicial) ?></span>
            <span class="u-txt"><strong><?= $h($nome) ?></strong><small><?= $h($perfil) ?></small></span>
        </div>
    </header>

    <nav class="lateral" aria-label="Módulos do sistema">
        <div class="lateral-grupo">
            <?php foreach ($rotas as $k => $_): ?>
                <a href="<?= $url($k) ?>" title="<?= $nomes[$k] ?>" aria-label="<?= $nomes[$k] ?>"<?= $k === 'inicio' ? ' class="on" aria-current="page"' : '' ?>><?= $ico($k) ?></a>
            <?php endforeach; ?>
        </div>
        <a href="index.php?controller=auth&amp;action=logout" title="Sair" aria-label="Sair"><?= $ico('sair') ?></a>
    </nav>

    <section class="card hero">
        <img class="hero-img" src="public/assets/uploads/equipe-login.png" alt="" aria-hidden="true">
        <div class="hero-corpo">
            <span class="chip">Painel da loja</span>
            <h1>Bem-vindo(a),<br><?= $h($nome) ?></h1>
            <p>Escolha um módulo para continuar.</p>
            <div class="stats">
                <div class="stat"><span class="stat-ico"><?= $ico('vendas') ?></span><div><small>Vendas (mês)</small><strong><?= (int)$resumo['vendas_mes'] ?></strong></div></div>
                <div class="stat"><span class="stat-ico"><?= $ico('carros') ?></span><div><small>Carros no catálogo</small><strong><?= (int)$resumo['produtos'] ?></strong></div></div>
            </div>
        </div>
        <div class="hero-rodape">
            <div><small>Acesso como</small><strong><?= $h($perfil) ?></strong></div>
            <a class="btn" href="<?= $url('vendas') ?>">Nova venda <?= $ico('seta') ?></a>
        </div>
    </section>

    <aside class="card atalhos">
        <div class="atalhos-topo">
            <span class="caixa-ico"><?= $ico('raio') ?></span>
            <div><h2>Acesso rápido</h2><small>Escolha um módulo</small></div>
        </div>
        <div class="tiles">
            <?php $i = 0; foreach ($descs as $k => $d): ?>
                <a class="tile<?= $i++ === 0 ? ' escuro' : '' ?>" href="<?= $url($k) ?>">
                    <?= $ico($k) ?><strong><?= $nomes[$k] ?></strong><small><?= $d ?></small>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="nota">
            <p>Vendas e carros são atualizados a cada venda registrada. Novos interessados chegam quando um cliente marca interesse em um carro no site.</p>
            <a class="btn" href="<?= $url('entradas') ?>">Entradas</a>
        </div>
    </aside>

    <?php
    $minis = [
        ['Interessados', 'Clientes aguardando contato', 'interessados', 'interessados', $novosInteressados, 'novos interessados', ''],
        ['Entradas (mês)', 'Reposição e entrada de estoque', 'entradas', 'entradas', $entradasMes, 'entradas registradas', ''],
        ['Relatórios', 'Indicadores e históricos', 'relatorios', 'relatorios', null, 'Ver indicadores', ' preto'],
    ];
    foreach ($minis as [$t, $s, $rota, $icone, $n, $leg, $cls]): ?>
        <div class="card mini<?= $cls ?>">
            <div class="mini-topo">
                <div><h3><?= $t ?></h3><small><?= $s ?></small></div>
                <a class="seta-btn" href="<?= $url($rota) ?>" aria-label="Abrir <?= $t ?>"><?= $ico('seta') ?></a>
            </div>
            <div class="caixa">
                <span class="caixa-ico"><?= $ico($icone) ?></span>
                <?php if ($n !== null): ?>
                    <div><strong><?= (int)$n ?></strong><small><?= $leg ?></small></div>
                <?php else: ?>
                    <div><strong><?= (int)$resumo['vendas_mes'] ?></strong><small>vendas neste mês</small></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>
<footer class="rodape">&copy; 2026 Montclair Motors. Todos os direitos reservados.</footer>
</div>
</body>

</html>