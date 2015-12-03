function captcha() {
  var x = Math.round(Math.random() * 10000);
  $("div.captcha").html("<img src=\"/inc/captcha.php?random=" + x + "\" alt=\"Оп\" title=\"Оп\"><input type=\"text\" name=\"captcha\" value=\"\">");
}

$( function() {

  $(".captcha").on("click", "img", function() {
    captcha();
  });
  
  $(".content").on("submit", "form", function(e) {
    e.preventDefault();
    var x_method = $(this).attr("method");
    var x_action = $(this).attr("action");
    var x_data = $(this).serialize();
    //alert("method: " + x_method + " action: " + x_action + " data: " + x_data);
    $.ajax({
      type: x_method ,
      url: x_action,
      data: x_data,
      dataType: "json",
      success: function(data) {
        if (data["answer"] == true) {
          $(".result").html(data["solution"]);
          setTimeout(function() {
            location.href = "/index.php?user=page";
          },2000);
        } else if (data["answer"] == false) {
          if (data["lc"] > 5) {
            captcha();
          }
          
          if (data["feedback"] == true)  $(".result").html(data["solution"]);

        
          $(".result").html(data["error"]);
        }
      }
    });
  });
});
