<?php
include 'header.php';
include 'menu.php';
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="form">
            <div class="col-mb-12 col-tb-8 col-tb-offset-2">
				<?php ExtraVerification_Plugin::editForm()->render(); ?>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
function replaceKey(){
    var oriinp = document.getElementById('randSecret-0-3');
    var tarinp = document.getElementById('secretKey-0-1');
    tarinp.setAttribute('value', oriinp.getAttribute('value'));
    alert('参考密钥已经复制到密钥框中');
}

window.onload = function(){
    copybtn = document.getElementById('copyKey');
    copybtn.addEventListener('click', replaceKey);
}
</script>

<?php
include 'form-js.php';
include 'footer.php';
?>
