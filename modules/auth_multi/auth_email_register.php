<?php $view['masterview']="master_email" ?>
<p class="Normal">Hello <?php echo htmlspecialchars($model['username'])?>,</p>
<p class="Normal">Thank you for registering with <?php echo htmlspecialchars($model['auth']['sitename']) ?>.</p>
<p class="Normal">In order to activate your account, please click the following link:</p>
<p class="Normal"><a href="<?php echo $model['activateUrl']?>"><?php echo htmlspecialchars($model['activateUrl'])?></a></p>
<p class="Normal">If you have any questions or problems, please <a href="mailto:<?php echo $model['auth']['adminEmail']?>">Email Us</a></p>
<p class="Normal">Thanks</p>
