
<?php
require_once "../config/conexao.php";

$mensagem = "";
$enderecoEditar = null;

// CADASTRAR OU ATUALIZAR ENDEREÇO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["salvar"])) {

    $dados = [
        ":identificacao" => trim($_POST["identificacao"]),
        ":cep" => trim($_POST["cep"]),
        ":logradouro" => trim($_POST["logradouro"]),
        ":numero" => trim($_POST["numero"]),
        ":complemento" => trim($_POST["complemento"]),
        ":bairro" => trim($_POST["bairro"]),
        ":cidade" => trim($_POST["cidade"]),
        ":estado" => strtoupper(trim($_POST["estado"]))
    ];

    $id = $_POST["id"] ?? "";

    if (
        $dados[":identificacao"] !== "" &&
        $dados[":cep"] !== "" &&
        $dados[":logradouro"] !== "" &&
        $dados[":numero"] !== "" &&
        $dados[":bairro"] !== "" &&
        $dados[":cidade"] !== "" &&
        strlen($dados[":estado"]) === 2
    ) {

        if ($id !== "") {

            $sql = "UPDATE enderecos SET
                    identificacao = :identificacao,
                    cep = :cep,
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    bairro = :bairro,
                    cidade = :cidade,
                    estado = :estado
                    WHERE id = :id";

            $dados[":id"] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);

            $mensagem = "Endereço atualizado com sucesso!";

        } else {

            $sql = "INSERT INTO enderecos
                    (identificacao, cep, logradouro, numero, complemento, bairro, cidade, estado)
                    VALUES
                    (:identificacao, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);

            $mensagem = "Endereço cadastrado com sucesso!";
        }

    } else {
        $mensagem = "Preencha corretamente os campos obrigatórios.";
    }
}

// EXCLUIR ENDEREÇO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir"])) {

    try {

        $stmt = $pdo->prepare("DELETE FROM enderecos WHERE id = ?");
        $stmt->execute([$_POST["id"]]);

        $mensagem = "Endereço excluído com sucesso!";

    } catch (PDOException $erro) {

        $mensagem = "Não foi possível excluir este endereço porque ele está vinculado a um frete.";
    }
}

// BUSCAR ENDEREÇO PARA EDIÇÃO
if (isset($_GET["editar"])) {

    $stmt = $pdo->prepare("SELECT * FROM enderecos WHERE id = ?");
    $stmt->execute([$_GET["editar"]]);

    $enderecoEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// LISTAR ENDEREÇOS
$stmt = $pdo->query("SELECT * FROM enderecos ORDER BY id DESC");
$enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Endereços - LogiControl</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h2 class="mb-4">Gerenciamento de Endereços</h2>

    <a href="../index.php" class="btn btn-secondary mb-3">
        Voltar ao início
    </a>

    <?php if ($mensagem): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <div class="card p-4 mb-4">

        <h4 class="mb-3">
            <?= $enderecoEditar ? "Editar endereço" : "Cadastrar novo endereço" ?>
        </h4>

        <form method="POST">

            <input type="hidden" name="id"
                   value="<?= htmlspecialchars($enderecoEditar["id"] ?? "") ?>">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Identificação</label>
                    <input type="text" name="identificacao" class="form-control"
                           value="<?= htmlspecialchars($enderecoEditar["identificacao"] ?? "") ?>"
                           placeholder="Ex: Depósito principal" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">CEP</label>
                    <input type="text" name="cep" class="form-control"
                           value="<?= htmlspecialchars($enderecoEditar["cep"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Logradouro</label>
                    <input type="text" name="logradouro" class="form-control"
                           value="<?= htmlspecialchars($enderecoEditar["logradouro"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Número</label>
                    <input type="text" name="numero" class="form-control"
                           value="<?= htmlspecialchars($enderecoEditar["numero"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Complemento</label>
                    <input type="text" name="complemento" class="form-control"
                           value="<?= htmlspecialchars($enderecoEditar["complemento"] ?? "") ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Bairro</label>
                    <input type="text" name="bairro" class="form-control"
                           value="<?= htmlspecialchars($enderecoEditar["bairro"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="cidade" class="form-control"
                           value="<?= htmlspecialchars($enderecoEditar["cidade"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado (UF)</label>
                    <input type="text" name="estado" class="form-control"
                           maxlength="2"
                           value="<?= htmlspecialchars($enderecoEditar["estado"] ?? "") ?>"
                           placeholder="PR" required>
                </div>

            </div>

            <button type="submit" name="salvar" class="btn btn-primary">
                <?= $enderecoEditar ? "Atualizar endereço" : "Cadastrar endereço" ?>
            </button>

            <?php if ($enderecoEditar): ?>
                <a href="index.php" class="btn btn-secondary">
                    Cancelar edição
                </a>
            <?php endif; ?>

        </form>

    </div>

    <h4>Endereços cadastrados</h4>

    <div class="table-responsive">

        <table class="table table-bordered table-striped bg-white">

            <thead class="table-dark">
                <tr>
                    <th>Identificação</th>
                    <th>CEP</th>
                    <th>Logradouro</th>
                    <th>Número</th>
                    <th>Bairro</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($enderecos as $endereco): ?>

                    <tr>

                        <td><?= htmlspecialchars($endereco["identificacao"]) ?></td>
                        <td><?= htmlspecialchars($endereco["cep"]) ?></td>
                        <td><?= htmlspecialchars($endereco["logradouro"]) ?></td>
                        <td><?= htmlspecialchars($endereco["numero"]) ?></td>
                        <td><?= htmlspecialchars($endereco["bairro"]) ?></td>
                        <td><?= htmlspecialchars($endereco["cidade"]) ?></td>
                        <td><?= htmlspecialchars($endereco["estado"]) ?></td>

                        <td>
                            <a href="index.php?editar=<?= $endereco["id"] ?>"
                               class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Deseja realmente excluir este endereço?');">

                                <input type="hidden" name="id"
                                       value="<?= $endereco["id"] ?>">

                                <button type="submit" name="excluir"
                                        class="btn btn-danger btn-sm">
                                    Excluir
                                </button>

                            </form>
                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (count($enderecos) == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            Nenhum endereço cadastrado.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>

