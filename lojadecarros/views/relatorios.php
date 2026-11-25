<?php
// Espera: $dados (veja models/relatorio.php), $de e $ate (AAAA-MM-DD), $atalhos e $erro.
$h       = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$erro    = $erro ?? null;
$dados   = $dados ?? [];
$atalhos = $atalhos ?? [];
$de      = $de ?? date('Y-m-01');
$ate     = $ate ?? date('Y-m-d');
$ativo   = 'relatorios';

$brl   = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
$curto = function ($v) {
    $v = (float)$v;
    if ($v >= 1000000) return 'R$ ' . number_format($v / 1000000, 1, ',', '.') . ' mi';
    if ($v >= 1000)    return 'R$ ' . number_format($v / 1000, 0, ',', '.') . ' mil';
    return 'R$ ' . number_format($v, 0, ',', '.');
};
$dataBr    = fn($d) => date('d/m/Y', strtotime($d));
$periodo   = $de < '2001-01-01' ? 'Todo o período' : $dataBr($de) . ' a ' . $dataBr($ate);
$qtdVendas = fn($n) => (int)$n . ' venda' . ((int)$n === 1 ? '' : 's');
$qtdCarros = fn($n) => (int)$n . ' carro' . ((int)$n === 1 ? '' : 's');

// lista com barras proporcionais: $sub (opcional) escreve o detalhe ao lado do nome
$rank = function (array $linhas, string $rot, string $val, callable $fmt, ?callable $sub = null) use ($h) {
    if (!$linhas) { echo '<p class="vazio">Sem dados no período.</p>'; return; }
    $max = max(array_map(fn($r) => (float)$r[$val], $linhas)) ?: 1;
    echo '<ul class="rank">';
    foreach ($linhas as $r) {
        $pct = round((float)$r[$val] / $max * 100);
        echo '<li><div class="rank-topo"><span>' . $h($r[$rot]) . ($sub ? ' <small>' . $h($sub($r)) . '</small>' : '')
           . '</span><strong>' . $h($fmt($r[$val])) . '</strong></div><div class="rank-barra"><i style="width:' . $pct . '%"></i></div></li>';
    }
    echo '</ul>';
};

if (!$erro) extract($dados);
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Relatórios | Montclair Motors</title>
    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="public/assets/css/painel-paginas.css?v=<?= (int)@filemtime(__DIR__ . '/../public/assets/css/painel-paginas.css') ?>">
</head>

<body>
<div class="moldura">
<div class="app pagina">

<?php require __DIR__ . '/painel_topo.php'; ?>

<main class="conteudo">

    <div class="titulo"><h1>Relatórios</h1><small><?= $h($periodo) ?></small></div>

    <form class="card filtro nao-imprimir" method="get" action="index.php">
        <input type="hidden" name="controller" value="relatorio">
        <input type="hidden" name="action" value="index">
        <div class="campo"><label for="de">De</label><input type="date" id="de" name="de" value="<?= $h($de) ?>" required></div>
        <div class="campo"><label for="ate">Até</label><input type="date" id="ate" name="ate" value="<?= $h($ate) ?>" required></div>
        <button class="btn" type="submit">Aplicar</button>
        <div class="periodos">
            <?php foreach ($atalhos as $nomeAtalho => [$a, $b]): ?>
                <a href="index.php?controller=relatorio&amp;action=index&amp;de=<?= $a ?>&amp;ate=<?= $b ?>"<?= ($a === $de && $b === $ate) ? ' class="on"' : '' ?>><?= $nomeAtalho ?></a>
            <?php endforeach; ?>
        </div>
        <button class="btn claro" type="button" onclick="window.print()">Imprimir</button>
    </form>

<?php if ($erro): ?>

    <div class="alerta" role="alert"><?= $h($erro) ?></div>

<?php else:
    $ticket = $resumo['qtd'] ? $resumo['total'] / $resumo['qtd'] : 0;
    $delta  = ($ant && (float)$ant['total'] > 0) ? ($resumo['total'] - $ant['total']) / $ant['total'] * 100 : null;
    $conv   = $demanda['interessados'] ? round($demanda['compraram'] / $demanda['interessados'] * 100) : 0;
    $maxSerie = $serie ? max(array_column($serie, 'total')) : 0;
    $deltaTxt = $delta === null
        ? 'sem período anterior para comparar'
        : '<span class="delta ' . ($delta >= 0 ? 'up' : 'down') . '">' . ($delta >= 0 ? '▲ ' : '▼ ') . number_format(abs($delta), 0, ',', '.') . '%</span> vs. período anterior';
    $cards = [
        ['Faturamento', $brl($resumo['total']), $deltaTxt, ' destaque'],
        ['Vendas', (int)$resumo['qtd'], 'carros vendidos no período', ''],
        ['Ticket médio', $brl($ticket), 'valor médio por venda', ''],
        ['A receber', $brl($pend['total']), (int)$pend['qtd'] . ' pagamento(s) pendente(s)', ''],
        ['Em estoque', $qtdCarros($estoque['qtd']), $brl($estoque['total']) . ' parados', ''],
        ['Conversão', $conv . '%', (int)$demanda['compraram'] . ' de ' . (int)$demanda['interessados'] . ' interessados compraram', ''],
    ];
