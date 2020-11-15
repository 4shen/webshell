<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */
?>
<div class="shopping-cart-img">
	<?= Yii::$service->page->translate->__('Register'); ?>
	
	<a external href="<?= Yii::$service->url->getUrl('customer/account/login');  ?>" class="f-right">
		<?= Yii::$service->page->translate->__('Login'); ?>
	</a>
</div>
<?= Yii::$service->page->widget->render('base/flashmessage'); ?>	
<div class="list-block customer-login  customer-register">
	<form action="<?= Yii::$service->url->getUrl('customer/account/register'); ?>" method="post" id="register-form" class="account-form">
		<?= \fec\helpers\CRequest::getCsrfInputHtml();  ?>
		<ul>
			<li>
				<div class="item-content">
					<div class="item-media">
						<i class="icon icon-form-name"></i>
					</div>
					<div class="item-inner">
						<div class="item-input">
						<input class="required-entry" type="text" placeholder="First name"  id="firstname" name="editForm[firstname]" value="<?= $firstname ?>" title="First Name">
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="item-content">
					<div class="item-media">
						<i class="icon icon-form-name"></i>
					</div>
					<div class="item-inner">
						<div class="item-input">
						<input class="required-entry" type="text" placeholder="Last name" id="lastname" name="editForm[lastname]" value="<?= $lastname ?>" title="Last Name">
						</div>
					</div>
				</div>
			</li>
			
			<li>
				<div class="item-content">
					<div class="item-media"><i class="icon icon-form-email"></i></div>
					<div class="item-inner">
						<div class="item-input">
							<input class="required-entry  validate-email"  type="email" placeholder="E-mail"  name="editForm[email]" id="email_address" value="<?= $email ?>" title="Email Address">
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="item-content">
					<div class="item-media"><i class="icon icon-form-password"></i></div>
					<div class="item-inner">
						<div class="item-input">
							<input  type="password" placeholder="Password"  name="editForm[password]" class="input-text required-entry validate-password" id="password" title="Password" >
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="item-content">
					<div class="item-media"><i class="icon icon-form-password"></i></div>
					<div class="item-inner">
						<div class="item-input">
							<input type="password" placeholder="Confirm password"  name="editForm[confirmation]" title="Confirm Password" id="confirmation" >
						</div>
					</div>
				</div>
			</li>
			
			<?php if($registerPageCaptcha):  ?>
				<li>
					<div class="item-content">
						<div class="item-media"><i class="icon icon-form-password"></i></div>
						<div class="item-inner">
							<div class="item-input">
								<input placeholder="captcha" type="text" name="editForm[captcha]" value="" size=10 class="login-captcha-input">
                                <img class="login-captcha-img"  title="<?= Yii::$service->page->translate->__('click refresh'); ?>" src="<?= Yii::$service->url->getUrl('site/helper/captcha'); ?>?<?php echo md5(time() . mt_rand(1,10000));?>" align="absbottom" onclick="this.src='<?= Yii::$service->url->getUrl('site/helper/captcha'); ?>?'+Math.random();"></img>
								<span class="icon icon-refresh"></span>
							</div>
						</div>
					</div>
				<script>
					<?php $this->beginBlock('register_captcha_onclick_refulsh') ?>  
					$(document).ready(function(){
						$(".icon-refresh").click(function(){
							$(this).parent().find("img").click();
						});
					});
					<?php $this->endBlock(); ?>  
					</script>  
					<?php $this->registerJs($this->blocks['register_captcha_onclick_refulsh'],\yii\web\View::POS_END);//将编写的js代码注册到页面底部 ?>

				</li>	
			<?php endif;  ?>	
			<li class="control">
				<div class="newsletter">
					<input name="editForm[is_subscribed]" title="Sign Up for Newsletter" value="1" id="is_subscribed" class="checkbox" type="checkbox" checked="checked">
					<label for="is_subscribed"><?= Yii::$service->page->translate->__('Sign Up for Newsletter'); ?></label>
				</div>
			</li>
		</ul>
		<div class="clear"></div>
		<div class="buttons-set">
			<p>
				<a external href="#"  id="js_registBtn" class="button button-fill">
					<?= Yii::$service->page->translate->__('Register Account'); ?>
				</a>
			</p>
		</div>
	</form>
</div>

<?php 
$requiredValidate 			= Yii::$service->page->translate->__('This is a required field.');
$emailFormatValidate 		= Yii::$service->page->translate->__('Please enter a valid email address. For example johndoe@domain.com.');
$firstNameLenghtValidate 	= Yii::$service->page->translate->__('first name length must between');
$lastNameLenghtValidate 	= Yii::$service->page->translate->__('last name length must between');
$passwordLenghtValidate 	= Yii::$service->page->translate->__('Please enter 6 or more characters. Leading or trailing spaces will be ignored.');
$passwordMatchValidate 		= Yii::$service->page->translate->__('Please make sure your passwords match. ');
//$minNameLength = 2;
//$maxNameLength = 20;
//$minPassLength = 6;  
//$maxPassLength = 30;

