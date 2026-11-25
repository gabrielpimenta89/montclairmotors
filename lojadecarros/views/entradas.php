<?php
// Espera $entradas: lista (mais recente primeiro) com id_entrada, data_entrada e os dados do carro
// (id_carro, marca, modelo, ano, cor, km, n_chassi, preco, imagem e vendido). data_entrada pode vir vazia (carro anterior ao registro).
$entradas = $entradas ?? [];
$erro     = $erro ?? null;
$ativo    = 'entradas';
$brl      = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');

$hoje = date('Y-m-d');
$mes  = date('Y-m');
$nHoje = $nMes = 0;
foreach ($entradas as $e) {
    if (empty($e['data_entrada'])) continue;
    $dia = date('Y-m-d', strtotime($e['data_entrada']));
    if ($dia === $hoje) $nHoje++;
    if (substr($dia, 0, 7) === $mes) $nMes++;
}
$resumo = ['Total de entradas' => count($entradas), 'Hoje' => $nHoje, 'Neste mês' => $nMes];
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Entradas | Montclair Motors</title>
    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="public/assets/css/painel-paginas.css?v=<?= (int)@filemtime(__DIR__ . '/../public/assets/css/painel-paginas.css') ?>">
</head>

<body>
<div class="moldura">
<div class="app pagina">

<?php require __DIR__ . '/painel_topo.php'; ?>

<main class="conteudo">

    <div class="titulo"><h1>Entradas</h1><small>Carros registrados no sistema, do mais recente ao mais antigo</small></div>

    <?php if ($erro): ?><div class="alerta" role="alert"><?= $h($erro) ?></div><?php endif; ?>

    <div class="resumo">
        <?php foreach ($resumo as $rotulo => $n): ?>
            <div class="card">
                <span class="chip-ico"><?= $ico('entradas') ?></span>
                <div><strong><?= (int)$n ?></strong><small><?= $rotulo ?></small></div>
            </div>
        <?php endforeach; ?>
    </div>

    <section class="card painel">
        <h2>Histórico de entradas <span class="contador"><?= count($entradas) ?></span></h2>

        <?php if (empty($entradas)): ?>
            <p class="vazio">Nenhuma entrada registrada ainda. Cadastre um carro em Carros e ele aparece aqui.</p>
        <?php else: ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead>
                        <tr><th></th><th>Carro</th><th>Nº do chassi</th><th class="num">Preço</th><th>Data</th><th>Horário</th><th>Situação</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entradas as $e): $ts = !empty($e['data_entrada']) ? strtotime($e['data_entrada']) : null; $vendido = (int)($e['vendido'] ?? 0) === 1; ?>
                            <tr>
                                <td><?php if (!empty($e['imagem'])): ?><img class="thumb" src="<?= $h($e['imagem']) ?>" alt="Foto do carro"><?php else: ?><div class="thumb sem-foto"></div><?php endif; ?></td>
                                <td>
                                    <strong><?= $h($e['marca']) ?> <?= $h($e['modelo']) ?></strong><br>
                                    <small><?= $h($e['ano']) ?><?= !empty($e['cor']) ? ' • ' . $h($e['cor']) : '' ?> • <?= number_format((int)$e['km'], 0, ',', '.') ?> km</small>
                                </td>
                                <td><?= $h($e['n_chassi'] ?: '-') ?></td>
                                <td class="num preco"><?= $brl($e['preco']) ?></td>
                                <td><?= $ts ? date('d/m/Y', $ts) : '<small>Sem registro</small>' ?></td>
                                <td><?= $ts ? date('H:i', $ts) : '—' ?></td>
                                <td><span class="selo<?= $vendido ? ' vendido' : '' ?>"><?= $vendido ? 'Vendido' : 'Em estoque' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</main>

</div>
<footer class="rodape">&copy; 2026 Montclair Motors. Todos os direitos reservados.</footer>
</div>
</body>

</html>