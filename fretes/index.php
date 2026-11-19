
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {
        $pdo->beginTransaction();

        $codigo = $_POST["codigo"];
        $data = $_POST["data_frete"];
        $origem = $_POST["origem_id"];
        $destino = $_POST["destino_id"];
        $transportador = $_POST["transportador_id"];
        $status = $_POST["status"];
        $observacoes = $_POST["observacoes"];

        $sql = "INSERT INTO fretes
        (codigo, data_frete, origem_id, destino_id, transportador_id, status, observacoes)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $codigo,
            $data,
            $origem,
            $destino,
            $transportador,
            $status,
            $observacoes
        ]);

        $freteId = $pdo->lastInsertId();

        if (isset($_POST["produtos"]) && is_array($_POST["produtos"])) {

            $sqlProduto = "INSERT INTO frete_produtos
            (frete_id, produto_id, quantidade)
            VALUES (?, ?, ?)";

            $stmtProduto = $pdo->prepare($sqlProduto);

            foreach ($_POST["produtos"] as $produto) {
                if (!empty($produto["id"]) && !empty($produto["quantidade"])) {
                    $stmtProduto->execute([
                        $freteId,
                        $produto["id"],
                        $produto["quantidade"]
                    ]);
                }
            }
        }

        $pdo->commit();
        $mensagem = "Frete cadastrado com sucesso!";

    } catch (Exception $e) {
        $pdo->rollBack();
        $mensagem = "Erro ao cadastrar frete: " . $e->getMessage();
    }
}

$enderecos = $pdo->query("SELECT * FROM enderecos ORDER BY identificacao")->fetchAll(PDO::FETCH_ASSOC);
$transportadores = $pdo->query("SELECT * FROM transportadores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

$fretes = $pdo->query("
    SELECT f.*, 
    o.identificacao AS origem,
    d.identificacao AS destino,
    t.nome AS transportador
    FROM fretes f
    INNER JOIN enderecos o ON f.origem_id = o.id
    INNER JOIN enderecos d ON f.destino_id = d.id
    INNER JOIN transportadores t ON f.transportador_id = t.id
    ORDER BY f.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gerenciamento de Fretes - LogiControl</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<h2 class="mb-4">Gerenciamento de Fretes</h2>

<a href="../index.php" class="btn btn-secondary mb-3">Voltar ao início</a>

<?php if ($mensagem): ?>
<div class="alert alert-info">
<?= htmlspecialchars($mensagem) ?>
</div>
<?php endif; ?>

<div class="card p-4 mb-4">

<h4 class="mb-3">Cadastrar Novo Frete</h4>

<form method="POST">

<div class="row">

<div class="col-md-4 mb-3">
<label class="form-label">Código do Frete</label>
<input type="text" name="codigo" class="form-control" required>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Data do Frete</label>
<input type="date" name="data_frete" class="form-control" required>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Status</label>
<select name="status" class="form-select" required>
<option value="Pendente">Pendente</option>
<option value="Em andamento">Em andamento</option>
<option value="Concluído">Concluído</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Origem</label>
<select name="origem_id" class="form-select" required>
<option value="">Selecione a origem</option>
<?php foreach ($enderecos as $endereco): ?>
<option value="<?= $endereco["id"] ?>">
<?= htmlspecialchars($endereco["identificacao"]) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Destino</label>
<select name="destino_id" class="form-select" required>
<option value="">Selecione o destino</option>
<?php foreach ($enderecos as $endereco): ?>
<option value="<?= $endereco["id"] ?>">
<?= htmlspecialchars($endereco["identificacao"]) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-12 mb-3">
<label class="form-label">Transportador</label>
<select name="transportador_id" class="form-select" required>
<option value="">Selecione o transportador</option>
<?php foreach ($transportadores as $transportador): ?>
<option value="<?= $transportador["id"] ?>">
<?= htmlspecialchars($transportador["nome"]) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-12 mb-3">
<label class="form-label">Observações</label>
<textarea name="observacoes" class="form-control" rows="2"></textarea>
</div>

</div>

<hr>

<h5>Produtos do Frete</h5>

<div id="listaProdutos">

<div class="row produto-item mb-3">
<div class="col-md-8">
<label class="form-label">Produto</label>
<select name="produtos[0][id]" class="form-select">
<option value="">Selecione um produto</option>
<?php foreach ($produtos as $produto): ?>
<option value="<?= $produto["id"] ?>">
<?= htmlspecialchars($produto["nome"]) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-4">
<label class="form-label">Quantidade</label>
<input type="number" name="produtos[0][quantidade]" class="form-control" min="1">
</div>
</div>

</div>

<button type="button" class="btn btn-outline-primary mb-3" onclick="adicionarProduto()">
+ Adicionar outro produto
</button>

<br>

<button type="submit" class="btn btn-primary">
Cadastrar Frete
</button>

</form>
</div>

<h4>Fretes cadastrados</h4>

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
</tr>
<?php endforeach; ?>
</tbody>

</table>
</div>

</div>

<script>
let contadorProdutos = 1;

function adicionarProduto() {

    const lista = document.getElementById("listaProdutos");

    const novoProduto = document.createElement("div");

    novoProduto.className = "row produto-item mb-3";

    novoProduto.innerHTML = `
        <div class="col-md-8">
            <label class="form-label">Produto</label>
            <select name="produtos[${contadorProdutos}][id]" class="form-select">
                <option value="">Selecione um produto</option>
                <?php foreach ($produtos as $produto): ?>
                <option value="<?= $produto["id"] ?>">
                    <?= htmlspecialchars($produto["nome"]) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Quantidade</label>
            <input type="number" name="produtos[${contadorProdutos}][quantidade]" class="form-control" min="1">
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger" onclick="this.closest('.produto-item').remove()">X</button>
        </div>
    `;

    lista.appendChild(novoProduto);

    contadorProdutos++;
}
</script>

</body>
</html>
