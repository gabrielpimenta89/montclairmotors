<?php $titulo = 'Criar conta | Montclair Motors'; $semCabecalho = true; require __DIR__ . '/site_topo.php'; ?>
<main class="entrada">
  <section class="entrada-form">
    <a class="marca" href="index.php?controller=site&amp;action=home">Montclair<span>Motors</span></a>

    <div class="entrada-corpo">
      <h1>Criar conta</h1>
      <p class="entrada-sub">Cadastre-se para registrar interesse nos carros.</p>
      <?php if ($erro): ?><div class="aviso" role="alert"><?= site_e($erro) ?></div><?php endif; ?>
      <form method="post" action="index.php?controller=site&amp;action=cadastro">
        <input type="hidden" name="csrf" value="<?= site_csrf() ?>">
        <input type="hidden" name="voltar" value="<?= $voltar ?>">
        <input class="entrada-campo" type="text" name="nome" value="<?= site_e($d['nome']) ?>" placeholder="Nome completo" aria-label="Nome completo" required autocomplete="name" autofocus>
        <input class="entrada-campo" type="text" id="cpf" name="cpf" value="<?= site_e($d['cpf']) ?>" placeholder="CPF (000.000.000-00)" aria-label="CPF" required inputmode="numeric" maxlength="14">
        <input class="entrada-campo" type="email" name="email" value="<?= site_e($d['email']) ?>" placeholder="E-mail" aria-label="E-mail" required autocomplete="email">
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
</body>
</html>