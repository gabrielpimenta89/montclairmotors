<?php
class Relatorio
{
    private PDO $pdo;

    public function __construct()
    {
        // Mesma conexão do models/entrada.php (padrão do XAMPP). Ajuste aqui se o seu for diferente.
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=montclair_motors;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }

    private function todos(string $sql, array $p = []): array
    {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    private function um(string $sql, array $p = []): array
    {
        return $this->todos($sql, $p)[0] ?? [];
    }

    /** Reúne todos os números da página. $de e $ate no formato AAAA-MM-DD. */
    public function gerar(string $de, string $ate): array
    {
        $p          = [':de' => $de, ':ate' => $ate];
        $noPeriodo  = 'v.data_venda BETWEEN :de AND :ate';
        $naoVendido = 'NOT EXISTS (SELECT 1 FROM vendas x WHERE x.id_carro = c.id_carro)';
        $vendidoSql = 'EXISTS (SELECT 1 FROM vendas x WHERE x.id_carro = c.id_carro)';
        $doPeriodo  = "FROM vendas v JOIN carros c ON c.id_carro = v.id_carro WHERE $noPeriodo";

        // 1) resumo e comparação com o período anterior (mesma duração, logo antes)
        $resumoSql = "SELECT COUNT(*) qtd, COALESCE(SUM(v.valor), 0) total FROM vendas v WHERE $noPeriodo";
        $resumo = $this->um($resumoSql, $p);
        $dias   = (int)((strtotime($ate) - strtotime($de)) / 86400) + 1;
        $ant    = null;
        if ($de > '2001-01-01') {
            $pAte = date('Y-m-d', strtotime($de . ' -1 day'));
            $pDe  = date('Y-m-d', strtotime($pAte . ' -' . ($dias - 1) . ' days'));
            $ant  = $this->um($resumoSql, [':de' => $pDe, ':ate' => $pAte]);
        }

        // 2) vendas ao longo do tempo (por dia se o período for curto)
        $porDia = $dias <= 62;
        $fmt    = $porDia ? '%Y-%m-%d' : '%Y-%m';
        $serie  = $this->preencher($this->todos(
            "SELECT DATE_FORMAT(v.data_venda, '$fmt') k, COUNT(*) qtd, SUM(v.valor) total
             FROM vendas v WHERE $noPeriodo GROUP BY k ORDER BY k", $p), $porDia);

        // 3) vendedores
        $vend = $this->todos(
            "SELECT u.nome nome, COUNT(*) qtd, SUM(v.valor) total
             FROM vendas v JOIN usuario u ON u.id = v.id_usuario
             WHERE $noPeriodo GROUP BY u.id, u.nome ORDER BY total DESC", $p);

        // 4) formas de pagamento e valores pendentes
        $pag = $this->todos(
            "SELECT COALESCE(p.forma_pagamento, 'Não informado') forma, COUNT(*) qtd, SUM(v.valor) total
             FROM pagamentos p JOIN vendas v ON v.id_venda = p.id_venda
             WHERE $noPeriodo GROUP BY forma ORDER BY total DESC", $p);
        $pend = $this->um(
            "SELECT COUNT(*) qtd, COALESCE(SUM(v.valor), 0) total
             FROM pagamentos p JOIN vendas v ON v.id_venda = p.id_venda
             WHERE $noPeriodo AND p.status_pagamento = 'pendente'", $p);

        // 5) estoque atual (não depende do período)
        $estoque = $this->um("SELECT COUNT(*) qtd, COALESCE(SUM(c.preco), 0) total FROM carros c WHERE $naoVendido");
        $parados = $this->todos(
            "SELECT c.id_carro, c.marca, c.modelo, c.ano, c.preco, c.imagem, es.data_entrada,
                    DATEDIFF(CURDATE(), es.data_entrada) dias
             FROM carros c LEFT JOIN estoque es ON es.id_carro = c.id_carro
             WHERE $naoVendido
             ORDER BY es.data_entrada IS NULL, es.data_entrada ASC LIMIT 8");
        $porMarca = $this->todos("SELECT c.marca nome, COUNT(*) qtd FROM carros c WHERE $naoVendido GROUP BY c.marca ORDER BY qtd DESC, nome");
        $porComb  = $this->todos(
            "SELECT COALESCE(NULLIF(c.combustivel, ''), 'Não informado') nome, COUNT(*) qtd
             FROM carros c WHERE $naoVendido GROUP BY nome ORDER BY qtd DESC, nome");

        // 6) demanda dos clientes (não depende do período)
        $procurados = $this->todos(
            "SELECT c.id_carro, c.marca, c.modelo, c.imagem, COUNT(i.id_interesse) interessados, $vendidoSql vendido
             FROM interesse i JOIN carros c ON c.id_carro = i.id_carro
             GROUP BY c.id_carro, c.marca, c.modelo, c.imagem
             ORDER BY interessados DESC, c.id_carro DESC LIMIT 6");
        $demanda = $this->um(
            "SELECT COUNT(DISTINCT i.id_cliente) interessados,
                    COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM vendas x WHERE x.id_cliente = i.id_cliente) THEN i.id_cliente END) compraram
             FROM interesse i");

        // 7) mais vendidos no período
        $marcas  = $this->todos("SELECT c.marca nome, COUNT(*) qtd $doPeriodo GROUP BY c.marca ORDER BY qtd DESC, nome LIMIT 5", $p);
        $modelos = $this->todos("SELECT CONCAT(c.marca, ' ', c.modelo) nome, COUNT(*) qtd $doPeriodo GROUP BY c.marca, c.modelo ORDER BY qtd DESC, nome LIMIT 5", $p);
        $nomesFaixa = [1 => 'Até R$ 100 mil', 2 => 'R$ 100 a 300 mil', 3 => 'R$ 300 a 600 mil', 4 => 'R$ 600 mil a 1 mi', 5 => 'Acima de R$ 1 mi'];
        $faixas = array_map(fn($r) => ['nome' => $nomesFaixa[(int)$r['f']], 'qtd' => $r['qtd']], $this->todos(
            "SELECT f, COUNT(*) qtd FROM (
                 SELECT CASE WHEN v.valor < 100000 THEN 1 WHEN v.valor < 300000 THEN 2 WHEN v.valor < 600000 THEN 3
                             WHEN v.valor < 1000000 THEN 4 ELSE 5 END f
                 FROM vendas v WHERE $noPeriodo AND v.valor IS NOT NULL) t
             GROUP BY f ORDER BY f", $p));

        return compact('resumo', 'ant', 'porDia', 'serie', 'vend', 'pag', 'pend', 'estoque', 'parados',
                       'porMarca', 'porComb', 'procurados', 'demanda', 'marcas', 'modelos', 'faixas');
    }

    /** Completa com zero os dias/meses sem venda entre a primeira e a última venda. */
    private function preencher(array $linhas, bool $porDia): array
    {
        if (!$linhas) return [];
        $mapa  = array_column($linhas, null, 'k');
        $meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        $sufixo = $porDia ? '' : '-01';
        $t   = strtotime(array_key_first($mapa) . $sufixo);
        $fim = strtotime(array_key_last($mapa) . $sufixo);
        $saida = [];
        while ($t <= $fim && count($saida) < 400) {
            $k = date($porDia ? 'Y-m-d' : 'Y-m', $t);
            $l = $mapa[$k] ?? ['qtd' => 0, 'total' => 0];
            $saida[] = [
                'rotulo' => $porDia ? date('d/m', $t) : $meses[(int)date('n', $t) - 1] . '/' . date('y', $t),
                'qtd'    => (int)$l['qtd'],
                'total'  => (float)$l['total'],
            ];
            $t = strtotime($porDia ? '+1 day' : '+1 month', $t);
        }
        return $saida;
    }
}