<?php
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$brl = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');

$numero = $nota['numero_nota'] ?: str_pad((string)$nota['id_nota'], 6, '0', STR_PAD_LEFT);
$cpfFormatado = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', (string)$nota['cliente_cpf']);
$dataFormatada = date('d/m/Y \à\s H:i', strtotime($nota['data_emissao']));

$dados = [
    'Cliente'            => $nota['cliente_nome'],
    'CPF'                => $cpfFormatado,
    'Forma de pagamento' => $nota['forma_pagamento'],
    'Data da venda'      => $dataFormatada,
    'Vendedor(a)'        => $nota['vendedor_nome'] ?? '-',
];
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Nota de venda Nº <?= $h($numero) ?> | Montclair Motors</title>
    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="public/assets/css/painel-nota.css?v=<?= (int)@filemtime(__DIR__ . '/../public/assets/css/painel-nota.css') ?>">
</head>

<body>
<div class="moldura">
<div class="app">

    <main class="nota">

        <header class="nota-topo">
            <img class="nota-logo" src="public/assets/uploads/logo2.png" alt="Montclair Motors">
            <div class="nota-titulo">
                <span>Nota de venda</span>
                <strong>Nº <?= $h($numero) ?></strong>
            </div>
        </header>

        <section class="nota-corpo">

            <div class="dados">
                <?php foreach ($dados as $rotulo => $valor): ?>
                    <div class="dado"><small><?= $rotulo ?></small><strong><?= $h($valor) ?></strong></div>
                <?php endforeach; ?>
            </div>

            <div class="tabela-wrap">
                <table class="itens">
                    <thead>
                        <tr><th>Veículo</th><th>Nº do chassi</th><th class="num">Valor</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nota['itens'] as $item): ?>
                            <tr>
                                <td><?= $h($item['descricao']) ?></td>
                                <td><?= $h($item['n_chassi'] ?: '-') ?></td>
                                <td class="num"><?= $brl($item['valor']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="2">Total</td><td class="num"><?= $brl($nota['valor_total']) ?></td></tr>
                    </tfoot>
                </table>
            </div>

            <p class="aviso">Este documento é um comprovante de venda e não substitui a nota fiscal eletrônica (NF-e).</p>

        </section>

    </main>

    <div class="acoes nao-imprimir">
        <button type="button" class="btn" onclick="window.print()">Imprimir</button>
        <a class="btn claro" href="index.php?controller=venda&action=index">Nova venda</a>
        <a class="btn claro" href="index.php?controller=auth&action=dashboard">Dashboard</a>
    </div>

</div>
</div>
</body>

</html>