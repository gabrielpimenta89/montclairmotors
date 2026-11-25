<?php
$editar   = $editar ?? null;
$produtos = $produtos ?? [];
$ativo    = 'carros';
$v = fn($campo) => htmlspecialchars((string)($editar[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Carros | Montclair Motors</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
    <link rel="stylesheet" href="public/assets/css/painel-paginas.css?v=<?= (int)@filemtime(__DIR__ . '/../public/assets/css/painel-paginas.css') ?>">
</head>

<body>
<div class="moldura">
<div class="app pagina">

<?php require __DIR__ . '/painel_topo.php'; ?>

<main class="conteudo">

    <div class="titulo"><h1>Carros</h1><small>Cadastre e gerencie o catálogo da loja</small></div>

    <div class="duas produtos-grid">

        <section class="card painel">
            <h2><?= $editar ? 'Editar carro #' . (int)$editar['id_carro'] : 'Cadastrar carro' ?></h2>

            <form class="form" method="post" action="index.php?controller=produto&action=salvar" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= (int)($editar['id_carro'] ?? 0) ?>">

                <div class="linha">
                    <div class="campo"><label for="marca">Marca</label><input type="text" id="marca" name="marca" required value="<?= $v('marca') ?>"></div>
                    <div class="campo"><label for="modelo">Modelo</label><input type="text" id="modelo" name="modelo" required value="<?= $v('modelo') ?>"></div>
                </div>
                <div class="linha">
                    <div class="campo"><label for="ano">Ano</label><input type="number" id="ano" name="ano" required value="<?= $v('ano') ?>"></div>
                    <div class="campo"><label for="cor">Cor</label><input type="text" id="cor" name="cor" value="<?= $v('cor') ?>"></div>
                </div>
                <div class="linha">
                    <div class="campo"><label for="combustivel">Combustível</label><input type="text" id="combustivel" name="combustivel" value="<?= $v('combustivel') ?>"></div>
                    <div class="campo"><label for="km">KM</label><input type="number" id="km" name="km" value="<?= $v('km') ?>"></div>
                </div>
                <div class="campo"><label for="n_chassi">Nº do chassi</label><input type="text" id="n_chassi" name="n_chassi" value="<?= $v('n_chassi') ?>"></div>
                <div class="campo"><label for="imagem">Imagem do carro</label><input type="file" name="imagem" id="imagem" accept="image/*"></div>
                <img id="previewImagem" class="preview" alt="Prévia da imagem">
                <div class="campo"><label for="preco">Preço (R$)</label><input type="number" id="preco" name="preco" step="0.01" min="0" required value="<?= $v('preco') ?>"></div>

                <div class="acoes">
                    <button class="btn" type="submit">Salvar</button>
                    <a class="btn claro" href="index.php?controller=produto&action=index">Limpar</a>
                </div>
            </form>
        </section>

        <section class="card painel">
            <h2>Lista de carros <span class="contador"><?= count($produtos) ?></span></h2>

            <?php if (empty($produtos)): ?>
                <p class="vazio">Nenhum carro cadastrado ainda.</p>
            <?php else: ?>
                <div class="tabela-wrap">
                    <table class="tabela">
                        <thead><tr><th></th><th>ID</th><th>Carro</th><th>Ano</th><th>Cor</th><th class="num">KM</th><th class="num">Preço</th><th>Ações</th></tr></thead>
                        <tbody>
                            <?php foreach ($produtos as $p): ?>
                                <tr>
                                    <td><?php if (!empty($p['imagem'])): ?><img class="thumb" src="<?= $h($p['imagem']) ?>" alt="Foto do carro"><?php else: ?><div class="thumb sem-foto"></div><?php endif; ?></td>
                                    <td><small>#<?= (int)$p['id_carro'] ?></small></td>
                                    <td><strong><?= $h($p['marca']) ?></strong> <?= $h($p['modelo']) ?></td>
                                    <td><?= $h($p['ano']) ?></td>
                                    <td><?= $h($p['cor']) ?></td>
                                    <td class="num"><?= number_format((int)$p['km'], 0, ',', '.') ?></td>
                                    <td class="num preco">R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></td>
                                    <td>
                                        <div class="acoes">
                                            <a class="btn mini" href="index.php?controller=venda&action=adicionar&id=<?= (int)$p['id_carro'] ?>">Vender</a>
                                            <a class="btn mini claro" href="index.php?controller=produto&action=index&id=<?= (int)$p['id_carro'] ?>">Editar</a>
                                            <a class="btn mini perigo" href="index.php?controller=produto&action=deletar&id=<?= (int)$p['id_carro'] ?>" onclick="return confirm('Deletar este carro?')">Excluir</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </div>

</main>

</div>
<footer class="rodape">&copy; 2026 Montclair Motors. Todos os direitos reservados.</footer>
</div>

<script>
document.getElementById("imagem").addEventListener("change", function (e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function (ev) {
    const img = document.getElementById("previewImagem");
    img.src = ev.target.result;
    img.style.display = "block";
  };
  reader.readAsDataURL(file);
});
</script>
</body>

</html>