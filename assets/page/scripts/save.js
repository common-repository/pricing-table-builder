function SGPT(){
	this.init();
};

SGPT.maxElementsCount = 6;
SGPT.minElementsCount = 2;
SGPT.elementsInARow = 3;

SGPT.prototype.init = function(){
	var that = this;
	var sgptPluginUrl = jQuery('#sgpt-plugin-url').val(); // This is hidden input inside save page
	jQuery('.js-sgpt-clone-column').click(function(){
		that.clone();
	});

	jQuery('.sg-banner-close-js').click(function(){
		SGPT.prototype.ajaxCloseBanner();
	});

	jQuery('.sgpt-js-update').click(function(){

		that.save();
	});

	jQuery('.close_free_banner_pricing_table').on('click', function() {
		jQuery("#hugeit_pricing_table_builder_free_banner").css('display', 'none');
		hgPricingSetCookie('hgPricingFreeBannerShow', 'no', {expires: 86400});
	});

	if (jQuery('.sgpt-current-font').length) {
		var currentFont = jQuery('.sgpt-current-font').val();
		if(currentFont) {
			changeFont(currentFont);
		}
	}

	jQuery('.one-column').mousedown(function(){
		jQuery('.sgpt-tables-container').sortable();
		jQuery('.sgpt-description').css({
			'background-color':'#fff'
		});
	});
	var custom_uploader;
	jQuery('#sgpt-import-js').click(function(e){
		e.preventDefault();

		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Select Export File',
			button: {
				text: 'Select Export File'
			},
			multiple: false,
			library : { type  :  'text/plain'},
		}).open();

		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			var attachmentUrl = attachment.url;
			SGPT.ajaxSgptImport(attachmentUrl);
		});
	});

	jQuery('.sgpt-column').hover(function(){
		jQuery(this).css('cursor','move');
	});

	var isPro = jQuery('.isPro').val();
	if (isPro == 1) {
		jQuery().bfhselectbox('toggle');
	}

	jQuery(function(){
		jQuery(".colorPicker").wpColorPicker();
	});

	jQuery('.sgpt-theme-selector').click(function(){
		var all = jQuery('#sgpt-theme-name').text();
		var container = jQuery('#sgpt-themes').dialog({ 
							width: 875,
							modal: true,
							height: 500,
							buttons: {
							"Select theme": function() {
								var themeName = jQuery('input[name=sgpt-theme-radio]:checked').val();
								jQuery('input[name=sgpt-theme]').val(themeName);
								jQuery('#sgpt-theme-name').html(themeName);
								jQuery(this).dialog( "close" );
							},
							Cancel: function() {
							  jQuery(this).dialog("close");
							}
						  }
						}),
			scrollTo = jQuery('input[name=sgpt-theme-radio]:checked').parent();
			jQuery('input[name=sgpt-theme-radio]').each(function(){
				if (jQuery(this).val() == all) {
					jQuery(this).parent().find('input').attr('checked','checked');
					scrollTo = jQuery(this).parent();
				}
			});
		if (scrollTo.length != 0) {
			if(typeof container.offset().top !== 'undefined') {
				container.animate({
					scrollTop: (scrollTo.offset().top - container.offset().top + container.scrollTop()) - 7
					//Lowered to 7,because label has border and is highlighted
				});
			}
			
		}
		else {
		// Select theme for the first time
		var defaultTheme = jQuery('#TB_ajaxContent label:first-child');
		var res = jQuery(defaultTheme).find('input').attr('checked','checked');
		}
	});

	jQuery('.sgpt-js-preview').click(function(){
		var font = '';
		jQuery('#sgpt-preview-wrapper').remove();
		var theme = jQuery('input[name=sgpt-theme]').val();
		var form = jQuery('.sgpt-js-form');
		var previewAction = 'PricingTable_ajaxPreview';
		var ajaxHandler = new sgptRequestHandler(previewAction, form.serialize());
		var planNameColor = jQuery('input[name=planNameColor]').val();
		var bgColor = jQuery('input[name=bgColor]').val();
		var featureColor = jQuery('input[name=featureColor]').val();
		var buttonColor = jQuery('input[name=buttonColor]').val();
		var textNameColor = jQuery('input[name=textNameColor]').val();
		var textFeatureColor = jQuery('input[name=textFeatureColor]').val();
		var textButtonColor = jQuery('input[name=textButtonColor]').val();
		var fontName = jQuery('.bfh-selectbox-option').text();
		var planShadowOn = jQuery('input[name=planShadowOn]:checked');
		var textShadowOn = jQuery('input[name=textShadowOn]:checked');

		var planShadowStyle = '';
		var textShadowStyle = '';
		var mainShadowStyle = '';

		var planShadowColor = jQuery('input[name=planShadowColor]').val();
		var planLeftRight = jQuery('input[name=planLeftRight]').val();
		var planTopBottom = jQuery('input[name=planTopBottom]').val();
		var planBlur = jQuery('input[name=planBlur]').val();

		var textShadowColor = jQuery('input[name=textShadowColor]').val();
		var textLeftRight = jQuery('input[name=textLeftRight]').val();
		var textTopBottom = jQuery('input[name=textTopBottom]').val();
		var textBlur = jQuery('input[name=textBlur]').val();

		if (planShadowOn.length == 1) {
			if (!planLeftRight || !planTopBottom || 
					!jQuery.isNumeric(planLeftRight) ||
					!jQuery.isNumeric(planTopBottom) || 
					!jQuery.isNumeric(planBlur)
				) {
				alert('Fill all needed fields correctly');
				return;
			}
			if (planLeftRight && planTopBottom) {
				mainShadowStyle += 'box-shadow:'+planLeftRight+'px '+planTopBottom+'px ';
			}
			if (planBlur) {
				mainShadowStyle += planBlur+'px ';
			}
			if (planShadowColor) {
				mainShadowStyle += planShadowColor+';';
			}
		}

		if (textShadowOn.length == 1) {
			if (!textLeftRight || !textTopBottom || 
					!jQuery.isNumeric(textLeftRight) || 
					!jQuery.isNumeric(textTopBottom) || 
					!jQuery.isNumeric(textBlur)
				) {
				alert('Fill all needed fields correctly');
				return;
			}
			if (textLeftRight && textTopBottom) {
				mainShadowStyle += 'text-shadow:'+textLeftRight+'px '+textTopBottom+'px ';
			}
			if (textBlur) {
				mainShadowStyle += textBlur+'px ';
			}
			if (textShadowColor) {
				mainShadowStyle += textShadowColor+';';
			}
		}

		jQuery('.loading i').show();
		ajaxHandler.dataIsObject = false;
		ajaxHandler.dataType = 'html';
		ajaxHandler.callback = function(response){
			//If success
			if(response) {

								//Load theme css if not added before
				var themeURL = sgptPluginUrl+'assets/core/styles/css/'+theme+'.css';
				if (!jQuery('link[href="'+themeURL+'"]').length)
					jQuery('<link href="'+themeURL+'" rel="stylesheet">').appendTo('head');
				jQuery('.sgpt-wrapper').append('<div style="display: none" id="sgpt-preview-wrapper"><div id="sgpt-preview">'+response+'</div></div>');
				//Thickbox
				tb_show('Preview', '#TB_inline?height=500&amp;width=635&amp;inlineId=sgpt-preview-wrapper');
				if (mainShadowStyle) {
					jQuery("div[class$='_whole']").attr('style',mainShadowStyle);
				}
				if (planNameColor || bgColor || featureColor|| buttonColor|| textNameColor|| textFeatureColor|| textButtonColor || fontName) {
					if (fontName) {
						changeFont(fontName);
						var nameColorFont = 'font-family:'+fontName+';color: '+textNameColor+';background-color: '+planNameColor;
						var featureColorFont = 'font-family:'+fontName+';color: '+textFeatureColor+';background-color: '+featureColor;
						var buttonColorFont = 'font-family:'+fontName+';color: '+textButtonColor+';background-color: '+buttonColor;
					   jQuery("div[class$='_text']").attr("style",nameColorFont);
					   jQuery("div[class$='_content'] ul li").attr("style",featureColorFont);
					   jQuery("div[class$='_plan']").attr("style",'font-family:'+fontName+';background-color: '+bgColor);
					   jQuery("div[class$='_timeframe']").attr('style','font-family:'+fontName);
					   jQuery("a[class$='_bottom']").attr("style",buttonColorFont);
					}
					else {
						jQuery("div[class$='_text']").css("color",textNameColor);
						jQuery("div[class$='_content'] ul li").css("color",textFeatureColor);
						jQuery("a[class$='_bottom']").css("color",textButtonColor);
						jQuery("div[class$='_type']").css('background-color',planNameColor);
						jQuery("div[class$='_plan']").css('background-color',bgColor);
						jQuery("div[class$='_content'] ul li").css('background-color',featureColor);
						jQuery("a[class$='_bottom']").css('background-color',buttonColor);
					}
				}
				changeSize();
			}
			jQuery('.loading i').hide();
		}
		ajaxHandler.run();
	});

	jQuery('.reset-options').click(function(){
		if (confirm('Are you sure?')) {
			jQuery('input[name=planNameColor]').val('');
			jQuery('input[name=bgColor]').val('');
			jQuery('input[name=featureColor]').val('');
			jQuery('input[name=buttonColor]').val('');
			jQuery('input[name=textNameColor]').val('');
			jQuery('input[name=textFeatureColor]').val('');
			jQuery('input[name=textButtonColor]').val('');

			jQuery('input[name=planLeftRight]').val('');
			jQuery('input[name=textLeftRight]').val('');
			jQuery('input[name=planTopBottom]').val('');
			jQuery('input[name=textTopBottom]').val('');
			jQuery('input[name=planBlur]').val('');
			jQuery('input[name=textBlur]').val('');
			jQuery('input[name=planShadowOn]').removeAttr('checked');
			jQuery('input[name=textShadowOn]').removeAttr('checked');

			jQuery('#selectFonts .bfh-selectbox-option').text('');
			jQuery('.wp-color-result').css('background-color','');
		}
	});
};

