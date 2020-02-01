$(document).ready(init);

var PROTOCOL = window.location.protocol;
var HOST = window.location.hostname;
var PATH = "Dialogflow/Link";


function init()
{
    attachEventHandlers();
}

function attachEventHandlers()
{
    $('#submit-button').click(submit);
    $('.delete-stuff').hide();

    $('#show-create').click(function(){
          $('.create-stuff').show();
          $('.delete-stuff').hide();
    });

    $('#show-delete').click(function(){
          $('.delete-stuff').show();
          $('.create-stuff').hide();
    });

    $('#retrieve-project-button').click(retrieveProjectId);
    $('#delete-button').click(deleteMapping);



}

function deleteMapping()
{
    var phone = $('#avaya-cloud-number').val();
    if(phone == undefined) {
        return;
    }

    var projectId = $('#project-id').val();
    if(projectId == undefined) {
        return;
    }

    var yes = confirm("Are you sure you want to remove this mapping?");
    if(! yes) {
        return;
    }

    $.get(PROTOCOL + "//" + HOST + "/" + PATH + "/deleteProjectMapping.php?phone=" + phone + "&project_id=" + projectId , function(data) {
          console.log(data);
          if(data == "Success") {
              alert("Successfully unmapped.");
              $('#project-id').val('');
          }

          if(data == "NotMapped") {
              alert("Project and phone number combination was not mapped.");
          }

    });
}

function retrieveProjectId()
{
    var phone = $('#avaya-cloud-number').val();
    if(phone == undefined) {
        return;
    }

    $.get(PROTOCOL + "//" + HOST + "/" + PATH + "/retrieveProjectId.php?phone=" + phone , function(data) {
          console.log(data);
          var json = JSON.parse(data);
          if(json && json.projectId != "<NONE>") {
              $('#project-id').val(json.projectId);
              $('#webhook').val(json.webhook);
              $('#welcome-intent-event-name').val(json.welcome_intent);
          } else {
              $('#project-id').val('');
              $('#webhook').val("");
          }
    });
}

function submit()
{
      var phone = $('#avaya-cloud-number').val();
      var projectId = $('#project-id').val();
      var eventName = $('#welcome-intent-event-name').val();
      var webhook = $('#webhook').val();

      if(phone == undefined || phone == "") {
          alert("Must enter a phone number!");
          return;
      }

      if(projectId == undefined || projectId == "") {
          alert("Must enter a project Id!");
          return;
      }

      if(eventName == undefined || eventName == "") {
          var yay = confirm("If you continue without an event name, the voice portion of your project will require the caller to say something to trigger the bot.  Continue?");
          if(! yay) {
              return;
          }
      }

      if(webhook == undefined || webhook == "") {
          var yayW = confirm("If you continue without a web hook, you will not be able to access the default Avaya Cloud Request parameters.  Continue?");
          if(! yayW) {
              return;
          }
      }

      var file = document.getElementById("project-credentials").files[0];
      if(file == undefined || file == "") {
          alert("Must enter a file!");
          return;
      }

      if (file) {
        var reader = new FileReader();
        reader.readAsText(file, "UTF-8");
        reader.onload = function (evt) {
            console.log("Result: " , evt.target.result);

            var json = JSON.parse(evt.target.result);
            sendIt(phone , projectId , json , eventName , webhook);
            console.log("Json: " , json);
            $('#project-credentials').val('');
        }
        reader.onerror = function (evt) {
            console.log("error reading file");
        }
    }
}

function sendIt(phone , projectId , file , eventName , webhook)
{
    $.ajax({
          url : PROTOCOL + "//" + HOST + "/" + PATH + "/link.php?phone=" + phone + "&projectId=" + projectId + "&eventName=" + eventName + "&webhook=" + encodeURIComponent(webhook) ,
          method : "POST" ,
          data : JSON.stringify(file) ,
          success : function(data) {
              alert("Successfully mapped project.");
              console.log("Success: " , data);
          } ,
          error : function(data) {
              alert("Error mapping project.");
              console.log("Error: " , data);
          }
     });
}