?>

    <div class="indicadores">
        <?php foreach ($cards as [$rot, $valor, $detalhe, $cls]): ?>
            <div class="card indicador<?= $cls ?>"><small><?= $rot ?></small><strong><?= $h($valor) ?></strong><small><?= $detalhe ?></small></div>
        <?php endforeach; ?>
    </div>

    <section class="card painel">
        <h2>Vendas ao longo do tempo <small><?= $porDia ? 'por dia' : 'por mês' ?></small></h2>
        <?php if (!$serie): ?>
            <p class="vazio">Nenhuma venda no período.</p>
        <?php else: ?>
            <div class="grafico"><div class="barras">
                <?php foreach ($serie as $s): $pct = $s['total'] > 0 ? max(2, round($s['total'] / $maxSerie * 100)) : 0; ?>
                    <div class="coluna" title="<?= $h($s['rotulo'] . ': ' . $qtdVendas($s['qtd']) . ', ' . $brl($s['total'])) ?>">
                        <span class="v"><?= $s['qtd'] ? $h($curto($s['total'])) : '–' ?></span>
                        <div class="col-barra"><i style="height:<?= $pct ?>%"></i></div>
                        <span class="r"><?= $h($s['rotulo']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div></div>
        <?php endif; ?>
    </section>

    <div class="duas iguais">
        <section class="card painel">
            <h2>Vendedores</h2>
            <?php $rank($vend, 'nome', 'total', $brl, fn($r) => $qtdVendas($r['qtd'])); ?>
        </section>
        <section class="card painel">
            <h2>Formas de pagamento</h2>
            <?php $rank($pag, 'forma', 'total', $brl, fn($r) => $qtdVendas($r['qtd'])); ?>
        </section>
    </div>

    <div class="duas iguais">
        <section class="card painel">
            <h2>Estoque há mais tempo <small>carros à venda</small></h2>
            <?php if (!$parados): ?>
                <p class="vazio">Nenhum carro em estoque.</p>
            <?php else: ?>
                <div class="tabela-wrap">
                    <table class="tabela">
                        <thead><tr><th></th><th>Carro</th><th>No estoque</th><th class="num">Preço</th></tr></thead>
                        <tbody>
                            <?php foreach ($parados as $c): $dias = $c['dias'] === null ? null : (int)$c['dias']; ?>
                                <tr>
                                    <td><?php if (!empty($c['imagem'])): ?><img class="thumb" src="<?= $h($c['imagem']) ?>" alt="Foto do carro"><?php else: ?><div class="thumb sem-foto"></div><?php endif; ?></td>
                                    <td><strong><?= $h($c['marca']) ?> <?= $h($c['modelo']) ?></strong><br><small><?= $h($c['ano']) ?></small></td>
                                    <td>
                                        <?php if ($dias === null): ?><small>Sem registro</small>
                                        <?php else: ?><span class="selo<?= $dias >= 60 ? ' alerta' : '' ?>"><?= $dias ?> dia<?= $dias === 1 ? '' : 's' ?></span><?php endif; ?>
                                    </td>
                                    <td class="num preco"><?= $brl($c['preco']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <section class="card painel">
            <h2>Estoque atual</h2>
            <h3 class="sub-titulo">Por marca</h3>
            <?php $rank($porMarca, 'nome', 'qtd', $qtdCarros); ?>
            <h3 class="sub-titulo">Por combustível</h3>
            <?php $rank($porComb, 'nome', 'qtd', $qtdCarros); ?>
        </section>
    </div>

    <div class="duas iguais">
        <section class="card painel">
            <h2>Interesse dos clientes</h2>
            <p class="resumo-texto"><strong><?= (int)$demanda['interessados'] ?></strong> cliente(s) marcaram interesse; <strong><?= (int)$demanda['compraram'] ?></strong> já compraram.</p>
            <?php if (!$procurados): ?>
                <p class="vazio">Nenhum cliente marcou interesse ainda.</p>
            <?php else: foreach ($procurados as $c): ?>
                <div class="linha-carro">
                    <?php if (!empty($c['imagem'])): ?><img class="thumb" src="<?= $h($c['imagem']) ?>" alt="Foto do carro"><?php else: ?><div class="thumb sem-foto"></div><?php endif; ?>
                    <div><strong><?= $h($c['marca']) ?> <?= $h($c['modelo']) ?></strong><br><small><?= (int)$c['interessados'] ?> interessado<?= (int)$c['interessados'] === 1 ? '' : 's' ?></small></div>
                    <span class="selo<?= (int)$c['vendido'] ? ' vendido' : '' ?>"><?= (int)$c['vendido'] ? 'Vendido' : 'À venda' ?></span>
                </div>
            <?php endforeach; endif; ?>
        </section>
        <section class="card painel">
            <h2>Mais vendidos <small>no período</small></h2>
            <h3 class="sub-titulo">Marcas</h3>
            <?php $rank($marcas, 'nome', 'qtd', $qtdVendas); ?>
            <h3 class="sub-titulo">Modelos</h3>
            <?php $rank($modelos, 'nome', 'qtd', $qtdVendas); ?>
            <h3 class="sub-titulo">Faixa de preço</h3>
            <?php $rank($faixas, 'nome', 'qtd', $qtdVendas); ?>
        </section>
    </div>

<?php endif; ?>

</main>

</div>
<footer class="rodape">&copy; 2026 Montclair Motors. Todos os direitos reservados.</footer>
</div>
</body>

</html>