SGPT.prototype.save = function(){
	var font = '';
	jQuery('.notice').remove();
	var form = jQuery('.sgpt-js-form');
	var planNameColor = jQuery('input[name=planNameColor]').val();
	var bgColor = jQuery('input[name=bgColor]').val();
	var featureColor = jQuery('input[name=featureColor]').val();
	var buttonColor = jQuery('input[name=buttonColor]').val();
	var textNameColor = jQuery('input[name=textNameColor]').val();
	var textFeatureColor = jQuery('input[name=textFeatureColor]').val();
	var textButtonColor = jQuery('input[name=textButtonColor]').val();
	var font = jQuery('.bfh-selectbox-option').text();
	var planShadowOn = jQuery('input[name=planShadowOn]:checked');
	var textShadowOn = jQuery('input[name=textShadowOn]:checked');

	var planLeftRight = jQuery.isNumeric(jQuery('input[name=planLeftRight]').val());
	var planTopBottom = jQuery.isNumeric(jQuery('input[name=planTopBottom]').val());
	var textLeftRight = jQuery.isNumeric(jQuery('input[name=textLeftRight]').val());
	var textTopBottom = jQuery.isNumeric(jQuery('input[name=textTopBottom]').val());
	var planBlur = jQuery.isNumeric(jQuery('input[name=planBlur]').val());
	var textBlur = jQuery.isNumeric(jQuery('input[name=textBlur]').val());

	jQuery('.fontSelectbox').val(font);

	if (planShadowOn.length == 1) {
		if (!planLeftRight || !planTopBottom) {
			alert('Fill all needed fields correctly');
			return;
		}
	}
	if (textShadowOn.length == 1) {
		if (!textLeftRight || !textTopBottom) {
			alert('Fill required fills');
			return;
		}
	}
	//Validate title field
	if(jQuery('.sgpt-title-input').val().replace(/\s/g, "").length <= 0){
		alert('Title field is required.');
		return;
	}
	var saveAction = 'PricingTable_ajaxSave';
	var ajaxHandler = new sgptRequestHandler(saveAction, form.serialize());
	ajaxHandler.dataIsObject = false;
	ajaxHandler.dataType = 'html';
	ajaxHandler.callback = function(response){
		//If success
		if(response) {
			//Response is pricing table id
			jQuery('input[name=sgpt-id]').val(response);
			jQuery('<div class="updated notice notice-success is-dismissible below-h2">' +
					'<p>Pricing table updated.</p>' +
					'<button type="button" class="notice-dismiss" onclick="jQuery(\'.notice\').remove();"></button></div>').appendTo('.sgpt-top-bar h1');
		}
		if (planNameColor || bgColor || featureColor|| buttonColor|| textNameColor|| textFeatureColor|| textButtonColor || font) {
				jQuery("div[class$='_type']").css('background-color',planNameColor);
				jQuery("div[class$='_plan']").css('background-color',bgColor);
				jQuery("div[class$='_content'] ul li").css('background-color',featureColor);
				jQuery("a[class$='_bottom']").css('background-color',buttonColor);
				jQuery("div[class$='_text']").css('color',textNameColor);
				jQuery("div[class$='_content'] ul li").css('color',textFeatureColor);
				jQuery("a[class$='_bottom").css('color',textButtonColor);
				if (font) {
					jQuery('.fontSelectbox').val(font);
					var nameColorFont = 'font-family:'+font+';color: '+textNameColor;
					var featureColorFont = 'font-family:'+font+';color: '+textFeatureColor;
					var buttonColorFont = 'font-family:'+font+';color: '+textButtonColor;
					changeFont(font);
				   jQuery("div[class$='_text']").attr("style",nameColorFont);
				   jQuery("div[class$='_content'] ul li").attr("style",textFeatureColor);
				   jQuery("div[class$='_plan']").attr("style",font);
				   jQuery("div[class$='_timeframe']").attr('style','font-family:'+font);
				   jQuery("a[class$='_bottom']").attr("style",textButtonColor);
				}
			}
	}
	ajaxHandler.run();
}

