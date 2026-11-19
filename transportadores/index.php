
<?php
require_once "../config/conexao.php";

$mensagem = "";
$transportadorEditar = null;

// CADASTRAR OU ATUALIZAR TRANSPORTADOR
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["salvar"])) {

    $dados = [
        ":nome" => trim($_POST["nome"]),
        ":documento" => trim($_POST["documento"]),
        ":telefone" => trim($_POST["telefone"]),
        ":email" => trim($_POST["email"]),
        ":placa" => strtoupper(trim($_POST["placa_veiculo"])),
        ":modelo" => trim($_POST["modelo_veiculo"])
    ];

    $id = $_POST["id"] ?? "";

    if (
        $dados[":nome"] !== "" &&
        $dados[":documento"] !== "" &&
        $dados[":placa"] !== "" &&
        $dados[":modelo"] !== ""
    ) {

        if ($id !== "") {

            $sql = "UPDATE transportadores SET
                    nome = :nome,
                    documento = :documento,
                    telefone = :telefone,
                    email = :email,
                    placa_veiculo = :placa,
                    modelo_veiculo = :modelo
                    WHERE id = :id";

            $dados[":id"] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);

            $mensagem = "Transportador atualizado com sucesso!";

        } else {

            $sql = "INSERT INTO transportadores
                    (nome, documento, telefone, email, placa_veiculo, modelo_veiculo)
                    VALUES
                    (:nome, :documento, :telefone, :email, :placa, :modelo)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($dados);

            $mensagem = "Transportador cadastrado com sucesso!";
        }

    } else {
        $mensagem = "Preencha corretamente os campos obrigatórios.";
    }
}

// EXCLUIR TRANSPORTADOR
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir"])) {

    try {

        $stmt = $pdo->prepare("DELETE FROM transportadores WHERE id = ?");
        $stmt->execute([$_POST["id"]]);

        $mensagem = "Transportador excluído com sucesso!";

    } catch (PDOException $erro) {

        $mensagem = "Não foi possível excluir este transportador porque ele está vinculado a um frete.";
    }
}

// BUSCAR TRANSPORTADOR PARA EDIÇÃO
if (isset($_GET["editar"])) {

    $stmt = $pdo->prepare("SELECT * FROM transportadores WHERE id = ?");
    $stmt->execute([$_GET["editar"]]);

    $transportadorEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// LISTAR TRANSPORTADORES
$stmt = $pdo->query("SELECT * FROM transportadores ORDER BY id DESC");
$transportadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transportadores - LogiControl</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h2 class="mb-4">Gerenciamento de Transportadores</h2>

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
            <?= $transportadorEditar ? "Editar transportador" : "Cadastrar novo transportador" ?>
        </h4>

        <form method="POST">

            <input type="hidden" name="id"
                   value="<?= htmlspecialchars($transportadorEditar["id"] ?? "") ?>">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nome do Transportador</label>
                    <input type="text" name="nome" class="form-control"
                           value="<?= htmlspecialchars($transportadorEditar["nome"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">CPF ou CNPJ</label>
                    <input type="text" name="documento" class="form-control"
                           value="<?= htmlspecialchars($transportadorEditar["documento"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control"
                           value="<?= htmlspecialchars($transportadorEditar["telefone"] ?? "") ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($transportadorEditar["email"] ?? "") ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Placa do Veículo</label>
                    <input type="text" name="placa_veiculo" class="form-control"
                           value="<?= htmlspecialchars($transportadorEditar["placa_veiculo"] ?? "") ?>"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Modelo do Veículo</label>
                    <input type="text" name="modelo_veiculo" class="form-control"
                           value="<?= htmlspecialchars($transportadorEditar["modelo_veiculo"] ?? "") ?>"
                           required>
                </div>

            </div>

            <button type="submit" name="salvar" class="btn btn-primary">
                <?= $transportadorEditar ? "Atualizar transportador" : "Cadastrar transportador" ?>
            </button>

            <?php if ($transportadorEditar): ?>
                <a href="index.php" class="btn btn-secondary">
                    Cancelar edição
                </a>
            <?php endif; ?>

        </form>

    </div>

    <h4>Transportadores cadastrados</h4>

    <div class="table-responsive">

        <table class="table table-bordered table-striped bg-white">

            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Documento</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Placa</th>
                    <th>Veículo</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($transportadores as $transportador): ?>

                    <tr>

                        <td><?= htmlspecialchars($transportador["nome"]) ?></td>
                        <td><?= htmlspecialchars($transportador["documento"]) ?></td>
                        <td><?= htmlspecialchars($transportador["telefone"] ?? "") ?></td>
                        <td><?= htmlspecialchars($transportador["email"] ?? "") ?></td>
                        <td><?= htmlspecialchars($transportador["placa_veiculo"]) ?></td>
                        <td><?= htmlspecialchars($transportador["modelo_veiculo"]) ?></td>

                        <td>

                            <a href="index.php?editar=<?= $transportador["id"] ?>"
                               class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Deseja realmente excluir este transportador?');">

                                <input type="hidden" name="id"
                                       value="<?= $transportador["id"] ?>">

                                <button type="submit" name="excluir"
                                        class="btn btn-danger btn-sm">
                                    Excluir
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (count($transportadores) == 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            Nenhum transportador cadastrado.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
