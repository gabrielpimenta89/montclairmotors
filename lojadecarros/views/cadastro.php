<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="public/assets/css/equipe.css">

    <title>Cadastrar vendedor | Montclair Motors</title>

    <link rel="icon" type="image/png" sizes="512x512" href="public/assets/css/favicon.png">
</head>

<body>

    <main class="equipe">

        <section class="equipe-form">

            <div class="equipe-corpo">

                <a class="logo-selo" href="index.php?controller=site&action=home">
                    <img src="public/assets/uploads/logo2.png" alt="Logo da loja">
                </a>

                <span class="etiqueta">Área do funcionário</span>
                <h1>Cadastro de <em>vendedor</em></h1>
                <p class="sub">Crie o acesso de um novo vendedor ao painel da loja.</p>

                <form action="index.php?controller=usuario&action=store" method="POST">

                    <input class="campo" type="text" id="nome" name="nome" placeholder="Nome completo" aria-label="Nome" required autofocus>

                    <input class="campo" type="email" id="email" name="email" placeholder="E-mail" aria-label="E-mail" required>

                    <input class="campo" type="password" id="senha" name="senha" placeholder="Senha" aria-label="Senha" required>

                    <button type="submit" class="botao">Cadastrar</button>

                </form>

                <p class="troca">Já tem acesso? <a href="index.php?controller=auth&action=form">Entrar</a></p>

            </div>

            <nav class="equipe-rodape" aria-label="Outros acessos">
                <a href="index.php?controller=site&action=home">Voltar ao site</a>
                <a href="index.php?controller=site&action=login">Área do cliente</a>
            </nav>

        </section>

        <aside class="equipe-arte" aria-hidden="true">
            <img src="public/assets/uploads/equipe-cadastro.png" alt="">
            <p>Excelência em movimento</p>
        </aside>

    </main>

</body>

</html>