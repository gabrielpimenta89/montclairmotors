<?php $titulo = 'Entrar | Montclair Motors'; $semCabecalho = true; require __DIR__ . '/site_topo.php'; ?>
<main class="entrada">
  <section class="entrada-form">
    <a class="marca" href="index.php?controller=site&amp;action=home">Montclair<span>Motors</span></a>

    <div class="entrada-corpo">
      <h1>Bem-vindo de volta!</h1>
      <p class="entrada-sub"><?= $voltar ? 'Entre para registrar seu interesse neste carro.' : 'Seus carros favoritos, num só lugar.' ?></p>
      <?php if ($erro): ?><div class="aviso" role="alert"><?= site_e($erro) ?></div><?php endif; ?>
      <form method="post" action="index.php?controller=site&amp;action=login">
        <input type="hidden" name="csrf" value="<?= site_csrf() ?>">
        <input type="hidden" name="voltar" value="<?= $voltar ?>">
        <input class="entrada-campo" type="email" name="email" placeholder="Digite seu e-mail" aria-label="E-mail" required autocomplete="email" autofocus>
        <input class="entrada-campo" type="password" name="senha" placeholder="Digite sua senha" aria-label="Senha" required autocomplete="current-password">
        <button class="entrada-botao">Entrar</button>
      </form>
      <p class="entrada-troca">Ainda não tem conta? <a href="index.php?controller=site&amp;action=cadastro<?= $voltar ? '&amp;voltar=' . $voltar : '' ?>">Criar conta</a></p>
    </div>

    <nav class="entrada-rodape" aria-label="Outros acessos">
      <a href="index.php?controller=site&amp;action=home">Voltar ao site</a>
      <a href="index.php?controller=auth&amp;action=form">Área do funcionário</a>
    </nav>
  </section>
  <aside class="entrada-arte" aria-hidden="true"><img src="public/assets/uploads/login-pontilhado.png" alt=""></aside>
</main>
</body>
</html>