SGPT.prototype.clone = function(){
	var elementsCount = jQuery('.one-column').length;
	if(elementsCount >= SGPT.maxElementsCount){
		alert('Maximum number of plans has been exceeded');
		return;
	}
	var elementToClone = jQuery('.one-column').first();
	var elementToAppend = jQuery('.sgpt-tables-container');
	//var descriptionOfElements = jQuery('.sgpt-description-column').first();
	if(elementsCount%SGPT.elementsInARow == 0){
		//descriptionOfElements.clone().appendTo(elementToAppend);
	}
	var clonedElementId = elementsCount+1;
	var clonedElement = elementToClone.clone().find("input:text, input:radio, textarea").removeAttr('checked').val("").end().appendTo(elementToAppend).attr('id', 'sgpt-' + clonedElementId).removeClass('col-1').addClass('col-'+clonedElementId);
	//Add id to remove button
	clonedElement.find('.sgpt-remove-icon-button').removeAttr('onclick').attr('onclick', "SGPT.remove('sgpt-"+clonedElementId+"')");
	//Change radio button value
	clonedElement.find('.sgpt-radio-input').val(clonedElementId);
	//Change fake id value
	clonedElement.find('.sgpt-fake-id').val(clonedElementId);
	reorderColumnsAdmin();
};

