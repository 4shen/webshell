/**
 * Post the email address change to the server.
 */
function changeEmailAddress () {
	var emailInfo = $('#email');
	if (emailInfo.val() === emailInfo.defaultValue) {
		return;
	}
	emailInfo.defaultValue = emailInfo.val();
	OC.msg.startSaving('#lostpassword .msg');
	var post = $("#lostpassword").serializeArray();
	$.ajax({
		type: 'PUT',
		url: OC.generateUrl('/settings/users/{id}/mailAddress', {id: OC.currentUser}),
		data: {
			mailAddress: post[0].value
		}
	}).done(function(result){
		// I know the following 4 lines look weird, but that is how it works
		// in jQuery -  for success the first parameter is the result
		//              for failure the first parameter is the result object
		OC.msg.finishedSaving('#lostpassword .msg', result);
	}).fail(function(result){
		OC.msg.finishedSaving('#lostpassword .msg', result.responseJSON);
	});
}

/**
 * Post the display name change to the server.
 */
function changeDisplayName () {
	if ($('#displayName').val() !== '') {
		OC.msg.startSaving('#displaynameform .msg');
		// Serialize the data
		var post = $("#displaynameform").serialize();
		// Ajax foo
		$.post(OC.generateUrl('/settings/users/{id}/displayName', {id: OC.currentUser}), post, function (data) {
			if (data.status === "success") {
				$('#oldDisplayName').val($('#displayName').val());
				// update displayName on the top right expand button
				$('#expandDisplayName').text($('#displayName').val());
				// update avatar if avatar is available
				if(!$('#removeavatar').hasClass('hidden')) {
					updateAvatar();
				}
			}
			else {
				$('#newdisplayname').val(data.data.displayName);
			}
			OC.msg.finishedSaving('#displaynameform .msg', data);
		});
	}
}

function updateAvatar (hidedefault) {
	var $headerdiv = $('#header .avatardiv');
	var $displaydiv = $('#displayavatar .avatardiv');

	if (hidedefault) {
		$headerdiv.hide();
		$('#header .avatardiv').removeClass('avatardiv-shown');
	} else {
		$headerdiv.css({'background-color': ''});
		$headerdiv.avatar(OC.currentUser, 32, true);
		$('#header .avatardiv').addClass('avatardiv-shown');
	}
	$displaydiv.css({'background-color': ''});
	$displaydiv.avatar(OC.currentUser, 145, true);
	$.get(OC.generateUrl(
		'/avatar/{user}/{size}',
		{user: OC.currentUser, size: 1}
	), function (result) {
		if (typeof(result) === 'string') {
			// Show the delete button when the avatar is custom
			$('#removeavatar').removeClass('hidden').addClass('inlineblock');
		}
	});
}

function showAvatarCropper () {
	var $cropper = $('#cropper');
	$cropper.prepend("<img>");
	var $cropperImage = $('#cropper img');

	$cropperImage.attr('src',
		OC.generateUrl('/avatar/tmp') + '?requesttoken=' + encodeURIComponent(oc_requesttoken) + '#' + Math.floor(Math.random() * 1000));

	// Looks weird, but on('load', ...) doesn't work in IE8
	$cropperImage.ready(function () {
		$('#displayavatar').hide();
		$cropper.show();

		$cropperImage.Jcrop({
			onChange: saveCoords,
			onSelect: saveCoords,
			aspectRatio: 1,
			boxHeight: 500,
			boxWidth: 500,
			setSelect: [0, 0, 100000, 100000]  /* set to a very large value since 
			this will automatically take the minimum with width and height of
			parent image and aspect ratio is already one, ensuring a square cropper */
		});
	});
}

function sendCropData () {
	cleanCropper();

	var cropperData = $('#cropper').data();
	var data = {
		x: cropperData.x,
		y: cropperData.y,
		w: cropperData.w,
		h: cropperData.h
	};
	$.post(OC.generateUrl('/avatar/cropped'), {crop: data}, avatarResponseHandler);
}

function saveCoords (c) {
	$('#cropper').data(c);
}

function cleanCropper () {
	var $cropper = $('#cropper');
	$('#displayavatar').show();
	$cropper.hide();
	$('.jcrop-holder').remove();
	$('#cropper img').removeData('Jcrop').removeAttr('style').removeAttr('src');
	$('#cropper img').remove();
}

function avatarResponseHandler (data) {
	if (typeof data === 'string') {
		data = JSON.parse(data);
	}
	var $warning = $('#avatar .warning');
	$warning.hide();
	if (data.status === "success") {
		updateAvatar();
	} else if (data.data === "notsquare") {
		showAvatarCropper();
	} else {
		$warning.show();
		$warning.text(data.data.message);
	}
}




