<?php
class Entrada
{
    private PDO $pdo;

    public function __construct()
    {
        // Usa a conexão do projeto (config/db.php); se ela não for um PDO, abre a padrão do XAMPP.
        $conexao = class_exists('Database') ? Database::getConnection() : null;
        $this->pdo = $conexao instanceof PDO ? $conexao : new PDO('mysql:host=127.0.0.1;dbname=montclair_motors;charset=utf8mb4', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Entradas do mais recente ao mais antigo (carros sem data vão para o fim). */
    public function listar(): array
    {
        $sql = 'SELECT es.id_estoque AS id_entrada, es.data_entrada, c.*,
                       EXISTS (SELECT 1 FROM vendas v WHERE v.id_carro = c.id_carro) AS vendido
                FROM estoque es
                JOIN carros c ON c.id_carro = es.id_carro
                ORDER BY es.data_entrada IS NULL, es.data_entrada DESC, es.id_estoque DESC';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}