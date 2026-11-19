
<?php
require_once "../config/conexao.php";

$data = $_GET["data"] ?? "";
$fretes = [];

if ($data !== "") {
    $sql = "SELECT
                f.codigo,
                f.data_frete,
                o.identificacao AS origem,
                d.identificacao AS destino,
                t.nome AS transportador,
                f.status,
                GROUP_CONCAT(
                    CONCAT(p.nome, ' (Quantidade: ', fp.quantidade, ')')
                    SEPARATOR ', '
                ) AS produtos
            FROM fretes f
            INNER JOIN enderecos o ON f.origem_id = o.id
            INNER JOIN enderecos d ON f.destino_id = d.id
            INNER JOIN transportadores t ON f.transportador_id = t.id
            LEFT JOIN frete_produtos fp ON f.id = fp.frete_id
            LEFT JOIN produtos p ON fp.produto_id = p.id
            WHERE f.data_frete = :data
            GROUP BY f.id
            ORDER BY f.codigo";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":data" => $data]);
    $fretes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET["baixar"]) && $_GET["baixar"] === "1") {
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=relatorio_fretes.csv");

    $saida = fopen("php://output", "w");

    // BOM para abrir corretamente caracteres acentuados no Excel
    fwrite($saida, "\xEF\xBB\xBF");

    fputcsv($saida, [
        "Código",
        "Data",
        "Origem",
        "Destino",
        "Transportador",
        "Status",
        "Produtos"
    ], ";");

    foreach ($fretes as $frete) {
        fputcsv($saida, [
            $frete["codigo"],
            $frete["data_frete"],
            $frete["origem"],
            $frete["destino"],
            $frete["transportador"],
            $frete["status"],
            $frete["produtos"] ?? ""
        ], ";");
    }

    fclose($saida);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - LogiControl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h2 class="mb-4">Relatório de Fretes</h2>

    <a href="../index.php" class="btn btn-secondary mb-3">Voltar ao início</a>

    <div class="card p-4 mb-4">
        <h5>Consultar fretes por data</h5>

        <form method="GET" class="row align-items-end">
            <div class="col-md-6 mb-3">
                <label class="form-label">Selecione a data</label>
                <input type="date" name="data" class="form-control"
                       value="<?= htmlspecialchars($data) ?>" required>
            </div>

            <div class="col-md-3 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    Consultar
                </button>
            </div>
        </form>
    </div>

    <?php if ($data !== ""): ?>

        <h5>Resultado da consulta</h5>

        <?php if (count($fretes) > 0): ?>

            <p>Foram encontrados <?= count($fretes) ?> frete(s).</p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped bg-white">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Data</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Transportador</th>
                            <th>Status</th>
                            <th>Produtos</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($fretes as $frete): ?>
                            <tr>
                                <td><?= htmlspecialchars($frete["codigo"]) ?></td>
                                <td><?= htmlspecialchars($frete["data_frete"]) ?></td>
                                <td><?= htmlspecialchars($frete["origem"]) ?></td>
                                <td><?= htmlspecialchars($frete["destino"]) ?></td>
                                <td><?= htmlspecialchars($frete["transportador"]) ?></td>
                                <td><?= htmlspecialchars($frete["status"]) ?></td>
                                <td><?= htmlspecialchars($frete["produtos"] ?? "") ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <a href="index.php?data=<?= urlencode($data) ?>&baixar=1"
               class="btn btn-success">
                Baixar relatório CSV
            </a>

        <?php else: ?>

            <div class="alert alert-warning">
                Nenhum frete encontrado para essa data.
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
