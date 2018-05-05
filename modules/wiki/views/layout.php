<?php
// Sanitize html content:
function e($dirty) {
    return htmlspecialchars($dirty, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="<?php echo e($page['description']) ?>">
        <meta name="keywords" content="<?php echo e(join(',', $page['tags'])) ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <?php if($page['title'] === false): ?>
            <title><?php echo e(APP_NAME) ?></title>
        <?php else: ?>
            <title><?php echo e($page['title']) ?> - <?php echo e(APP_NAME) ?></title>
        <?php endif ?>
        <?php if(!empty($page['author'])): ?>
            <meta name="author" content="<?php echo e($page['author']) ?>">
        <?php endif; ?>
        <base href="<?php echo BASE_URL; ?>/">

        <link rel="shortcut icon" href="../../library/images/favicon.ico">
        <link rel="stylesheet" href="../../modules/wiki/static/css/bootstrap.min.css">
        <link rel="stylesheet" href="../../modules/wiki/static/css/prettify.css">
        <link rel="stylesheet" href="../../modules/wiki/static/css/codemirror.css">
        <link rel="stylesheet" href="../../modules/wiki/static/css/main.css">
        <link rel="stylesheet" href="../../modules/wiki/adminlte/css/AdminLTE.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.12/css/all.css">
        <link rel="stylesheet" href="../../modules/wiki/adminlte/css/skin-gazie.css">
        
        <script src="../../modules/wiki/static/js/jquery.min.js"></script>
        <script src="../../modules/wiki/static/js/prettify.js"></script>
        <script src="../../modules/wiki/static/js/codemirror.min.js"></script>
    </head>
    
    <body class="hold-transition skin-blue sidebar-mini">
    <form method="POST" action="<?php echo BASE_URL . "/?a=edit" ?>">
      <header class="main-header">
        <a href="<?php echo DEFAULT_FILE; ?>" class="logo">
          <span class="logo-mini"><b>Gi</b>Wi</span>
          <span class="logo-lg"><b>Gazie</b>Wiki</span>
        </a>
        <nav class="navbar navbar-static-top" role="navigation">
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <?php if (ENABLE_EDITING): ?>
                    <li ><input type="submit" class="btn btn-primary btn-sm" id="submit-edits" value="Salva cambiamenti"></li>
                <?php endif ?>
                <li ><a href="javascript:;" id="toggle"><i class="fas fa-edit"></i>Modifica</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-wrench"></i> Gestione<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a target="_blank" href="filemanager\fm.php"><i class="fas fa-folder-open"></i> Cartelle</a></li>
                    </ul>
                </li>
            </ul>
          </div>
        </nav>
      </header>
<div class="container-fluid">
    <div id="main">
        <div class="inner">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-md-3">
                        <div id="sidebar">
                            <div class="inner">
                                <h2><span><?php echo 'Documentazione'; ?></span></h2>
                                <?php include('tree.php') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-9">
                        <div id="content">
                            <div class="inner">
                                <?php echo $content; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
    <script src="../../library/theme/lte/adminlte/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <script src="../../library/theme/lte/adminlte/bootstrap/js/bootstrap.min.js"></script>
    
</body>
</html>
