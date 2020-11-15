<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */
?>
<div class="account-ds">
	<div class="bar bar-nav account-top-m">
		<a external class="button button-link button-nav pull-left" href="<?= Yii::$service->url->getUrl('customer/account/index'); ?>">
			<span class="icon icon-left"></span>
		</a>
		<h1 class='title'><?= Yii::$service->page->translate->__('Reset Password'); ?></h1>
	</div>
</div>
<?= Yii::$service->page->widget->render('base/flashmessage'); ?>	
<div class="list-block customer-login ">
	<?php  if(!empty($identity)):  ?>
		
		<div class="account-create">
			
			<form action="<?= Yii::$service->url->getUrl('customer/account/resetpassword',['resetToken'=>$resetToken]); ?>" method="post" id="form-validate">
				<?= \fec\helpers\CRequest::getCsrfInputHtml();  ?>
				<input type="hidden"  name="editForm[resetToken]"  value="<?= $resetToken ?>" />
				
				<ul>
					<li>
						<div class="item-content">
							<div class="item-media">
								<i class="icon icon-form-name"></i>
							</div>
							<div class="item-inner">
								<div class="item-input">
									<input placeholder="<?= Yii::$service->page->translate->__('Your Email Address');?>" name="editForm[email]" id="email_address" value="<?= $email ?>" title="Email Address" class="input-text validate-email required-entry" type="text">
							
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
									<input placeholder="<?= Yii::$service->page->translate->__('New Password');?>" name="editForm[password]" id="password" title="Password" class="input-text required-entry validate-password" type="password">
								
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
									<input placeholder="<?= Yii::$service->page->translate->__('Confirm Password');?>" name="editForm[confirmation]" title="Confirm Password" id="confirmation" class="input-text required-entry validate-cpassword" type="password">
								
								</div>
							</div>
						</div>
					</li>
				</ul>
				<div class="clear"></div>
				<div class="buttons-set">
					<p>
						<a external href="javascript:void(0)"   id="js_resetPassBtn" class="button button-fill">
							<?= Yii::$service->page->translate->__('Reset Password'); ?>
						</a>
					</p>
				</div>
				
				<div class="clear"></div>
				
			</form>
		</div>
		
		<?php 
		$requiredValidate 			= Yii::$service->page->translate->__('This is a required field.');
		$emailFormatValidate 		= Yii::$service->page->translate->__('Please enter a valid email address. For example johndoe@domain.com.');
		$passwordLenghtValidate 	= Yii::$service->page->translate->__('Please enter 6 or more characters. Leading or trailing spaces will be ignored.');
		$passwordMatchValidate 		= Yii::$service->page->translate->__('Please make sure your passwords match.');
		//$minNameLength = 2;
		//$maxNameLength = 20;
		//$minPassLength = 6;  
		//$maxPassLength = 30;

		?>
		<script>
		<?php $this->beginBlock('customer_account_reset') ?>  
		$(document).ready(function(){
			$("#js_resetPassBtn").click(function(){
				validate = 1;
				$(".validation-advice").remove();
				$(".validation-failed").removeClass("validation-failed");
				
				var myreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
				// empty check
				$(".account-create .required-entry").each(function(){
					val = $(this).val();
					if(!val){
						$(this).addClass("validation-failed");
						$(this).parent().append('<div class="validation-advice" id="advice-required-entry-firstname" style=""><?= $requiredValidate; ?></div>');
						validate = 0;
					}
				});
				
				// email check
				$(".account-create .validate-email").each(function(){
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
				
				
				password  	= $("#password").val();
				confirmation= $("#confirmation").val();
				minPassLength = <?= $minPassLength ? $minPassLength : 4 ?>;  
				maxPassLength = <?= $maxPassLength ? $maxPassLength : 30 ?>; 
				passwordLength  = password.length;
				
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
					$("#form-validate").submit();
				}
			});
		});
		<?php $this->endBlock(); ?>  
		</script>  
		<?php $this->registerJs($this->blocks['customer_account_reset'],\yii\web\View::POS_END);//将编写的js代码注册到页面底部 ?>


	<?php  else:  ?>
		<div class="customer-forgot-success">
			<?php
				$param = ['logUrlB' => '<a external href="'.$forgotPasswordUrl.'">','logUrlE' => '</a> '];
			?>
			<?= Yii::$service->page->translate->__('Your Reset Password Token is Expired, You can {logUrlB} click here {logUrlE} to retrieve it ',$param); ?>
			
		</div>
	<?php  endif; ?>
	
</div>