<?php
require_once __DIR__ . '/../models/produto.php';
require_once __DIR__ . '/../models/cliente_site.php';
require_once __DIR__ . '/../models/interesse.php';

// Taxa de juros mensal (%) usada no simulador de financiamento: ajuste para a taxa real da loja
const SITE_TAXA_MENSAL = 1.5;

// Funções de apoio das telas públicas (prefixo site_ para não colidir com o resto do projeto)
function site_e($v): string          { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function site_nome(array $c): string { return $c['marca'] . ' ' . $c['modelo']; }
function site_preco($v): string      { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }
function site_csrf(): string         { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }

// Especificações públicas do carro (o chassi não aparece no site)
function site_specs(array $c): array
{
    return array_filter([
        'Ano'           => $c['ano'],
        'Quilometragem' => $c['km'] === null ? null : number_format((int)$c['km'], 0, ',', '.') . ' km',
        'Combustível'   => $c['combustivel'],
        'Cor'           => $c['cor'],
    ], fn($v) => $v !== null && $v !== '');
}

function site_cpf_valido(string $cpf): bool
{
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($soma = 0, $i = 0; $i < $t; $i++) $soma += (int)$cpf[$i] * (($t + 1) - $i);
        if ((int)$cpf[$t] !== ((10 * $soma) % 11) % 10) return false;
    }
    return true;
}

// Provedores conhecidos: já se sabe que existem, então não precisam de consulta ao DNS
const SITE_EMAIL_CONHECIDOS = [
    'gmail.com', 'hotmail.com', 'hotmail.com.br', 'outlook.com', 'outlook.com.br', 'live.com', 'msn.com',
    'yahoo.com', 'yahoo.com.br', 'icloud.com', 'me.com', 'uol.com.br', 'bol.com.br', 'terra.com.br',
    'ig.com.br', 'globo.com', 'globomail.com', 'proton.me', 'protonmail.com',
];

// Erros de digitação comuns => domínio que a pessoa provavelmente queria
const SITE_EMAIL_ERROS = [
    'gmial.com' => 'gmail.com', 'gmai.com' => 'gmail.com', 'gamil.com' => 'gmail.com', 'gnail.com' => 'gmail.com',
    'gmail.co' => 'gmail.com', 'gmail.con' => 'gmail.com', 'gmail.com.br' => 'gmail.com', 'gmall.com' => 'gmail.com',
    'hotmial.com' => 'hotmail.com', 'hotmai.com' => 'hotmail.com', 'hotmal.com' => 'hotmail.com',
    'hotmail.co' => 'hotmail.com', 'hotmail.con' => 'hotmail.com',
    'outlok.com' => 'outlook.com', 'outloo.com' => 'outlook.com', 'outlook.con' => 'outlook.com',
    'yaho.com' => 'yahoo.com', 'yahooo.com' => 'yahoo.com', 'yahoo.con' => 'yahoo.com',
    'iclod.com' => 'icloud.com', 'icloud.con' => 'icloud.com',
];

// E-mails temporários (descartáveis) e domínios de teste
const SITE_EMAIL_DESCARTAVEIS = [
    'mailinator.com', 'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org', 'guerrillamail.biz',
    'guerrillamail.de', 'sharklasers.com', 'grr.la', '10minutemail.com', '10minutemail.net', 'tempmail.com',
    'temp-mail.org', 'temp-mail.io', 'tempmail.net', 'yopmail.com', 'yopmail.fr', 'trashmail.com',
    'throwawaymail.com', 'getnada.com', 'dispostable.com', 'fakeinbox.com', 'maildrop.cc', 'mailnesia.com',
    'mintemail.com', 'mohmal.com', 'emailondeck.com', 'spamgourmet.com', 'tempinbox.com', 'moakt.com',
    'burnermail.io', 'mailcatch.com', 'tempail.com', 'discard.email', 'inboxkitten.com', 'mytemp.email',
    'tmpmail.org', 'tmpmail.net', 'example.com', 'example.org', 'example.net', 'test.com', 'teste.com',
    'teste.com.br', 'exemplo.com', 'exemplo.com.br',
];

// O domínio recebe e-mail? (registro MX; na falta dele, o endereço A/AAAA do domínio)
function site_dominio_recebe_email(string $dominio): bool
{
    if (!function_exists('checkdnsrr')) return true;
    if (checkdnsrr($dominio, 'MX') || checkdnsrr($dominio, 'A') || checkdnsrr($dominio, 'AAAA')) return true;
    // Se nem o DNS de um site gigante responde, o computador está sem internet: não dá para saber,
    // então não barra o cliente por isso.
    return !checkdnsrr('google.com', 'MX');
}

