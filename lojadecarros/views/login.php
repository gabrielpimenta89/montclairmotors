<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="public/assets/css/equipe.css">

    <title>Área do funcionário | Montclair Motors</title>

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
                <h1>Bem-vindo de <em>volta!</em></h1>
                <p class="sub">Acesse o painel de vendas e estoque da loja.</p>

                <form method="post" action="index.php?controller=auth&action=login">

                    <input class="campo" type="email" id="email" name="email" placeholder="Digite seu e-mail" aria-label="E-mail" required autofocus>

                    <input class="campo" type="password" id="senha" name="senha" placeholder="Digite sua senha" aria-label="Senha" required>

                    <button type="submit" class="botao">Entrar</button>

                </form>

                <p class="troca">Ainda não tem acesso? <a href="index.php?controller=usuario&action=create">Cadastrar vendedor</a></p>

            </div>

            <nav class="equipe-rodape" aria-label="Outros acessos">
                <a href="index.php?controller=site&action=home">Voltar ao site</a>
                <a href="index.php?controller=site&action=login">Área do cliente</a>
            </nav>

        </section>

        <aside class="equipe-arte" aria-hidden="true">
            <img src="public/assets/uploads/equipe-login.png" alt="">
            <p>Excelência em movimento</p>
        </aside>

    </main>

</body>

</html>