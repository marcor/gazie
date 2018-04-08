<?php include('_header.php'); ?>

<form method="post" action="login_user.php" name="loginform">
    <div class="container">    
        <div id="loginbox" style="margin-top:50px;" class="mainbox mainbox col-sm-offset-2 col-sm-8">                    
            <div class="panel panel-info" >
                <div class="panel-heading panel-gazie">
                    <div class="panel-title">
                        <img width="5%" src="../../library/images/gazie.gif" />
                        <?php echo MESSAGE_LOG_ADMIN; ?> <?php echo $server_lang; ?> <img width="5%" src="../../language/<?php echo TRANSL_LANG; ?>/flag.png" />
                    </div>
                    <div style="color: red; float:right; font-size: 100%; position: relative; top:-10px"></div>
                </div>
                <div style="padding-top:10px" class="panel-body" >
                    <h4 ><?php echo MESSAGE_WELCOME_ADMIN ?></h4>
                    <p><?php echo MESSAGE_INTRO_ADMIN; ?></p>
                    <p><?php echo MESSAGE_PSW_ADMIN; ?></p><br/>
                    <?php
                    if (isset($login)) {
                        if ($login->errors) {
                            foreach ($login->errors as $error) {
                                echo '<div id="login-alert" class="alert alert-danger col-sm-12">';
                                echo $error;
                                echo '</div>';
                            }
                        }
                        if ($login->messages) {
                            foreach ($login->messages as $message) {
                                echo '<div id="login-alert" class="alert alert-danger col-sm-12">';
                                echo $message;
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                    <div style="padding-bottom: 25px;" class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="user_name" type="text" name="user_name" required class="form-control" style="height: 34px;"  placeholder="<?php echo WORDING_USERNAME; ?>" />
                    </div>

                    <div style="padding-bottom: 25px;" class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input  type="password"  autocomplete="off" required style="height: 34px;"  id="login-password" class="form-control" name="user_password" placeholder="<?php echo WORDING_PASSWORD; ?>">
                    </div>
                    <div id="capsWarning" class="alert alert-warning col-sm-12" style="display:none;">Blocco maiuscole attivato! Caps lock on! Bloqueo de mayusculas!</div>
                    <div style="padding-top:10px" class="form-group">
                        <div class="col-sm-6 controls">
                            <a style="float:left;" href="login_password_reset.php"><?php echo WORDING_FORGOT_MY_PASSWORD; ?></a>
                        </div>
                     <!--     <div class="col-sm-6">
                            <input  style="float:left;"  type="checkbox" id="user_rememberme" name="user_rememberme" value="1" />
                            <label for="user_rememberme"><?php// echo WORDING_REMEMBER_ME; ?></label>
                        </div> -->
                        <div class="col-sm-6">
                            <input style="float:right;" class="btn btn-success"  name="login" type="submit" value="<?php echo WORDING_LOGIN; ?>" >
                        </div>
                    </div>
                    <div style="padding-top:10px" class="form-group">
                    <!--    <div class="col-sm-6 controls">
                            <a style="float:left;" href="login_register.php"><?php //echo WORDING_REGISTER_NEW_ADMIN; ?></a>
                        </div>-->
                    </div>
                </div>  
            </div>  
        </div>
    </div><!-- chiude div container -->
</form>


<?php include('_footer.php'); ?>