// Confere o e-mail do cadastro. Devolve a mensagem de erro, ou null se estiver tudo certo.
function site_email_erro(string $email): ?string
{
    // nível 1: formato
    if ($email === '' || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Digite um e-mail válido.';
    }
    $dominio = substr(strrchr($email, '@'), 1);
    $usuario = substr($email, 0, -(strlen($dominio) + 1));

    // nível 2: erro de digitação, e-mail temporário e domínio que não existe
    if (isset(SITE_EMAIL_ERROS[$dominio])) {
        return 'Este e-mail parece ter um erro. Você quis dizer ' . $usuario . '@' . SITE_EMAIL_ERROS[$dominio] . '?';
    }
    foreach (SITE_EMAIL_DESCARTAVEIS as $bloqueado) {
        if ($dominio === $bloqueado || str_ends_with($dominio, '.' . $bloqueado)) {
            return 'E-mails temporários não são aceitos. Use o seu e-mail pessoal.';
        }
    }
    if (!in_array($dominio, SITE_EMAIL_CONHECIDOS, true) && !site_dominio_recebe_email($dominio)) {
        return 'Não encontramos o domínio "' . $dominio . '". Confira o e-mail digitado.';
    }
    return null;
}

// Celular brasileiro com DDD: devolve "(21) 99999-9999", ou null se for inválido
function site_celular_formatado(string $texto): ?string
{
    $n = preg_replace('/\D/', '', $texto);
    if (!in_array(strlen($n), [10, 11], true) || preg_match('/^(\d)\1+$/', $n)) return null;

    $ddds = array_merge(range(11, 19), [21, 22, 24, 27, 28], range(31, 35), [37, 38], range(41, 49), [51, 53, 54, 55],
                        range(61, 69), [71, 73, 74, 75, 77, 79], range(81, 89), range(91, 99));
    if (!in_array((int)substr($n, 0, 2), $ddds, true)) return null;

    $primeiro = $n[2];                       // celular começa com 9; telefone fixo, com 2 a 5
    if (strlen($n) === 11 ? $primeiro !== '9' : !in_array($primeiro, ['2', '3', '4', '5'], true)) return null;

    $corte = strlen($n) === 11 ? 7 : 6;
    return '(' . substr($n, 0, 2) . ') ' . substr($n, 2, $corte - 2) . '-' . substr($n, $corte);
}

class SiteController
{
    private Produto $produto;
    private ClienteSite $clientes;

    public function __construct()
    {
        $this->produto  = new Produto();
        $this->clientes = new ClienteSite();
    }

    // Vitrine: os mesmos carros disponíveis da tela de produtos (carros já vendidos ficam de fora)
    public function home(): void
    {
        $carros = $this->produto->listarTodos();
        require __DIR__ . '/../views/site_home.php';
    }

    // Página independente com todos os carros disponíveis (e busca por modelo)
    public function carros(): void
    {
        $carros = $this->produto->listarTodos();
        // ids que o cliente logado já marcou com o coração
        $marcados = isset($_SESSION['cliente_id'])
            ? array_map('intval', array_column((new Interesse())->doCliente((int)$_SESSION['cliente_id']), 'id_carro'))
            : [];
        require __DIR__ . '/../views/site_carros.php';
    }

    // index.php?controller=site&action=detalhes&id=3
    public function detalhes(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $carro = $this->carro($id);
        if (!$carro) http_response_code(404);
        $interessado = $carro && isset($_SESSION['cliente_id'])
            && $this->clientes->temInteresse((int)$_SESSION['cliente_id'], $id);
        // até 3 outros carros disponíveis para a seção "Você também pode gostar"
        $outros = array_slice(array_values(array_filter(
            $this->produto->listarTodos(),
            fn($c) => (int)$c['id_carro'] !== $id
        )), 0, 3);
        require __DIR__ . '/../views/site_detalhes.php';
    }

    // POST: grava o interesse do cliente logado na tabela interesse
    public function interesse(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->ir('home');
        $this->conferirCsrf();
        $id = (int)($_POST['id_carro'] ?? 0);
        if (!isset($_SESSION['cliente_id'])) $this->negar("login&voltar=$id");
        if ($this->carro($id)) $this->clientes->registrarInteresse((int)$_SESSION['cliente_id'], $id);
        $this->responder("detalhes&id=$id");
    }

    // Página do cliente: carros em que ele marcou interesse
    public function interesses(): void
    {
        if (!isset($_SESSION['cliente_id'])) $this->ir('login');
        $lista = (new Interesse())->doCliente((int)$_SESSION['cliente_id']);
        require __DIR__ . '/../views/site_interesses.php';
    }

    // POST: tira o carro da lista de interesses do cliente
    public function removerInteresse(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->ir('home');
        if (!isset($_SESSION['cliente_id'])) $this->negar('login');
        $this->conferirCsrf();
        (new Interesse())->remover((int)$_SESSION['cliente_id'], (int)($_POST['id_carro'] ?? 0));
        $this->responder('interesses');
    }

