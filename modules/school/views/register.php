<?php include('_header.php'); ?>

<!-- show registration form, but only if we didn't submit already -->
<?php if (!$registration->registration_successful && !$registration->verification_successful) { ?>
    <form method="post" action="student_register.php" name="registerform">
        <div class="container">    
            <div id="loginbox" style="margin-top:50px;" class="mainbox mainbox col-sm-offset-2 col-sm-8">                    
                <div class="panel panel-info" >
                    <div class="panel-heading panel-gazie">
                        <div class="panel-title">
                            <img width="7%" src="../../library/images/gazie.gif" />
                            <img width="5%" src="./school.png" />
                            <h4 ><?php echo MESSAGE_WELCOME_REGISTRATION ?></h4>
                            <p><?php echo MESSAGE_INTRO_REGISTRATION; ?></p>
                            <p><?php echo MESSAGE_PSW_REGISTRATION; ?></p></div>
                    </div>
                    <div style="padding-top:10px" class="panel-body" >
                        <div style="padding-bottom: 25px;" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="user_name" type="text"  pattern="[a-zA-Z0-9]{2,64}" name="user_name" required class="form-control" style="height: 34px;"  placeholder="<?php echo WORDING_REGISTRATION_USERNAME; ?>" />
                        </div>
                        <div style="padding-bottom: 25px;" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                            <input id="user_email" type="email" name="user_email" required class="form-control" style="height: 34px;"  placeholder="<?php echo WORDING_REGISTRATION_EMAIL; ?>" />
                        </div>
                        <div style="padding-bottom: 25px;" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="user_password_new" type="password"  pattern=".{8,}" name="user_password_new" required class="form-control" style="height: 34px;"  placeholder="<?php echo WORDING_REGISTRATION_PASSWORD; ?>" />
                        </div>
                        <div style="padding-bottom: 25px;" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="user_password_repeat" type="password"  pattern=".{8,}" name="user_password_repeat" required class="form-control" style="height: 34px;"  placeholder="<?php echo WORDING_REGISTRATION_PASSWORD_REPEAT; ?>" />
                        </div>
                        <div style="padding-bottom: 25px;" class="input-group">
                            <img src="tools/showCaptcha.php" alt="captcha" />
                        </div>
                        <div style="padding-bottom: 25px;" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon glyphicon-hand-right"></i></span>
                            <input type="text" name="captcha"  required class="form-control" style="height: 34px;"  placeholder="<?php echo WORDING_REGISTRATION_CAPTCHA; ?>" />
                        </div>
                        <div style="padding-bottom: 25px;" class="input-group col-sm-12">
                            <input style="float:right;" class="btn btn-success"  type="submit" name="register" value="<?php echo WORDING_REGISTER; ?>" />
                        </div>
                        <div style="padding-bottom: 25px;" class="input-group">
                            <a style="float:left;" href="student_login.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>
                        </div>
                    </div>  <!-- chiude div panel-body -->
                </div>  <!-- chiude div panel -->
            </div>
        </div><!-- chiude div container -->
    </form>
<?php } ?>

<?php include('_footer.php'); ?>
