<?php
class Interessado
{
    public const STATUS = [
        'novo'       => 'Novo',
        'em_contato' => 'Em contato',
        'negociando' => 'Negociando',
        'fechado'    => 'Fechado',
        'descartado' => 'Descartado',
    ];

    private PDO $pdo;

    public function __construct()
    {
        // Usa a conexão do projeto (config/db.php); se ela não for um PDO, abre a padrão do XAMPP.
        $conexao = class_exists('Database') ? Database::getConnection() : null;
        $this->pdo = $conexao instanceof PDO ? $conexao : new PDO('mysql:host=127.0.0.1;dbname=montclair_motors;charset=utf8mb4', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Interesses do mais urgente ao menos: carros à venda primeiro, novos primeiro, mais recentes primeiro. */
    public function listar(string $status = ''): array
    {
        $sql = 'SELECT i.id_interesse, i.status, i.observacao, i.data_registro, i.atualizado_em,
                       TIMESTAMPDIFF(MINUTE, i.data_registro, NOW()) minutos,
                       cl.nome, cl.email, cl.celular,
                       c.marca, c.modelo, c.ano, c.preco, c.imagem,
                       EXISTS (SELECT 1 FROM vendas x WHERE x.id_carro = c.id_carro) vendido
                FROM interesse i
                JOIN clientes cl ON cl.id_cliente = i.id_cliente
                JOIN carros c ON c.id_carro = i.id_carro'
             . ($status !== '' ? ' WHERE i.status = :s' : '')
             . ' ORDER BY vendido, (i.status = \'novo\') DESC, i.data_registro DESC';
        $st = $this->pdo->prepare($sql);
        $st->execute($status !== '' ? [':s' => $status] : []);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Quantidade por status, mais o total em 'todos'. */
    public function contagem(): array
    {
        $r = array_fill_keys(array_keys(self::STATUS), 0);
        foreach ($this->pdo->query('SELECT status, COUNT(*) n FROM interesse GROUP BY status')->fetchAll(PDO::FETCH_ASSOC) as $l) {
            $r[$l['status']] = (int)$l['n'];
        }
        $r['todos'] = array_sum($r);
        return $r;
    }

    public function totalNovos(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM interesse WHERE status = 'novo'")->fetchColumn();
    }

    public function atualizar(int $id, string $status, string $observacao): void
    {
        $st = $this->pdo->prepare('UPDATE interesse SET status = :s, observacao = :o, atualizado_em = NOW() WHERE id_interesse = :id');
        $st->execute([':s' => $status, ':o' => $observacao !== '' ? $observacao : null, ':id' => $id]);
    }
}