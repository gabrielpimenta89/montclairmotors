<?php
$titulo   = 'Montclair Motors';
$cssExtra = ['paginas.css', 'home.css'];
$semMarca = true; // a home já tem o nome gigante no corpo da página
$whatsapp = ''; // número da loja com DDI e DDD, só dígitos (ex.: 5521999999999). Vazio = o botão fica escondido.

$destaque = $carros[0] ?? null;
$vitrine  = array_slice($carros, 0, 3);
$total    = count($carros);
$marcas   = count(array_unique(array_column($carros, 'marca')));
$menor    = $total ? min(array_map('floatval', array_column($carros, 'preco'))) : 0;
$anoMax   = $total ? max(array_map('intval', array_column($carros, 'ano'))) : 0;
$fotoHero = !empty($destaque['imagem']) ? $destaque['imagem'] : null;
$foto2    = !empty($carros[1]['imagem']) ? $carros[1]['imagem'] : $fotoHero;
$zapOk    = (bool)preg_match('/^\d{12,13}$/', $whatsapp);
require __DIR__ . '/site_topo.php';
?>
<main class="cine">

<section class="c-hero<?= $fotoHero ? '' : ' sem-foto' ?>">
  <?php if ($fotoHero): ?><img class="c-hero-img" src="<?= site_e($fotoHero) ?>" alt="" aria-hidden="true"><?php endif; ?>
  <div class="c-hero-corpo">
    <span class="c-etiqueta cine-in">Montclair Motors · Coleção <?= date('Y') ?></span>
    <h1 class="cine-in" style="--i:1">Excelência<br><em>em movimento.</em></h1>
    <p class="cine-in" style="--i:2">Carros selecionados para quem busca desempenho, conforto e sofisticação. Preço, especificações e fotos sempre à vista.</p>
    <div class="c-acoes cine-in" style="--i:3">
      <a class="c-btn" href="index.php?controller=site&amp;action=carros">Explorar a coleção</a>
      <a class="c-btn c-vazado" href="#sobre">Conheça a loja</a>
    </div>
  </div>
  <?php if ($destaque): ?>
    <div class="c-hero-rodape cine-in" style="--i:4">
      <div><span class="c-etiqueta">Em destaque</span><strong><?= site_e(site_nome($destaque)) ?></strong></div>
      <div class="c-hero-preco" data-valor="<?= (float)$destaque['preco'] ?>"><?= site_e(site_preco($destaque['preco'])) ?></div>
      <a class="c-link" href="index.php?controller=site&amp;action=detalhes&amp;id=<?= (int)$destaque['id_carro'] ?>">Ver detalhes →</a>
    </div>
  <?php endif; ?>
</section>

<section class="c-sec">
  <header class="c-cab cine-in">
    <span class="c-etiqueta">A coleção</span>
    <h2>Nossas máquinas.<br><em>Uma só filosofia.</em></h2>
  </header>
  <?php if (!$vitrine): ?>
    <p class="c-vazio cine-in">Novos carros chegam em breve.</p>
  <?php else: ?>
    <div class="c-cards n<?= count($vitrine) ?>">
      <?php foreach ($vitrine as $i => $c): ?>
        <a class="c-card cine-in" style="--i:<?= $i ?>" href="index.php?controller=site&amp;action=detalhes&amp;id=<?= (int)$c['id_carro'] ?>">
          <div class="c-card-foto" style="view-transition-name:foto-<?= (int)$c['id_carro'] ?>">
            <?php if (!empty($c['imagem'])): ?><img src="<?= site_e($c['imagem']) ?>" alt="<?= site_e(site_nome($c)) ?>" loading="lazy"><?php endif; ?>
          </div>
          <div class="c-card-info">
            <span class="c-num">0<?= $i + 1 ?></span>
            <h3><?= site_e(site_nome($c)) ?></h3>
            <p><?= site_e(implode(' · ', array_slice(site_specs($c), 0, 3))) ?></p>
            <strong><?= site_e(site_preco($c['preco'])) ?></strong>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="c-mais cine-in"><a class="c-btn c-vazado" href="index.php?controller=site&amp;action=carros">Ver todos os carros (<?= $total ?>)</a></div>
  <?php endif; ?>
</section>

<?php if ($total): ?>
<section class="c-sec c-numeros">
  <header class="c-cab cine-in">
    <span class="c-etiqueta">Em números</span>
    <h2>Números que<br><em>falam baixo.</em></h2>
  </header>
  <div class="c-grade-num">
    <div class="cine-in" style="--i:0"><strong data-contar="<?= $total ?>"><?= $total ?></strong><span><?= $total === 1 ? 'carro disponível' : 'carros disponíveis' ?></span></div>
    <div class="cine-in" style="--i:1"><strong data-contar="<?= $marcas ?>"><?= $marcas ?></strong><span><?= $marcas === 1 ? 'marca' : 'marcas' ?></span></div>
    <div class="cine-in" style="--i:2"><strong><?= $anoMax ?></strong><span>modelo mais novo</span></div>
    <div class="cine-in" style="--i:3"><strong class="c-txt"><?= site_e(site_preco($menor)) ?></strong><span>a partir de</span></div>
  </div>
</section>
<?php endif; ?>

<section class="c-sec c-split" id="sobre">
  <div class="c-split-foto cine-in">
    <?php if ($foto2): ?><img src="<?= site_e($foto2) ?>" alt="" loading="lazy"><?php endif; ?>
  </div>
  <div class="c-split-texto cine-in" style="--i:1">
    <span class="c-etiqueta">Sobre nós</span>
    <h2>Seleção criteriosa.<br><em>Cliente em primeiro.</em></h2>
    <p>A Montclair Motors reúne carros selecionados para quem busca desempenho, conforto e sofisticação. Cada veículo aparece aqui com preço, especificações e fotos, para você decidir com clareza.</p>
    <ul class="c-valores">
      <li><strong>Seleção criteriosa</strong><span>Cada carro é escolhido antes de chegar ao site.</span></li>
      <li><strong>Transparência</strong><span>Preço e especificações sempre à vista.</span></li>
      <li><strong>Atendimento próximo</strong><span>Uma equipe pronta para ajudar você.</span></li>
    </ul>
  </div>
</section>

<section class="c-cta">
  <p class="c-manifesto cine-in">Crie sua conta, marque interesse nos carros de que mais gostar e nossa equipe acompanha o seu interesse.</p>
  <div class="c-acoes cine-in" style="--i:1">
    <?php if ($logado): ?>
      <a class="c-btn" href="index.php?controller=site&amp;action=interesses">Meus interesses</a>
    <?php else: ?>
      <a class="c-btn" href="index.php?controller=site&amp;action=cadastro">Criar conta</a>
    <?php endif; ?>
    <a class="c-btn c-vazado" href="index.php?controller=site&amp;action=carros">Ver os carros</a>
  </div>
</section>

<div class="c-marca" aria-hidden="true">MONTCLAIR</div>

<?php if ($zapOk): ?>
  <a class="c-zap" target="_blank" rel="noopener" href="https://wa.me/<?= $whatsapp ?>?text=<?= rawurlencode('Olá! Vim pelo site da Montclair Motors.') ?>"><i></i>Fale conosco</a>
<?php endif; ?>

</main>
<script>
// revela cada bloco quando ele entra na tela
(function () {
  var els = document.querySelectorAll('.cine-in');
  if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('on'); }); return; }
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('on'); io.unobserve(e.target); } });
  }, { threshold: .15 });
  els.forEach(function (e) { io.observe(e); });
})();
</script>
<?php require __DIR__ . '/site_rodape.php'; ?>