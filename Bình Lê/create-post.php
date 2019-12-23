<?php
    ob_start();
    require_once ('init.php');
    require_once ('functions.php');
?>

<?php
    include 'check-before-login.php';
    include 'header.php';
?>

<?php if (isset($_POST['content'])): ?>
<?php $content = $_POST['content']; ?>
<?php if ($content == ''): ?>
    <div class="alert alert-danger" role="alert">Không được để trống status</div>
<?php else: ?>
<?php
    insertPost($_SESSION['userID'], $content);
    header('Location: index.php');
?>
<?php endif;?>
<?php endif;?>

<?php include 'footer.php'; ?>