SGPT.prototype.ajaxCloseBanner = function(){
	var closeAction = 'PricingTable_ajaxCloseBanner';
	var ajaxHandler = new sgptRequestHandler(closeAction);
	ajaxHandler.dataType = 'html';
	ajaxHandler.callback = function(response){
		//If success
		jQuery('.sg-banner-wrapper').remove();
	}
	ajaxHandler.run();
};

SGPT.ajaxDelete = function(id){
	if (confirm('Are you sure?')) {
		var deleteAction = 'PricingTable_ajaxDelete';
		var ajaxHandler = new sgptRequestHandler(deleteAction, {id: id});
		ajaxHandler.dataType = 'html';
		ajaxHandler.callback = function(response){
			//If success
			location.reload();
		}
		ajaxHandler.run();
	}
};

SGPT.ajaxSgptClone = function(id){
	if (confirm('Are you sure?')) {
		var cloneAction = 'PricingTable_ajaxSgptClone';
		var ajaxHandler = new sgptRequestHandler(cloneAction, {id: id});
		ajaxHandler.dataType = 'html';
		ajaxHandler.callback = function(response){
			//If success
			location.reload();
		}
		ajaxHandler.run();
	}
};

SGPT.remove = function(elementId){
	if (confirm('Are you sure?')) {
		var elementsCount = jQuery('.one-column').length;
		if (elementsCount <= SGPT.minElementsCount) {
			alert('At least ' + SGPT.minElementsCount + ' plans are needed');
			return;
		}

		jQuery('#' + elementId).remove();
		reorderColumnsAdmin();
	}
};

