$(document).ready(function() {
  
  var animating = false,
      submitPhase1 = 1100,
      submitPhase2 = 400,
      logoutPhase1 = 800,
      $login = $(".login"),
      $app = $(".app"),
      $appr = $("#appr"),
      $appn = $("#appn"),
	  newr = $("#newr").html(),
	  session = $("#session").html();
	
	if(session){
		if (animating) return;
		animating = true;
		var that = $('#login');
		ripple($(that), 1);
		$(that).addClass("processing");
		setTimeout(function() {
		  $(that).addClass("success");
		  setTimeout(function() {
			  if(newr){
				$appr.show();
				$appn.hide();
				$appr.css("top");
				$appr.addClass("active");
			  }else{
				$appn.show();
				$appr.hide();
				$appn.css("top");
				$appn.addClass("active");
			  }
		  }, submitPhase2 - 70);
		  setTimeout(function() {
			//$login.hide();
			$login.addClass("inactive");
			animating = false;
			$(that).removeClass("success processing");
		  }, submitPhase2);
		}, submitPhase1);
	}
  
  function ripple(elem, e) {
    $(".ripple").remove();
    var elTop = elem.offset().top,
        elLeft = elem.offset().left,
        x = e.pageX - elLeft,
        y = e.pageY - elTop;
    var $ripple = $("<div class='ripple'></div>");
    $ripple.css({top: y, left: x});
    elem.append($ripple);
  };
  
  $(document).on("submit", "#LI-form",function(ev){
	  if (animating) return;
		animating = true;
		var that = $('#verify_code');
		ripple($(that), 1);
		$(that).addClass("processing");
		var data = $("#LI-form").serialize();
		$.post('check_user.php', data, function(data,status){
			if( data == "done"){
				$(that).addClass("success");
				setTimeout(function() {
					window.location = 'dashboard.php';
				},5000);
			}
			else{
				alert(data);
			}
		});
  });
  
  $(document).on("submit", "#SI-form",function(ev){
	  if (animating) return;
		animating = true;
		var that = $('#verify_code');
		ripple($(that), 1);
		$(that).addClass("processing");
		var data = $("#SI-form").serialize();
		$.post('check_user.php', data, function(data,status){
			if( data == "done"){
				$(that).addClass("success");
				setTimeout(function() {
					window.location = 'dashboard.php';
				},5000);
			}
			else{
				alert(data);
			}
		});
  });
  
  $(document).on("submit", "#login-form", function(e) {	
	var data = $("#login-form").serialize();
	$.post('check_user.php', data, function(data,status){
		if( data == "done"){
			window.location = 'login.php';
		}else{}
			alert(data);
		}
	});
  });
  
  $(document).on("submit", "#signup-form", function(e) {	
	var data = $("#signup-form").serialize();
	$.post('check_user.php', data, function(data,status){
		if( data == "done"){
			window.location = 'login.php';
		}else{
			alert(data);
		}
	});
  });
  
  $(document).on("click", "#signup-link", function(e) {
		if (animating) return;
		$su = $('#signup-form');
		$li = $('#login-form');
		animating = true;
		var that = $('body');
		ripple($(that), e);
		setTimeout(function() {
		$su.show();
		$su.css("top");
		$su.addClass("active");
		$li.hide();
		}, submitPhase2 - 70);
		setTimeout(function() {
			animating = false;
		}, submitPhase2);
		
  });

  $(document).on("click", "#login-link", function(e) {
		if (animating) return;
		$su = $('#signup-form');
		$li = $('#login-form');
		animating = true;
		var that = $('body');
		ripple($(that), e);
		setTimeout(function() {
			$li.show();
			$li.css("top");
			$li.addClass("active");
			$su.hide();
		}, submitPhase2 - 70);
		setTimeout(function() {
			animating = false;
		}, submitPhase2);
  });
  
  $(document).on("click", ".app__logout", function(e) {
    if (animating) return;
	$.post('logout.php', function(data,status){
	    $(".ripple").remove();
		animating = true;
		var that = this;
		$(that).addClass("clicked");
		setTimeout(function() {
		  $appr.removeClass("active");
		  $login.show();
		  $login.css("top");
		  $login.removeClass("inactive");
		}, logoutPhase1 - 120);
		setTimeout(function() {
		  $app.hide();
		  $appr.hide();
		  animating = false;
		  $(that).removeClass("clicked");
		}, logoutPhase1);
	});
  });
  
});