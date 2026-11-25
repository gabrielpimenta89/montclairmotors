<?php
$brl = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');

$produtos   = $produtos ?? [];
$carrinho   = $carrinho ?? [];
$total      = $total ?? 0;
$ultimas    = $ultimas ?? [];
$pagamentos = $pagamentos ?? [];
$erro       = $erro ?? null;
$form       = $form ?? [];
$ativo      = 'vendas';

$idsNoCarrinho = array_map(fn($c) => (int)$c['id_carro'], $carrinho);
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Vendas | Montclair Motors</title>
    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="public/assets/css/painel-paginas.css?v=<?= (int)@filemtime(__DIR__ . '/../public/assets/css/painel-paginas.css') ?>">
</head>

<body>
<div class="moldura">
<div class="app pagina">

<?php require __DIR__ . '/painel_topo.php'; ?>

<main class="conteudo">

    <div class="titulo"><h1>Nova venda</h1><small>Monte o carrinho e registre a venda</small></div>

    <?php if ($erro): ?><div class="alerta" role="alert"><?= $h($erro) ?></div><?php endif; ?>

    <div class="duas">

        <section class="card painel">
            <h2>Carros disponíveis <span class="contador"><?= count($produtos) ?></span></h2>
            <?php if (empty($produtos)): ?><p class="vazio">Nenhum carro em estoque no momento.</p><?php endif; ?>

            <?php foreach ($produtos as $p): ?>
                <?php $noCarrinho = in_array((int)$p['id_carro'], $idsNoCarrinho, true); ?>
                <div class="carro">
                    <?php if (!empty($p['imagem'])): ?>
                        <img class="foto" src="<?= $h($p['imagem']) ?>" alt="Foto do carro">
                    <?php else: ?>
                        <div class="foto sem-foto">Sem foto</div>
                    <?php endif; ?>
                    <div>
                        <div class="carro-nome"><?= $h($p['marca']) ?> <?= $h($p['modelo']) ?></div>
                        <small>
                            <?= $h($p['ano']) ?><?= !empty($p['cor']) ? ' • ' . $h($p['cor']) : '' ?> • <?= number_format((int)$p['km'], 0, ',', '.') ?> km<?= !empty($p['combustivel']) ? ' • ' . $h($p['combustivel']) : '' ?>
                        </small>
                    </div>
                    <div class="carro-acao">
                        <strong><?= $brl($p['preco']) ?></strong>
                        <?php if ($noCarrinho): ?>
                            <small class="no-carrinho">No carrinho</small>
                        <?php else: ?>
                            <a class="btn mini" href="index.php?controller=venda&action=adicionar&id=<?= (int)$p['id_carro'] ?>">Adicionar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="card painel">
            <h2>Carrinho <span class="contador"><?= count($carrinho) ?></span></h2>

            <?php if (empty($carrinho)): ?>
                <p class="vazio">Carrinho vazio. Adicione um carro da lista ao lado.</p>
            <?php else: ?>
                <?php foreach ($carrinho as $c): ?>
                    <div class="item">
                        <div>
                            <div class="carro-nome"><?= $h($c['marca']) ?> <?= $h($c['modelo']) ?> <?= $h($c['ano']) ?></div>
                            <a class="remover" href="index.php?controller=venda&action=remover&id=<?= (int)$c['id_carro'] ?>">Remover</a>
                        </div>
                        <strong><?= $brl($c['preco']) ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="total"><span>Total</span><strong><?= $brl($total) ?></strong></div>
                <a class="limpar" href="index.php?controller=venda&action=limpar">Esvaziar carrinho</a>
            <?php endif; ?>

            <form class="form-cliente" method="post" action="index.php?controller=venda&action=finalizar">
                <h3>Dados do cliente</h3>

                <div class="campo">
                    <label for="cliente_nome">Nome completo</label>
                    <input type="text" id="cliente_nome" name="cliente_nome" required value="<?= $h($form['cliente_nome'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label for="cliente_cpf">CPF</label>
                    <input type="text" id="cliente_cpf" name="cliente_cpf" required inputmode="numeric" maxlength="14" placeholder="000.000.000-00"
                           pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}" title="Digite os 11 números do CPF" value="<?= $h($form['cliente_cpf'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label for="forma_pagamento">Forma de pagamento</label>
                    <select id="forma_pagamento" name="forma_pagamento" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($pagamentos as $fp): ?>
                            <option value="<?= $h($fp) ?>"<?= (($form['forma_pagamento'] ?? '') === $fp) ? ' selected' : '' ?>><?= $h($fp) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button class="btn grande" type="submit"<?= empty($carrinho) ? ' disabled' : '' ?>>Finalizar venda e gerar nota</button>
            </form>
        </section>

    </div>

    <section class="card painel">
        <h2>Últimas vendas</h2>
        <?php if (empty($ultimas)): ?>
            <p class="vazio">Nenhuma venda registrada ainda.</p>
        <?php else: ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead><tr><th>Nº</th><th>Cliente</th><th>Pagamento</th><th>Data</th><th class="num">Total</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($ultimas as $u): ?>
                            <tr>
                                <td><?= $h($u['numero_nota']) ?></td>
                                <td><?= $h($u['cliente_nome']) ?></td>
                                <td><?= $h($u['forma_pagamento']) ?></td>
                                <td><?= $h(date('d/m/Y H:i', strtotime($u['data_emissao']))) ?></td>
                                <td class="num"><?= $brl($u['valor_total']) ?></td>
                                <td class="num"><a class="btn mini claro" href="index.php?controller=venda&action=nota&id=<?= (int)$u['id_nota'] ?>">Ver nota</a></td>
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