var current_index = 0;
var interval = 5;	//seconds
var duration = 750;
var timerID = 0;

$(document).ready(function(){
	if (typeof(images) != "undefined")
	{
		$('#next_image').attr('src', images[nextIndex()]);
		$('#current_image').attr('src', images[current_index]);
		setTimeout(function() { doTransition( nextIndex() ); }, interval*1000);
		fixRotatingImages();
	}
});

function nextIndex() {
	return (current_index==max_index) ? 0 : current_index+1;
}

function doTransition(newid) {
	$('#next_image').attr( 'src', images[newid] );
	$('#current_image').fadeTo( duration, 0, function(){
		$(this).attr( 'src', images[current_index] ).fadeTo(duration, 1);
	});
	current_index = newid;
	setTimeout(function() { doTransition( nextIndex() ); }, interval*1000);
}

function fixRotatingImages(){
	$('#rotating_images').css('height',$('#rotating_images').width()+"px");
	$('#rotating_images img').css('height',$('#rotating_images img').width()+"px");
}

$(window).resize(function(){
	fixRotatingImages();
});