    public function login(): void
    {
        $erro = null;
        $voltar = (int)($_POST['voltar'] ?? $_GET['voltar'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->conferirCsrf();
            $c = $this->clientes->buscarPorEmail(trim($_POST['email'] ?? ''));
            if ($c && !empty($c['senha']) && password_verify($_POST['senha'] ?? '', $c['senha'])) {
                $this->entrar($c, $voltar);
            }
            $erro = 'E-mail ou senha incorretos.';
        }
        require __DIR__ . '/../views/site_login.php';
    }

    public function cadastro(): void
    {
        $erro = null;
        $voltar = (int)($_POST['voltar'] ?? $_GET['voltar'] ?? 0);
        $d = ['nome' => '', 'cpf' => '', 'email' => '', 'celular' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->conferirCsrf();
            $d = [
                'nome'    => trim($_POST['nome'] ?? ''),
                'cpf'     => preg_replace('/\D/', '', $_POST['cpf'] ?? ''),
                'email'   => strtolower(trim($_POST['email'] ?? '')),
                'celular' => trim($_POST['celular'] ?? ''),
            ];
            $senha = $_POST['senha'] ?? '';
            // mesmo formato que a tela de vendas grava (000.000.000-00)
            $cpf = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $d['cpf']);
            $existente = $this->clientes->buscarPorCpf($cpf);
            $celular = site_celular_formatado($d['celular']);

            if ($d['nome'] === '' || strlen($senha) < 6) {
                $erro = 'Informe seu nome e uma senha com pelo menos 6 caracteres.';
            } elseif (!site_cpf_valido($d['cpf'])) {
                $erro = 'CPF inválido. Confira os números digitados.';
            } elseif (($erro = site_email_erro($d['email'])) !== null) {
                // $erro já recebeu a mensagem do e-mail
            } elseif ($celular === null) {
                $erro = 'Digite um celular válido com DDD, por exemplo (21) 99999-9999.';
            } elseif ($this->clientes->buscarPorEmail($d['email']) || ($existente && !empty($existente['senha']))) {
                $erro = 'Já existe uma conta com este e-mail ou CPF. Entre com a sua senha.';
            } else {
                $id = $this->clientes->cadastrar($d['nome'], $cpf, $d['email'], password_hash($senha, PASSWORD_DEFAULT));
                $this->salvarCelular((int)$id, $celular);
                $this->entrar(['id_cliente' => $id, 'nome' => $existente['nome'] ?? $d['nome']], $voltar);
            }
        }
        require __DIR__ . '/../views/site_cadastro.php';
    }

    // Sai só da conta de cliente (não mexe na sessão da equipe da loja)
    public function sair(): void
    {
        unset($_SESSION['cliente_id'], $_SESSION['cliente_nome']);
        $this->ir('home');
    }

    // Grava o celular na tabela clientes (a coluna já existe)
    private function salvarCelular(int $id, string $celular): void
    {
        try {
            Database::getConnection()
                ->prepare('UPDATE clientes SET celular = :c WHERE id_cliente = :id')
                ->execute([':c' => $celular, ':id' => $id]);
        } catch (Throwable $e) {
            error_log('Cadastro (celular): ' . $e->getMessage());
        }
    }

    // Só carros disponíveis, com a mesma regra da vitrine
    private function carro(int $id): ?array
    {
        foreach ($this->produto->listarTodos() as $c) {
            if ((int)$c['id_carro'] === $id) return $c;
        }
        return null;
    }

    private function entrar(array $c, int $voltar): void
    {
        session_regenerate_id(true);
        $_SESSION['cliente_id']   = (int)$c['id_cliente'];
        $_SESSION['cliente_nome'] = $c['nome'];
        $this->ir($voltar ? "detalhes&id=$voltar" : 'home');
    }

    // Chamadas do JavaScript (fetch) recebem JSON; formulários normais seguem com redirecionamento
    private function ajax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
    }

    private function responder(string $destino): void
    {
        if ($this->ajax()) {
            header('Content-Type: application/json');
            echo '{"ok":true}';
            exit;
        }
        $this->ir($destino);
    }

    private function negar(string $destino): void
    {
        if ($this->ajax()) {
            http_response_code(401);
            exit;
        }
        $this->ir($destino);
    }

    private function ir(string $destino): void
    {
        header("Location: index.php?controller=site&action=$destino");
        exit;
    }

    private function conferirCsrf(): void
    {
        $token = $_SESSION['csrf'] ?? '';
        if ($token === '' || !hash_equals($token, (string)($_POST['csrf'] ?? ''))) {
            http_response_code(400);
            die('Sessão expirada. Volte à página anterior e tente novamente.');
        }
    }
}