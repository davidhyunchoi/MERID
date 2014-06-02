
$(document).ready(function(){
    var videoTemplate = $("#videoTemplate"),
        pieceTemplate = $("#pieceTemplate"),
        addPieceBtn = $("#addPiece"),
        addVideoBtn = $("#addVideo"),
        submitBtn = $("#submitRecording"),
        valid = "valid",
        pieceCount = 1,
        videoCount = 1,
        projectTable;
        
    submitBtn.on('click', function(event){
        submitHandler();
    });
    
    addPieceBtn.on('click', function(event){
        pieceCount = pieceCount + 1;
        $(pieceTemplate.html()).insertBefore(addPieceBtn).find('.pieceCount').text(pieceCount);
    });
    
    addVideoBtn.on('click', function(event){
        if(videoCount >= 5){
            alert('Cannot have more than five videos per recording!');
            return;
        }
        videoCount = videoCount + 1;
        $(videoTemplate.html()).insertBefore(addVideoBtn).find('.videoCount').text(videoCount);
    });
    
    $("#projects tbody tr").click( function( e ) {
        if ( $(this).hasClass('row_selected') ) {
            $(this).removeClass('row_selected');
            $(this).find('.rowSelect').prop('checked', false);
        }
        else{
            $(this).addClass('row_selected');
            $(this).find('.rowSelect').prop('checked', true);
        }
    });
    
    projectTable = $('#projects').dataTable({
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 0 ] }
        ]
    });
    
    function submitHandler(){
        var message = verifyForm();
        
        if(message.msg !== valid){
            return;
        }
        var recording = $('#addRecordingForm').serializeObject();
        recording.pieces = [];
        $('.addRecordingPiece').each(function(i, el){
                recording.pieces[i]= $(el).serializeObject();
        });
        recording.videos = [];
        $('.addRecordingVideo').each(function(i, el){
                recording.videos[i]= $(el).serializeObject();
        });
        recording.projects = [];
        projectTable.$('tr.row_selected').each(function(i, el){
            recording.projects[i] = $(el).attr('id');
        });
                
        for(var v in recording.videos){
            var startTime = recording.videos[v].startTime.split(":"),
                endTime = recording.videos[v].endTime.split(":");
            recording.videos[v].startTime = parseInt(startTime[0]*3600) + parseInt(startTime[1]*60) + parseInt(startTime[2]);
            recording.videos[v].endTime = parseInt(endTime[0]*3600) + parseInt(endTime[1]*60) + parseInt(endTime[2]);  
        }
        
        $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/recording/addHandler',
                data: JSON.stringify(recording), 
                success:function(data){
                    window.location.replace("/recording/viewAll");
                },
                error:function(){
                
                }
        });
    }
    
    function verifyForm(){
        var message = {};
        message.msg = valid;
        return message;
    }
    
    function getBaseUrl(){
        var pathArray = window.location.href.split( '/' );
        var protocol = pathArray[0];
        var host = pathArray[2];
        return protocol + '://' + host;
    }
    
    $.fn.serializeObject = function()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
});
 
