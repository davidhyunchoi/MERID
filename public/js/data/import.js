$(document).ready(function(){
    // This portion is the file extension validation
   $('#import').attr('disabled','disabled'); 
   $('#uploadedfile').change(function()
   {
        var str = $('#uploadedfile').val();
        var suffix='.txt';
        if(!(str.indexOf(suffix, str.length - suffix.length) !== -1))
        {
            str = '';
            $('#import').attr('disabled','disabled'); 
            $('#msg').html('This is not an accepted extension! Only .txt extension is allowed!');
        }
        else
        {
             $('#import').removeAttr('disabled');
             $('#msg').html('');
        }
   });
   //////////////////////////////////////////////////////////
   function importRequest()
   {
        var formData = new FormData($(this)[0]);
        var type = $('#type').val();
        $.ajax({
        dataType: 'json',
        type: 'POST',
        url: '/data/importHandler',
        data: formData,
        cache: false,
        async: false,
        contentType: false,
        processData: false,
        
        success: function(data) {
                // on success it shows success
                if (data.message == "success")
                { 
                   $("#success").show();
                   $("#success").fadeOut(3000);
                   $('#badcontent').text('');
                }
                else
                {
                    // it shows bad datasets
                    var badContent = "";
                    for(var i=0; i<data.badData.length; i++)
                    {
                        badContent = badContent.concat(data.badData[i] + '\n');
                    }
                    $("#warning").show();
                    $("#warning").fadeOut(20000)
                    $('#badcontent').text(badContent);
                }
        },
        error: function() {
            $("#failure").show();
            $("#failure").fadeOut(10000);
            $('#badcontent').text('');
        }
        });
        return false;
   }
   $('form#importMerid').on('submit', importRequest);
});