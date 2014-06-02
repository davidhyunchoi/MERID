$(document).ready(function(){
    $('#currentRecording').dataTable(
            {
                     "bSort": true,
                     "bPaginate": true,
                     "bFilter": true,
                     "bJQueryUI": true,
                     "bAutoWidth": false,
                     "bLengthChange": false,
                     "bSortable": false,
                     "bRender": true,
                     "bDestroy": true,
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/removeHandler',
            }
    ).yadcf([
        {column_number: 0},
        {column_number: 1},
        {column_number: 2},
        {column_number: 3},
    ]
    );
    
    $('#availableRecordings').dataTable(
            {
                     "bSort": true,
                     "bPaginate": true,
                     "bFilter": true,
                     "bJQueryUI": true,
                     "bAutoWidth": false,
                     "bLengthChange": false,
                     "bSortable": false,
                     "bRender": true,
                     "bDestroy": true,
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/removeHandler',
            }
    ).yadcf([
        {column_number: 1},
        {column_number: 2},
        {column_number: 3},
        {column_number: 4},
    ]
    );
    
    $('a.select').live('click', function(){
            var selectId = this.id;
            var surveyName = $('#survey').attr('class');
            var message = 'Are you sure you want to select this recording for survey ' + surveyName + ' ?';
            $('#confirmSelect').html(message).data('id', selectId).dialog('open');    
            return false;
    });
    
    $('#confirmSelect').dialog({
        autoOpen: false,
        title: 'Select Confirmation',
        width: 'auto',
        resizable: false,
        modal: true,
        buttons: {
            Yes: function() {
                $(this).dialog("close");
                var selectId = $(this).data('id');
                var surveyId = $('#survey').val();    
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/selectRecordingHandler',
                data: {
                    selectId: selectId,
                    surveyId: surveyId
                }, 

                success:function(data){
                    if(data.message == "success")
                    {
                         var current = $('#currentRecording').dataTable();
                         var available = $('#availableRecordings').dataTable();
                         var rowToSelect = $('#availableRecordings').find('tr#' + data.id);   
                         var rowToReplace = $('#currentRecording tbody').find('tr');
                         
                         var id = rowToReplace.attr('id');
                         var projects = rowToReplace.children('td:nth-child(1)').text();
                         var title = rowToReplace.children('td:nth-child(2)').text();
                         var location = rowToReplace.children('td:nth-child(3)').text();
                         var recordingTime = rowToReplace.children('td:nth-child(4)').text();
                         
                         current.fnDeleteRow(rowToReplace[0]);
                         available.fnAddData([
                           '<a href="#" id="' + id + '" class="select">Select</a></td>', 
                            projects,
                            title, 
                            location,
                            recordingTime
                         ]);
                         $('#availableRecordings tr:last').attr('id', id);
                         $('#availableRecordings tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#availableRecordings tr:last').children('td:nth-child(2)').attr('class', 'projects').attr('id', id);
                         $('#availableRecordings tr:last').children('td:nth-child(3)').attr('class', 'title').attr('id', id);
                         $('#availableRecordings tr:last').children('td:nth-child(4)').attr('class', 'location').attr('id', id);
                         $('#availableRecordings tr:last').children('td:nth-child(5)').attr('class', 'recordingTime').attr('id', id);
                         
                         
                         available.fnDeleteRow(rowToSelect[0]);
                         current.fnAddData([
                           data.projects,
                           data.title,
                           data.location,
                           data.recordingTime
                         ]
                         );
                         $('#currentRecording tr:last').attr('id', data.id);
                         $('#currentRecording tr:last').children('td:nth-child(1)').attr('class', 'projects').attr('id', data.id);
                         $('#currentRecording tr:last').children('td:nth-child(2)').attr('class', 'title').attr('id', data.id);
                         $('#currentRecording tr:last').children('td:nth-child(3)').attr('class', 'location').attr('id', data.id);
                         $('#currentRecording tr:last').children('td:nth-child(4)').attr('class', 'recordingTime').attr('id', data.id);
                         current.fnFilterOnReturn();
                         available.fnFilterOnReturn();
                         current.fnDraw();
                         available.fnDraw();
                         
                    }
                    else
                    {
                    }
                },
                error:function(){
                // failed request; give feedback to user
                   // alert('Unsuccessful return!');
                }
                });
                
            },
            No: function() {
                $(this).dialog("close");
            }
        }
    });
});
 
