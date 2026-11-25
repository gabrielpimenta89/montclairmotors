<?php
// Espera: $lista, $contagem, $status (filtro atual), $token (csrf) e $erro. Veja InteressadoController.
$h        = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$lista    = $lista ?? [];
$contagem = $contagem ?? [];
$status   = $status ?? '';
$token    = $token ?? '';
$erro     = $erro ?? null;
$ativo    = 'interessados';

$rotulos = ['novo' => 'Novo', 'em_contato' => 'Em contato', 'negociando' => 'Negociando', 'fechado' => 'Fechado', 'descartado' => 'Descartado'];
$brl = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
$ha = function ($min) {
    $min = max(0, (int)$min);
    if ($min < 1) return 'agora mesmo';
    if ($min < 60) return "há $min min";
    if ($min < 1440) return 'há ' . intdiv($min, 60) . ' h';
    $n = intdiv($min, 1440);
    return 'há ' . $n . ' dia' . ($n === 1 ? '' : 's');
};
$zap = function ($cel) {
    $d = preg_replace('/\D/', '', (string)$cel);
    if (strlen($d) < 10) return null;
    return strlen($d) <= 11 ? '55' . $d : $d;
};
$base = 'index.php?controller=interessado&amp;action=index';
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Interessados | Montclair Motors</title>
    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="public/assets/css/painel-paginas.css?v=<?= (int)@filemtime(__DIR__ . '/../public/assets/css/painel-paginas.css') ?>">
</head>

<body>
<div class="moldura">
<div class="app pagina">

<?php require __DIR__ . '/painel_topo.php'; ?>

<main class="conteudo">

    <div class="titulo"><h1>Interessados</h1><small>Clientes que marcaram interesse em um carro pelo site</small></div>

    <?php if ($erro): ?><div class="alerta" role="alert"><?= $h($erro) ?></div><?php endif; ?>

    <nav class="chips" aria-label="Filtrar por situação">
        <a href="<?= $base ?>"<?= $status === '' ? ' class="on"' : '' ?>>Todos (<?= (int)($contagem['todos'] ?? 0) ?>)</a>
        <?php foreach ($rotulos as $k => $t): ?>
            <a href="<?= $base ?>&amp;status=<?= $k ?>"<?= $status === $k ? ' class="on"' : '' ?>><?= $t ?> (<?= (int)($contagem[$k] ?? 0) ?>)</a>
        <?php endforeach; ?>
    </nav>

    <?php if (!$erro && !$lista): ?>
        <p class="vazio">Nenhum interessado aqui. Quando um cliente marcar interesse em um carro no site, ele aparece nesta lista.</p>
    <?php endif; ?>

    <?php foreach ($lista as $i):
        $vendido  = (int)$i['vendido'] === 1;
        $carro    = $i['marca'] . ' ' . $i['modelo'];
        $numero   = $zap($i['celular']);
        $primeiro = explode(' ', trim($i['nome']))[0];
        $texto    = 'Olá, ' . $primeiro . '! Aqui é ' . $nome . ' da Montclair Motors. Vi que você se interessou pelo ' . $carro . '. Posso ajudar?';
        $dataMarcou = strtotime($i['data_registro']);
    ?>
        <article class="card interesse<?= $vendido ? ' encerrado' : '' ?>">

            <div class="int-carro">
                <?php if (!empty($i['imagem'])): ?><img class="foto" src="<?= $h($i['imagem']) ?>" alt="Foto do carro"><?php else: ?><div class="foto sem-foto">Sem foto</div><?php endif; ?>
                <div>
                    <strong><?= $h($carro) ?> <small><?= $h($i['ano']) ?></small></strong><br>
                    <small><?= $brl($i['preco']) ?></small><br>
                    <span class="selo<?= $vendido ? ' vendido' : '' ?>"><?= $vendido ? 'Carro vendido' : 'À venda' ?></span>
                </div>
            </div>

            <div class="int-cliente">
                <strong><?= $h($i['nome']) ?></strong>
                <small><?= $i['email'] ? $h($i['email']) : 'E-mail não informado' ?></small>
                <small><?= $i['celular'] ? $h($i['celular']) : 'Celular não informado' ?></small>
                <small>Marcou interesse <?= $ha($i['minutos']) ?> · <?= date('d/m/Y \à\s H:i', $dataMarcou) ?></small>
            </div>

            <div class="int-acoes">
                <span class="selo st-<?= $h($i['status']) ?>"><?= $h($rotulos[$i['status']] ?? $i['status']) ?></span>
                <?php if ($numero): ?>
                    <a class="btn mini" target="_blank" rel="noopener" href="<?= $h('https://wa.me/' . $numero . '?text=' . rawurlencode($texto)) ?>">WhatsApp</a>
                <?php endif; ?>
                <?php if ($i['email']): ?>
                    <a class="btn mini claro" href="<?= $h('mailto:' . $i['email'] . '?subject=' . rawurlencode('Montclair Motors: ' . $carro)) ?>">E-mail</a>
                <?php endif; ?>
            </div>

            <form class="int-status" method="post" action="index.php?controller=interessado&amp;action=atualizar">
                <input type="hidden" name="csrf" value="<?= $h($token) ?>">
                <input type="hidden" name="id_interesse" value="<?= (int)$i['id_interesse'] ?>">
                <input type="hidden" name="voltar" value="<?= $h($status) ?>">
                <div class="campo">
                    <select name="status" aria-label="Situação do contato">
                        <?php foreach ($rotulos as $k => $t): ?><option value="<?= $k ?>"<?= $i['status'] === $k ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <input type="text" name="observacao" maxlength="255" placeholder="Anotação (ex.: ligar amanhã às 14h)" aria-label="Anotação" value="<?= $h($i['observacao'] ?? '') ?>">
                </div>
                <button class="btn mini" type="submit">Salvar</button>
            </form>

        </article>
    <?php endforeach; ?>

</main>

</div>
<footer class="rodape">&copy; 2026 Montclair Motors. Todos os direitos reservados.</footer>
</div>
</body>

</html>
