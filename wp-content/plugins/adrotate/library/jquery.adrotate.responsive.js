/****************************************************************************************
 * Dynamic responsive adverts for AdRotate												*
 * Arnan de Gans from AJdG Solutions (http://meandmymac.net, http://ajdg.solutions)		*
 * Version: 0.5														   					*
 * With help from: Mathias Joergensen (http://www.moofy.me)								*
 * Original code: N/a																	*
 ****************************************************************************************/

/* ------------------------------------------------------------------------------------
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2008-2015 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from it's use.
------------------------------------------------------------------------------------ */

jQuery(document).ready(function() {
	var resizeTimer;
	var $window = jQuery(window);

	function check_file_exists(fileToCheck) {
		var tmp = new Image;
		tmp.src = fileToCheck;

		if(!tmp.complete) {
			fileToCheck = fileToCheck.replace('.320', '.full').replace('.480', '.full').replace('.768', '.full').replace('.1024', '.full');
		}

		return fileToCheck;
	}

    function responsive_imageswap() {
		jQuery('img.responsive').each(function() {
			var $thisImg = jQuery(this);
			var newSrc = $thisImg.attr('src');

			if($window.width() <= 320) {
				// Max 320 viewport
				newSrc = newSrc.replace('.480', '.320').replace('.768', '.320').replace('.1024', '.320').replace('.full', '.320');	
			} else if($window.width() > 320 && $window.width() <= 480) {
				 // Max 480 viewport
				newSrc = newSrc.replace('.320', '.480').replace('.768', '.480').replace('.1024', '.480').replace('.full', '.480');
			} else if($window.width() > 480 && $window.width() <= 768) {
				 // Max 768 viewport
				newSrc = newSrc.replace('.320', '.768').replace('.480', '.768').replace('.1024', '.768').replace('.full', '.768');
			} else if($window.width() > 768 && $window.width() <= 1024) {
				 // Max 1024 viewport
				newSrc = newSrc.replace('.320', '.1024').replace('.480', '.1024').replace('.768', '.1024').replace('.full', '.1024');
			} else {
				 // Larger than 1024 viewport
				newSrc = newSrc.replace('.320', '.full').replace('.480', '.full').replace('.768', '.full').replace('.1024', '.full');
			}

			newSrc = check_file_exists(newSrc);
			$thisImg.attr('src', newSrc);

		});
	}
	responsive_imageswap();

	$window.resize(function() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(responsive_imageswap, 150);
	});

	$window.ready(function() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(responsive_imageswap, 150);
	});
});