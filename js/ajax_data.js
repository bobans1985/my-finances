$(document).ready(function() {
 //Грузим курсы  
    $.ajax({
    type: "post",
      url: "/inc/ajax_data.php",
      data: "&cbr_course=return",
      async: true,
      dataType: "json",
      success: function(data) { 
        var count = data.length;
        var ids = "";
        for (i = 0; i < count; i++) {
          ids = "#cbr_course";
          $(ids).html(data[i]["content"]);
        }
      },
       error: function (res) {
        alert('Unable to load cbr course ');
      }
    });
    
    
 //Грузим данные 
  $.ajax({
    type: "post",
      url: "/inc/ajax_data.php",
      data: "&accounts=return",
      async: true,
      dataType: "json",
      success: function(data) {
        var count = data.length;
        var ids = "";
        for (i = 0; i < count; i++) {
          ids = "#" + data[i]["id"];
          $(ids).html(data[i]["content"]);
        }
      },
      error: function (res) {
        alert('Unable to load data from server ');
      }
    });
        
               
});
