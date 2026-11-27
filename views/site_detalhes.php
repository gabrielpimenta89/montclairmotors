<?php
$cssExtra = 'paginas.css'; $titulo = ($carro ? site_nome($carro) : 'Carro não encontrado') . ' | Montclair Motors';
require __DIR__ . '/site_topo.php';
$volta = 'index.php?controller=site&amp;action=carros';
?>
<main class="detalhe">
<?php if (!$carro): ?>
  <section class="detalhe-topo"><p class="vazio">Este carro não está mais disponível. <a class="voltar" href="<?= $volta ?>">Voltar aos carros</a></p></section>
<?php else: $outros = $outros ?? []; ?>
  <section class="detalhe-topo det-hero">
    <div class="detalhe-foto" style="view-transition-name:foto-<?= (int)$carro['id_carro'] ?>">
      <?php if (!empty($carro['imagem'])): ?><img src="<?= site_e($carro['imagem']) ?>" alt="<?= site_e(site_nome($carro)) ?>"><?php endif; ?>
    </div>
    <div class="detalhe-corpo">
      <div class="det-titulo">
        <span class="det-etiqueta">Disponível · <?= (int)$carro['ano'] ?></span>
        <h1><?= site_e($carro['marca']) ?> <em><?= site_e($carro['modelo']) ?></em></h1>
      </div>
      <div>
        <p class="detalhe-preco" data-valor="<?= (float)$carro['preco'] ?>"><?= site_e(site_preco($carro['preco'])) ?></p>
        <div class="acoes">
          <?php if ($interessado): ?>
            <button class="btn" disabled>Interesse registrado</button>
          <?php elseif ($logado): ?>
            <form method="post" action="index.php?controller=site&amp;action=interesse">
              <input type="hidden" name="csrf" value="<?= site_csrf() ?>">
              <input type="hidden" name="id_carro" value="<?= (int)$carro['id_carro'] ?>">
              <button class="btn">Tenho interesse</button>
            </form>
          <?php else: ?>
            <a class="btn" href="index.php?controller=site&amp;action=login&amp;voltar=<?= (int)$carro['id_carro'] ?>">Tenho interesse</a>
          <?php endif; ?>
          <a class="voltar" href="<?= $volta ?>">Voltar aos carros</a>
        </div>
      </div>
    </div>
  </section>
  <section class="ficha">
    <h2 class="det-h2 reveal">Ficha <em>técnica.</em></h2>
    <dl class="specs">
      <?php $n = 0; foreach (site_specs($carro) as $k => $v): ?>
        <div class="reveal" style="--i:<?= $n++ ?>"><dt><?= site_e($k) ?></dt><dd><?= site_e($v) ?></dd></div>
      <?php endforeach; ?>
    </dl>
  </section>
  <section class="simulador reveal" data-preco="<?= (float)$carro['preco'] ?>">
    <div class="sim-intro">
      <h2>Simule seu <em>financiamento</em></h2>
      <p>Estimativa sem compromisso. A taxa real depende da análise de crédito.</p>
    </div>
    <div class="sim-corpo">
      <div class="sim-campos">
        <label class="sim-campo"><span>Entrada <b data-sim="entrada-pct"></b></span>
          <input type="range" min="0" max="80" step="1" value="20" data-sim="entrada">
        </label>
        <fieldset class="sim-parcelas"><legend>Parcelas</legend>
          <?php foreach ([12, 24, 36, 48, 60] as $n): ?>
            <label><input type="radio" name="parcelas" value="<?= $n ?>"<?= $n === 48 ? ' checked' : '' ?>><span><?= $n ?>x</span></label>
          <?php endforeach; ?>
        </fieldset>
        <label class="sim-campo"><span>Taxa estimada (% ao mês)</span>
          <input type="number" min="0" max="10" step="0.01" value="<?= SITE_TAXA_MENSAL ?>" data-sim="taxa">
        </label>
      </div>
      <div class="sim-resultado" aria-live="polite">
        <span>Parcela estimada</span>
        <strong data-sim="parcela">—</strong>
        <dl>
          <div><dt>Entrada</dt><dd data-sim="entrada-valor">—</dd></div>
          <div><dt>Valor financiado</dt><dd data-sim="financiado">—</dd></div>
          <div><dt>Total a pagar</dt><dd data-sim="total">—</dd></div>
        </dl>
      </div>
    </div>
  </section>
  <?php if ($outros): ?>
  <section class="relacionados">
    <h2 class="det-h2 reveal">Você também pode <em>gostar.</em></h2>
    <div class="rel-grade">
      <?php foreach ($outros as $i => $o): ?>
        <a class="rel-card reveal" style="--i:<?= $i ?>" href="index.php?controller=site&amp;action=detalhes&amp;id=<?= (int)$o['id_carro'] ?>">
          <div class="foto" style="view-transition-name:foto-<?= (int)$o['id_carro'] ?>">
            <?php if (!empty($o['imagem'])): ?><img src="<?= site_e($o['imagem']) ?>" alt="<?= site_e(site_nome($o)) ?>" loading="lazy"><?php endif; ?>
          </div>
          <div class="rel-info"><h3><?= site_e(site_nome($o)) ?></h3><span><?= site_e(site_preco($o['preco'])) ?></span></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
<?php endif; ?>
</main>
<?php require __DIR__ . '/site_rodape.php'; ?>