$(document).ready(function () {
	var query = OC.parseQueryString(location.search);
	if (query && query.changestatus) {
		if (query.changestatus === 'error') {
			OC.Notification.showTemporary(t('settings', 'Failed to change the email address.'));
		} else if (query.changestatus === 'success') {
			OC.Notification.showTemporary(t('settings', 'Email changed successfully for {user}.', {user: query.user}));
		}
		OC.Util.History.replaceState({});
	}

	if($('#pass2').length) {
		$('#pass2').showPassword().keyup();
	}
	$("#passwordbutton").click(function () {
		var isIE8or9 = $('html').hasClass('lte9');
		// FIXME - TODO - once support for IE8 and IE9 is dropped
		// for IE8 and IE9 this will check additionally if the typed in password
		// is different from the placeholder, because in IE8/9 the placeholder
		// is simply set as the value to look like a placeholder
		if ($('#pass1').val() !== '' && $('#pass2').val() !== ''
			&& !(isIE8or9 && $('#pass2').val() === $('#pass2').attr('placeholder'))) {
			// Serialize the data
			var post = $("#passwordform").serialize();
			$('#passwordchanged').hide();
			$('#passworderror').hide();
			// Ajax foo
			$.post(OC.generateUrl('/settings/personal/changepassword'), post, function (data) {
				if (data.status === "success") {
					$('#pass1').val('');
					$('#pass2').val('').change();
					// Hide a possible errormsg and show successmsg
					$('#password-changed').removeClass('hidden').addClass('inlineblock');
					$('#password-error').removeClass('inlineblock').addClass('hidden');
				} else {
					if (typeof(data.data) !== "undefined") {
						$('#password-error').text(data.data.message);
					} else {
						$('#password-error').text(t('Unable to change password'));
					}
					// Hide a possible successmsg and show errormsg
					$('#password-changed').removeClass('inlineblock').addClass('hidden');
					$('#password-error').removeClass('hidden').addClass('inlineblock');
				}
			});
			return false;
		} else {
			// Hide a possible successmsg and show errormsg
			$('#password-changed').removeClass('inlineblock').addClass('hidden');
			$('#password-error').removeClass('hidden').addClass('inlineblock');
			return false;
		}

	});

	$('#displayName').keyUpDelayedOrEnter(changeDisplayName);
	$('#email').enter(changeEmailAddress, true);
	$('#emailbutton').click(function () {
		changeEmailAddress();
	});

	$("#languageinput").change(function () {
		// Serialize the data
		var post = $("#languageinput").serialize();
		// Ajax foo
		$.post('ajax/setlanguage.php', post, function (data) {
			if (data.status === "success") {
				location.reload();
			}
			else {
				$('#passworderror').text(data.data.message);
			}
		});
		return false;
	});

	var uploadparms = {
		pasteZone: null,
		done: function (e, data) {
			var response = data;
			if (typeof data.result === 'string') {
				response = JSON.parse(data.result);
			} else if (data.result && data.result.length) {
				// fetch response from iframe
				response = JSON.parse(data.result[0].body.innerText);
			} else {
				response = data.result;
			}
			avatarResponseHandler(response);
		},
		submit: function(e, data) {
			data.formData = _.extend(data.formData || {}, {
				requesttoken: OC.requestToken
			});
		},
		fail: function (e, data){
			var msg = data.jqXHR.statusText + ' (' + data.jqXHR.status + ')';
			if (!_.isUndefined(data.jqXHR.responseJSON) &&
				!_.isUndefined(data.jqXHR.responseJSON.data) &&
				!_.isUndefined(data.jqXHR.responseJSON.data.message)
			) {
				msg = data.jqXHR.responseJSON.data.message;
			}
			avatarResponseHandler({
			data: {
					message: t('settings', 'An error occurred: {message}', { message: msg })
				}
			});
		}
	};

	$('#uploadavatar').fileupload(uploadparms);

	$('#selectavatar').click(function () {
		OC.dialogs.filepicker(
			t('settings', "Select a profile picture"),
			function (path) {
				$.ajax({
					type: "POST",
					url: OC.generateUrl('/avatar/'),
					data: { path: path }
				}).done(avatarResponseHandler)
					.fail(function(jqXHR, status){
						var msg = jqXHR.statusText + ' (' + jqXHR.status + ')';
						if (!_.isUndefined(jqXHR.responseJSON) &&
							!_.isUndefined(jqXHR.responseJSON.data) &&
							!_.isUndefined(jqXHR.responseJSON.data.message)
						) {
							msg = jqXHR.responseJSON.data.message;
						}
						avatarResponseHandler({
							data: {
								message: t('settings', 'An error occurred: {message}', { message: msg })
							}
						});
					});
			},
			false,
			["image/png", "image/jpeg"]
		);
	});

	$('#removeavatar').click(function () {
		$.ajax({
			type: 'DELETE',
			url: OC.generateUrl('/avatar/'),
			success: function () {
				updateAvatar(true);
				$('#removeavatar').addClass('hidden').removeClass('inlineblock');
			}
		});
	});

	$('#abortcropperbutton').click(function () {
		cleanCropper();
	});

	$('#sendcropperbutton').click(function () {
		sendCropData();
	});

	$('#pass2').strengthify({
		zxcvbn: OC.linkTo('core','vendor/zxcvbn/dist/zxcvbn.js'),
		titles: [
			t('core', 'Very weak password'),
			t('core', 'Weak password'),
			t('core', 'So-so password'),
			t('core', 'Good password'),
			t('core', 'Strong password')
		],
		drawTitles: true
	});

	// does the user have a custom avatar? if he does show #removeavatar
	$.get(OC.generateUrl(
		'/avatar/{user}/{size}',
		{user: OC.currentUser, size: 1}
	), function (result) {
		if (typeof(result) === 'string') {
			// Show the delete button when the avatar is custom
			$('#removeavatar').removeClass('hidden').addClass('inlineblock');
		}
	});

	// Load the big avatar
	if (oc_config.enable_avatars) {
		$('#avatar .avatardiv').avatar(OC.currentUser, 145);
	}
});
