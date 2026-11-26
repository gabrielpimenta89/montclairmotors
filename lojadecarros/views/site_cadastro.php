<?php $titulo = 'Criar conta | Montclair Motors'; $semCabecalho = true; require __DIR__ . '/site_topo.php'; ?>
<main class="entrada">
  <section class="entrada-form">
    <div class="entrada-corpo">
      <a class="entrada-logo" href="index.php?controller=site&amp;action=home">
        <img src="public/assets/uploads/logo2.png" alt="Montclair Motors">
      </a>

      <h1>Criar conta</h1>
      <p class="entrada-sub">Cadastre-se para registrar interesse nos carros.</p>
      <?php if ($erro): ?><div class="aviso" role="alert"><?= site_e($erro) ?></div><?php endif; ?>
      <form method="post" action="index.php?controller=site&amp;action=cadastro">
        <input type="hidden" name="csrf" value="<?= site_csrf() ?>">
        <input type="hidden" name="voltar" value="<?= $voltar ?>">
        <input class="entrada-campo" type="text" name="nome" value="<?= site_e($d['nome']) ?>" placeholder="Nome completo" aria-label="Nome completo" required autocomplete="name" autofocus>
        <input class="entrada-campo" type="text" id="cpf" name="cpf" value="<?= site_e($d['cpf']) ?>" placeholder="CPF (000.000.000-00)" aria-label="CPF" required inputmode="numeric" maxlength="14">
        <input class="entrada-campo" type="email" name="email" value="<?= site_e($d['email']) ?>" placeholder="E-mail" aria-label="E-mail" required maxlength="100" autocapitalize="off" spellcheck="false" autocomplete="email">
        <input class="entrada-campo" type="tel" id="celular" name="celular" value="<?= site_e($d['celular'] ?? '') ?>" placeholder="Celular com DDD: (00) 00000-0000" aria-label="Celular" required inputmode="tel" maxlength="15" pattern="\D*(\d\D*){10,11}" title="Digite o DDD e o número, por exemplo (21) 99999-9999" autocomplete="tel">
        <input class="entrada-campo" type="password" name="senha" placeholder="Senha (mínimo de 6 caracteres)" aria-label="Senha" required minlength="6" autocomplete="new-password">
        <button class="entrada-botao">Criar conta</button>
      </form>
      <p class="entrada-troca">Já tem conta? <a href="index.php?controller=site&amp;action=login<?= $voltar ? '&amp;voltar=' . $voltar : '' ?>">Entrar</a></p>
    </div>

    <nav class="entrada-rodape" aria-label="Outros acessos">
      <a href="index.php?controller=site&amp;action=home">Voltar ao site</a>
      <a href="index.php?controller=auth&amp;action=form">Área do funcionário</a>
    </nav>
  </section>
  <aside class="entrada-arte" aria-hidden="true"><img src="public/assets/uploads/cadastro-pontilhado.png" alt=""></aside>
</main>
<script>
// máscara do celular: (00) 00000-0000
document.getElementById('celular').addEventListener('input', function () {
  var d = this.value.replace(/\D/g, '').slice(0, 11), corte = d.length > 10 ? 7 : 6, s = '';
  if (d.length > 0) s = '(' + d.slice(0, 2);
  if (d.length >= 3) s += ') ' + d.slice(2, corte);
  if (d.length > corte) s += '-' + d.slice(corte);
  this.value = s;
});
</script>
</body>
</html>