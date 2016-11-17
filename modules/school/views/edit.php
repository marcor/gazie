<?php include('_header.php'); ?>

<!-- clean separation of HTML and PHP -->
<h2><?php echo htmlspecialchars($_SESSION['name']); ?> <?php echo WORDING_EDIT_YOUR_CREDENTIALS; ?></h2>

<!-- edit form for username / this form uses HTML5 attributes, like "required" and type="email" -->
<form method="post" action="edit.php" name="edit_form_name">
    <label for="name"><?php echo WORDING_NEW_USERNAME; ?></label>
    <input id="name" type="text" name="name" pattern="[a-zA-Z0-9]{2,64}" required /> (<?php echo WORDING_CURRENTLY; ?>: <?php echo htmlspecialchars($_SESSION['name']); ?>)
    <input type="submit" name="edit_submit_name" value="<?php echo WORDING_CHANGE_USERNAME; ?>" />
</form><hr/>

<!-- edit form for user email / this form uses HTML5 attributes, like "required" and type="email" -->
<form method="post" action="edit.php" name="edit_form_email">
    <label for="email"><?php echo WORDING_NEW_EMAIL; ?></label>
    <input id="email" type="email" name="email" required /> (<?php echo WORDING_CURRENTLY; ?>: <?php echo htmlspecialchars($_SESSION['email']); ?>)
    <input type="submit" name="edit_submit_email" value="<?php echo WORDING_CHANGE_EMAIL; ?>" />
</form><hr/>

<!-- edit form for user's password / this form uses the HTML5 attribute "required" -->
<form method="post" action="edit.php" name="edit_form_password">
    <label for="password_old"><?php echo WORDING_OLD_PASSWORD; ?></label>
    <input id="password_old" type="password" name="password_old" autocomplete="off" />

    <label for="password_new"><?php echo WORDING_NEW_PASSWORD; ?></label>
    <input id="password_new" type="password" name="password_new" autocomplete="off" />

    <label for="password_repeat"><?php echo WORDING_NEW_PASSWORD_REPEAT; ?></label>
    <input id="password_repeat" type="password" name="password_repeat" autocomplete="off" />

    <input type="submit" name="edit_submit_password" value="<?php echo WORDING_CHANGE_PASSWORD; ?>" />
</form><hr/>

<!-- backlink -->
<a href="index.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>

<?php include('_footer.php'); ?>
