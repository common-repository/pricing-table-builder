jQuery(document).ready(function(){
	changeSize();
});

jQuery(window).resize(function(){
	changeSize();
});

function changeSize () {
	var containerWidth = jQuery('.sgpt-wrapper').innerWidth();
	var plansContainIn = Math.floor(containerWidth / 199);
	var result = (containerWidth-12*(plansContainIn))/plansContainIn;
	jQuery('.sgpt-plan-count').css('width', result);
}
