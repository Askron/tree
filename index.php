<?php
require_once('models/comment.php');
$db = pg_connect('host=localhost port=5432 dbname=tree user=postgres');
$comment = new Comment;
switch ($_GET['dir']) { //делаем запрос к базе в зависимости от get запроса
    case 'des':
        $comment->fetchDescendants((int) $_GET['id']);
        break;
    case 'anc':
        $comment->fetchAncestors((int) $_GET['id']);
        break;
    default:
        $comment->fetchAll();
        break;
}
if (!isset($_GET['id'])) $comment->fetchAll();
pg_close($db);
?>
<!DOCTYPE html>
<html>
<head>
    <title>tree</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <style>
        header, main {margin-top: 32px;}
        form {margin-top: 16px;}
    </style>
</head>
<body>
    <header class="container">
        <form action="/" method="get" class="row">
            <div class="col">
                <button type="submit" class="btn btn-success">Показать все</button>
            </div>
        </form>
        <form class="by-id row">
            <div class="col-3">
                <input value="<?= $_GET['id']; ?>" placeholder="Введите id комментария" type="number" name="id" class="form-control">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn-des btn btn-primary">Показать потомков</button>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn-anc btn btn-primary">Показать предков</button>
            </div>
            <input type="hidden" name="dir" value="des">
        </form>
    </header>
    <main class="container">
        <?php foreach ($comment as $k => $v): ?>
            <div style="margin-top: 16px;" class="row">
                <div class="col" style="margin-left: <?= $comment->depth*30; ?>px;">
                    <h3><?= $k; ?></h3>
                    <?= $v['comment']; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
</body>
<script>
    document.querySelector('.btn-des').addEventListener('click', function () {
        this.closest('form').querySelector('input[name="dir"]').value = 'des';
    });
    document.querySelector('.btn-anc').addEventListener('click', function () {
        this.closest('form').querySelector('input[name="dir"]').value = 'anc';
    });
</script>
</html>
