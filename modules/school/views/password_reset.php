<?php include('_header.php'); ?>

<?php if ($login->passwordResetLinkIsValid() == true) { ?>
<form method="post" action="password_reset.php" name="new_password_form">
    <input type='hidden' name='name' value='<?php echo htmlspecialchars($_GET['name']); ?>' />
    <input type='hidden' name='password_reset_hash' value='<?php echo htmlspecialchars($_GET['verification_code']); ?>' />

    <label for="password_new"><?php echo WORDING_NEW_PASSWORD; ?></label>
    <input id="password_new" type="password" name="password_new" pattern=".{6,}" required autocomplete="off" />

    <label for="password_repeat"><?php echo WORDING_NEW_PASSWORD_REPEAT; ?></label>
    <input id="password_repeat" type="password" name="password_repeat" pattern=".{6,}" required autocomplete="off" />
    <input type="submit" name="submit_new_password" value="<?php echo WORDING_SUBMIT_NEW_PASSWORD; ?>" />
</form>
<!-- no data from a password-reset-mail has been provided, so we simply show the request-a-password-reset form -->
<?php } else { ?>
<form method="post" action="password_reset.php" name="password_reset_form">
    <label for="name"><?php echo WORDING_REQUEST_PASSWORD_RESET; ?></label>
    <input id="name" type="text" name="name" required />
    <input type="submit" name="request_password_reset" value="<?php echo WORDING_RESET_PASSWORD; ?>" />
</form>
<?php } ?>

<a href="index.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>

<?php include('_footer.php'); ?>