SGPT.ajaxSgptImport = function(attachmentUrl){
	jQuery('.sgpt-import-spinner').show();
	var cloneAction = 'PricingTable_ajaxSgptImport';
	var ajaxHandler = new sgptRequestHandler(cloneAction, {attachmentUrl: attachmentUrl});
	ajaxHandler.dataType = 'html';
	ajaxHandler.callback = function(response){
		//If success
		location.reload();
	}
	ajaxHandler.run();
};

function changeSize () {
	var containerWidth = jQuery('.sgpt-wrapper').innerWidth();
	var plansContainIn = Math.floor(containerWidth / 199);
	var result = (containerWidth-15*(plansContainIn-1))/plansContainIn;
	jQuery('.sgpt-plan-count').css('width', result);
}

function changeFont (fontName) {
	var font = fontName.replace(new RegExp(" ",'g'),"");
	var res = font.match(/[A-Z][a-z]+/g);
	var result = '';

	for (var i=0;i<res.length;i++) {
		result += res[i]+' ';
	}
	WebFontConfig = {
	google: { families: [ result.substr(0, result.length-1) ] }
  };
  (function() {
	var wf = document.createElement('script');
	wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
	  '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
	wf.type = 'text/javascript';
	wf.async = 'true';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(wf, s);

  })();
}

function reorderColumnsAdmin () {
	var allCols = jQuery('.sgpt-tables-container').find('div');
	var sgptIndex = 1;
	jQuery(allCols).each(function(){
		jQuery(this).removeClass(function(index, className) {
			return (className.match(/(^|\s)col-\S+/g) || []).join(' ');
		});
		jQuery(this).removeAttr('id');
		for (var i = 1; i<=allCols.length;i++) {
			jQuery(this).find('.sgpt-remove-icon-button').removeAttr('onclick').attr('onclick','SGPT.remove("sgpt-'+(sgptIndex)+'")');
			jQuery(this).attr('id','sgpt-'+(sgptIndex++));
			jQuery(allCols).each(function(){
				jQuery(this).addClass('col-'+(i++));
			});
		}
	});
}

function hgPricingSetCookie(name, value, options) {
	options = options || {};

	var expires = options.expires;

	if (typeof expires == "number" && expires) {
		var d = new Date();
		d.setTime(d.getTime() + expires * 1000);
		expires = options.expires = d;
	}
	if (expires && expires.toUTCString) {
		options.expires = expires.toUTCString();
	}


	if(typeof value == "object"){
		value = JSON.stringify(value);
	}
	value = encodeURIComponent(value);
	var updatedCookie = name + "=" + value;

	for (var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}

	document.cookie = updatedCookie;
}