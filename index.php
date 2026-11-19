

<?php
require_once "../config/conexao.php";

$mensagem = "";
$produtoEditar = null;

// CADASTRAR OU ATUALIZAR PRODUTO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["salvar"])) {

    $nome = trim($_POST["nome"]);
    $descricao = trim($_POST["descricao"]);
    $peso = $_POST["peso"];
    $quantidade = $_POST["quantidade"];
    $id = $_POST["id"] ?? "";

    if ($nome != "" && $peso > 0 && $quantidade > 0) {

        if ($id != "") {
            $sql = "UPDATE produtos
                    SET nome = ?, descricao = ?, peso = ?, quantidade = ?
                    WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $descricao, $peso, $quantidade, $id]);

            $mensagem = "Produto atualizado com sucesso!";
        } else {
            $sql = "INSERT INTO produtos
                    (nome, descricao, peso, quantidade)
                    VALUES (?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $descricao, $peso, $quantidade]);

            $mensagem = "Produto cadastrado com sucesso!";
        }

    } else {
        $mensagem = "Preencha os campos obrigatórios corretamente.";
    }
}

// EXCLUIR PRODUTO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["excluir"])) {

    try {
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$_POST["id"]]);

        $mensagem = "Produto excluído com sucesso!";

    } catch (PDOException $erro) {
        $mensagem = "Não foi possível excluir este produto porque ele está vinculado a um frete.";
    }
}

// BUSCAR PRODUTO PARA EDIÇÃO
if (isset($_GET["editar"])) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$_GET["editar"]]);
    $produtoEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// LISTAR PRODUTOS
$sql = "SELECT * FROM produtos ORDER BY id DESC";
$stmt = $pdo->query($sql);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produtos - LogiControl</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f3f6fa;
            font-family: Arial, sans-serif;
        }

        .cabecalho {
            background: #14243a;
            color: white;
            padding: 22px;
        }

        .conteudo {
            padding: 30px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }
    </style>
</head>

<body>

<header class="cabecalho">
    <h3>LogiControl</h3>
    <p class="mb-0">Gerenciamento de Produtos</p>
</header>

<main class="container conteudo">

    <a href="../index.php" class="btn btn-outline-secondary mb-4">
        ← Voltar ao painel
    </a>

    <div class="card p-4 mb-4">

        <h4 class="mb-3">
            <?= $produtoEditar ? "Editar produto" : "Cadastrar novo produto" ?>
        </h4>

        <?php if ($mensagem != ""): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="id"
                   value="<?= htmlspecialchars($produtoEditar["id"] ?? "") ?>">

            <div class="mb-3">
                <label class="form-label">Nome do produto *</label>
                <input type="text" name="nome"
                       class="form-control"
                       value="<?= htmlspecialchars($produtoEditar["nome"] ?? "") ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="descricao"
                          class="form-control"><?= htmlspecialchars($produtoEditar["descricao"] ?? "") ?></textarea>
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Peso (kg) *</label>
                    <input type="number" name="peso"
                           class="form-control"
                           step="0.01" min="0.01"
                           value="<?= htmlspecialchars($produtoEditar["peso"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Quantidade *</label>
                    <input type="number" name="quantidade"
                           class="form-control"
                           min="1"
                           value="<?= htmlspecialchars($produtoEditar["quantidade"] ?? "") ?>"
                           required>
                </div>

            </div>

            <button type="submit" name="salvar" class="btn btn-primary">
                <?= $produtoEditar ? "Atualizar produto" : "Salvar produto" ?>
            </button>

            <?php if ($produtoEditar): ?>
                <a href="index.php" class="btn btn-secondary">
                    Cancelar edição
                </a>
            <?php endif; ?>

        </form>

    </div>

    <div class="card p-4">

        <h4 class="mb-3">Produtos cadastrados</h4>

        <div class="table-responsive">

            <table class="table table-striped table-hover">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Descrição</th>
                        <th>Peso (kg)</th>
                        <th>Quantidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($produtos as $produto): ?>

                    <tr>
                        <td><?= $produto["id"] ?></td>
                        <td><?= htmlspecialchars($produto["nome"]) ?></td>
                        <td><?= htmlspecialchars($produto["descricao"] ?? "") ?></td>
                        <td><?= $produto["peso"] ?></td>
                        <td><?= $produto["quantidade"] ?></td>

                        <td>
                            <a href="index.php?editar=<?= $produto["id"] ?>"
                               class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Deseja realmente excluir este produto?');">

                                <input type="hidden" name="id"
                                       value="<?= $produto["id"] ?>">

                                <button type="submit" name="excluir"
                                        class="btn btn-danger btn-sm">
                                    Excluir
                                </button>

                            </form>
                        </td>
                    </tr>

                    <?php endforeach; ?>

                    <?php if (count($produtos) == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            Nenhum produto cadastrado.
                        </td>
                    </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

</body>
</html>

