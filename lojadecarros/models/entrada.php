<?php
class Entrada
{
    private PDO $pdo;

    public function __construct()
    {
        // Ajuste aqui se o seu MySQL tiver outro usuário/senha (padrão do XAMPP: root, sem senha)
        // ou troque por a conexão que o resto do projeto já usa.
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=montclair_motors;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }

    /** Entradas do mais recente ao mais antigo (carros sem data vão para o fim). */
    public function listar(): array
    {
        $sql = 'SELECT es.id_estoque AS id_entrada, es.data_entrada, c.*,
                       EXISTS (SELECT 1 FROM vendas v WHERE v.id_carro = c.id_carro) AS vendido
                FROM estoque es
                JOIN carros c ON c.id_carro = es.id_carro
                ORDER BY es.data_entrada IS NULL, es.data_entrada DESC, es.id_estoque DESC';
        return $this->pdo->query($sql)->fetchAll();
    }
}