?>
<script>
<?php $this->beginBlock('customer_account_register') ?>  
$(document).ready(function(){
	$(".email_register_resend").click(function(){
        emailRegisterResendUrl = "<?= Yii::$service->url->getUrl('customer/account/resendregisteremail') ?>";
        $.ajax({
            async:true,
            timeout: 6000,
            dataType: 'json', 
            type:'get',
            data: {
                "email": "<?= $email ?>"
            },
            url:emailRegisterResendUrl,
            success:function(data, textStatus){ 
                // 
                if (data.resendStatus == 'success') {
                    //$(".resend_text").html('resend register email success');
                    alert("<?= Yii::$service->page->translate->__('resend register email success') ?>")
                } else {
                    //$(".resend_text").html('resend register email fail');
                    alert("<?= Yii::$service->page->translate->__('resend register email fail') ?>")
                }
            },
            error:function (XMLHttpRequest, textStatus, errorThrown){}
        });
        
        
    });
	$("#js_registBtn").click(function(){
		validate = 1;
		$(".validation-advice").remove();
		$(".validation-failed").removeClass("validation-failed");
		
		var myreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		// empty check
		$(".customer-register .required-entry").each(function(){
			val = $(this).val();
			if(!val){
				$(this).addClass("validation-failed");
				$(this).parent().append('<div class="validation-advice" id="advice-required-entry-firstname" style=""><?= $requiredValidate; ?></div>');
				validate = 0;
			}
		});
		
		// email check
		$(".customer-register .validate-email").each(function(){
			email = $(this).val();
			if(email){
				if(!$(this).hasClass("validation-failed")){
					if(!myreg.test(email)){
						$(this).parent().append('<div class="validation-advice" id="advice-validate-email-email_address" style=""><?= $emailFormatValidate; ?></div>');
						$(this).addClass("validation-failed");
						validate = 0;
					}
				}
			}else{
				validate = 0;
			}
		});
		
		//first name lenght check;
		firstname 	= $("#firstname").val();
		lastname 	= $("#lastname").val();
		password  	= $("#password").val();
		confirmation= $("#confirmation").val();
		minNameLength = <?= $minNameLength ? $minNameLength : 4 ?>;  
		maxNameLength = <?= $maxNameLength ? $maxNameLength : 30 ?>;
		minPassLength = <?= $minPassLength ? $minPassLength : 4 ?>;  
		maxPassLength = <?= $maxPassLength ? $maxPassLength : 30 ?>; 
		firstNameLength = firstname.length;
		lastNameLength  = lastname.length;
		passwordLength  = password.length;
		//firstname length validate
		if(firstNameLength < minNameLength || firstNameLength > maxNameLength){
			if(!$("#firstname").hasClass("validation-failed")){
				//alert(111);
				$("#firstname").parent().append('<div class="validation-advice" id="min_lenght" style=""><?= $firstNameLenghtValidate; ?> '+minNameLength+' , '+maxNameLength+'</div>');
				$("#firstname").addClass("validation-failed");
				validate = 0;		
			}
		}
		//lastname length validate
		if(lastNameLength < minNameLength || lastNameLength > maxNameLength){
			if(!$("#lastname").hasClass("validation-failed")){
				//alert(111);
				$("#lastname").parent().append('<div class="validation-advice" id="min_lenght" style=""><?= $lastNameLenghtValidate; ?> '+minNameLength+' , '+maxNameLength+'</div>');
				$("#lastname").addClass("validation-failed");
				validate = 0;		
			}
		}
		//password length validate
		if(passwordLength < minPassLength || passwordLength > maxPassLength){
			if(!$("#password").hasClass("validation-failed")){
				//alert(111);
				$("#password").parent().append('<div class="validation-advice" id="min_lenght" style=""><?= $passwordLenghtValidate; ?> </div>');
				$("#password").addClass("validation-failed");
				validate = 0;		
			}
		}
		//password validate
		if(confirmation != password){
			if(!$("#confirmation").hasClass("validation-failed")){
				//alert(111);
				$("#confirmation").parent().append('<div class="validation-advice" id="min_lenght" style=""><?= $passwordMatchValidate; ?></div>');
				$("#confirmation").addClass("validation-failed");
				validate = 0;		
			}
		}
		
		if(validate){
		//	alert("validate success");
			$(this).addClass("dataUp");
			$("#register-form").submit();
		}
	});
});
<?php $this->endBlock(); ?>  
</script>  
<?php $this->registerJs($this->blocks['customer_account_register'],\yii\web\View::POS_END);//将编写的js代码注册到页面底部 ?>




