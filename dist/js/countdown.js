/* Credit: https://codepen.io/charlenopires/pen/JMoxdb */
var countdownInterval;
var countdown = 0;

function leadZero(i) {
	return i < 10 ? "0" + i.toString() : i;
}

function start_countdown(time) {
	clearInterval(countdownInterval);

	$("#countdown svg circle").css("display", "inline").css("animation","none");
	setTimeout(function() {
		$("#countdown svg circle").css("animation","countdown " + parseInt(time) + "s linear forwards");
	},10);
	$("#countdown-number").html(leadZero(time));

	countdown = time;

	$("#countdown").fadeIn();

	countdownInterval = setInterval(function() {
	  countdown = --countdown <= 0 ? 0 : countdown;

	  $("#countdown-number").html(leadZero(countdown));
	  if(countdown == 0) {
	  	clearInterval(countdownInterval);
	  }
	}, 1000);
}