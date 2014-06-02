$(document).ready(function(){
    $('#surveyParticipants').dataTable(
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
                     /*
                     "aoColumns": [
                        { "sWidth": "60px" },
                        { "sWidth": "45px" },
                        { "sWidth": "110px" },
                        { "sWidth": "170px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                     ]   
                     */
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/removeHandler',
            }
    ).yadcf([
        {column_number: 4},
        {column_number: 5},
        {column_number: 6},
        {column_number: 7},
        {column_number: 8},
        {column_number: 9},
    ]
    );
    
    $('#surveyPendingParticipants').dataTable(
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
                     /*
                     "aoColumns": [
                        { "sWidth": "60px" },
                        { "sWidth": "45px" },
                        { "sWidth": "110px" },
                        { "sWidth": "170px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                     ]   
                     */
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/removeHandler',
            }
    ).yadcf([
        {column_number: 4},
        {column_number: 5},
        {column_number: 6},
        {column_number: 7},
        {column_number: 8},
        {column_number: 9},
    ]
    );
/*    
    $('#removedParticipants').dataTable(
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
                     "aoColumns": [
                        { "sWidth": "70px" },
                        { "sWidth": "110px" },
                        { "sWidth": "170px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                     ]   
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/reinstateHandler',
            }
    ).yadcf([
        {column_number : 1 },
        {column_number : 2 },
        {column_number : 3 },
        {column_number : 4 },
        {column_number : 5 },
        {column_number : 6 },
        ]
    );
*/
    $('#notPresentParticipants').dataTable(
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
                     /*
                     "aoColumns": [
                        { "sWidth": "70px" },
                        { "sWidth": "110px" },
                        { "sWidth": "170px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                     ]   
                     */
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/reinstateHandler',
            }
    ).yadcf([
        {column_number : 3 },
        {column_number : 4 },
        {column_number : 5 },
        {column_number : 6 },
        {column_number : 7 },
        {column_number : 8 },
        ]
    );

    var editstatus = 0; 
    var editedRows = {};
    function update()
    {
        var editId = this.id;
        if(editstatus === 0)
        {
            // go through each column in the table
            $('#surveyParticipants td').each(function() {
                if($(this).attr('id') == editId)
                {
                        // get column text
                        var colData = $(this).text();
                        // get column class
                        var colClass = $(this).attr('class');
                        // get column id
                        var colId = $(this).attr('id');
                        // create input element                
                        var input = $('<td><input/></td>');
                        // fill it with data
                        input.find('input').val(colData).attr('class', colClass).attr('name', colClass).attr('id', colId);
                        // now. replace
                        $(this).replaceWith(input);
                } else{}
            });
                editstatus = 1;
        }
        else
        {
                var id = $('tbody').find('input').attr('id'),
                    firstName = $('tbody').find('input.firstName').val(),
                    lastName = $('tbody').find('input.lastName').val(),
                    userName = $('tbody').find('input.userName').val(),
                    email = $('tbody').find('input.email').val(),
                    instrument = $('tbody').find('input.instrument').val(),
                    playerNumber = $('tbody').find('input.playerNumber').val(),
                    desk = $('tbody').find('input.desk').val(),
                    playingPart = $('tbody').find('input.playingPart').val();
            
                $.ajax({
                dataType: 'json',
                        type: 'POST',
                        url: '/survey/participantsEditHandler',
                        data: {
                                id: id,
                                firstName: firstName,
                                lastName: lastName,
                                email: email,
                                userName: userName,
                                instrument: instrument,
                                playerNumber: playerNumber,
                                desk: desk,
                                playingPart: playingPart
                        },
                        success:function(data){
                        if (data.message == "success")
                        {
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
                
                // go through each input in the table
                $('tbody').find('input').each(function() {
                var colData = $('td').find('input').val();
                // get column class
                var colClass = $('td').find('input').attr('class');
                // get column id
                var colId = $('td').find('input').attr('id');
                // create td element
                var col = $('<td></td>');
                // fill it with data
                col.addClass(colClass).text(colData).attr('id', colId);
                // now. replace
                $(this).replaceWith(col);
                 });
                editstatus = 0;
        }
    }
    $('.editpts').live('click', update);      
   
    $('a.remove').live('click', function(){
            var removeId = this.id;
            var participant = $('#' + this.id + '.userName').text();
            var message = 'Are you sure you want to remove participant ' + participant + ' ?';
            $('#confirmRemove').html(message).data('id', removeId).dialog('open');    
            return false;
    });
    
    $('#confirmRemove').dialog({
        autoOpen: false,
        title: 'Remove Confirmation',
        width: 'auto',
        resizable: false,
        modal: true,
        buttons: {
            Yes: function() {
                $(this).dialog("close");
                var removeId = $(this).data('id');
                var surveyId = $('input#surveyId').val();

                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/participantsRemoveHandler',
                data: {
                    removeId: removeId,
                    surveyId: surveyId
                }, 
                
                success:function(data){
                    if(data.message == "success")
                    {
                        var active = $('#surveyParticipants').dataTable();
                        var notPresent = $('#notPresentParticipants').dataTable();
                        var rowToRemove = $('#surveyParticipants').find('tr#' + data.id);
                        active.fnDeleteRow(rowToRemove[0]);          
                        
                        notPresent.fnAddData([
                           '<a href="#" id="' + data.id + '" class="invite">Invite</a></td>', 
                           data.firstName,
                           data.lastName,
                           data.userName,
                           data.email,
                           data.instrument,
                           data.playerNumber,
                           data.desk,
                           data.playingPart
                         ]
                         );
                         $('#notPresentParticipants tr:last').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#notPresentParticipants tr:last').children('td:nth-child(2)').attr('class', 'firstName').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(3)').attr('class', 'lastName').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(4)').attr('class', 'userName').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(5)').attr('class', 'email').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(6)').attr('class', 'instrument').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(7)').attr('class', 'playerNumber').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(8)').attr('class', 'desk').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(9)').attr('class', 'playingPart').attr('id', data.id);
                         active.fnFilterOnReturn();
                         notPresent.fnFilterOnReturn();
        
                         active.fnDraw();
                         notPresent.fnDraw();
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
    
    
    function pendingUpdate()
    {
        var editId = this.id;
        if(editstatus === 0)
        {
            // go through each column in the table
            $('#surveyPendingParticipants td').each(function() {
                if($(this).attr('id') == editId)
                {
                        // get column text
                        var colData = $(this).text();
                        // get column class
                        var colClass = $(this).attr('class');
                        // get column id
                        var colId = $(this).attr('id');
                        // create input element                
                        var input = $('<td><input/></td>');
                        // fill it with data
                        input.find('input').val(colData).attr('class', colClass).attr('name', colClass).attr('id', colId);
                        // now. replace
                        $(this).replaceWith(input);
                } else{}
            });
                editstatus = 1;
        }
        else
        {
                var id = $('tbody').find('input').attr('id'),
                    firstName = $('tbody').find('input.firstName').val(),
                    lastName = $('tbody').find('input.lastName').val(),
                    userName = $('tbody').find('input.userName').val(),
                    email = $('tbody').find('input.email').val(),
                    instrument = $('tbody').find('input.instrument').val(),
                    playerNumber = $('tbody').find('input.playerNumber').val(),
                    desk = $('tbody').find('input.desk').val(),
                    playingPart = $('tbody').find('input.playingPart').val();
            
                $.ajax({
                dataType: 'json',
                        type: 'POST',
                        url: '/survey/participantsEditHandler',
                        data: {
                                id: id,
                                firstName: firstName,
                                lastName: lastName,
                                email: email,
                                userName: userName,
                                instrument: instrument,
                                playerNumber: playerNumber,
                                desk: desk,
                                playingPart: playingPart
                        },
                        success:function(data){
                        if (data.message == "success")
                        {
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
                
                // go through each input in the table
                $('tbody').find('input').each(function() {
                var colData = $('td').find('input').val();
                // get column class
                var colClass = $('td').find('input').attr('class');
                // get column id
                var colId = $('td').find('input').attr('id');
                // create td element
                var col = $('<td></td>');
                // fill it with data
                col.addClass(colClass).text(colData).attr('id', colId);
                // now. replace
                $(this).replaceWith(col);
                 });
                editstatus = 0;
        }
    }
    $('.peditpts').live('click', pendingUpdate);      
    
     
    $('a.premove').live('click', function(){
            var removeId = this.id;
            var participant = $('#' + this.id + '.userName').text();
            var message = 'Are you sure you want to remove participant ' + participant + ' ?';
            $('#pconfirmRemove').html(message).data('id', removeId).dialog('open');    
            return false;
    });
    
    $('#pconfirmRemove').dialog({
        autoOpen: false,
        title: 'Remove Confirmation',
        width: 'auto',
        resizable: false,
        modal: true,
        buttons: {
            Yes: function() {
                $(this).dialog("close");
                var removeId = $(this).data('id');
                var surveyId = $('input#psurveyId').val();

                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/participantsRemoveHandler',
                data: {
                    removeId: removeId,
                    surveyId: surveyId
                }, 
                
                success:function(data){
                    if(data.message == "success")
                    {
                        var pending = $('#surveyPendingParticipants').dataTable();
                        var notPresent = $('#notPresentParticipants').dataTable();
                        var rowToRemove = $('#surveyPendingParticipants').find('tr#' + data.id);
                        pending.fnDeleteRow(rowToRemove[0]);          
                        
                        notPresent.fnAddData([
                           '<a href="#" id="' + data.id + '" class="invite">Invite</a></td>', 
                           data.firstName,
                           data.lastName,
                           data.userName,
                           data.email,
                           data.instrument,
                           data.playerNumber,
                           data.desk,
                           data.playingPart
                         ]
                         );
                         $('#notPresentParticipants tr:last').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#notPresentParticipants tr:last').children('td:nth-child(2)').attr('class', 'firstName').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(3)').attr('class', 'lastName').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(4)').attr('class', 'userName').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(5)').attr('class', 'email').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(6)').attr('class', 'instrument').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(7)').attr('class', 'playerNumber').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(8)').attr('class', 'desk').attr('id', data.id);
                         $('#notPresentParticipants tr:last').children('td:nth-child(9)').attr('class', 'playingPart').attr('id', data.id);
                         pending.fnFilterOnReturn();
                         notPresent.fnFilterOnReturn();
        
                         pending.fnDraw();
                         notPresent.fnDraw();
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
    
   
    $('a.invite').live('click', function(){
            var inviteId = this.id;
            var participant = $('#' + this.id + '.userName').text();
            var message = 'Are you sure you want to invite this participant ' + participant + ' ?';
            $('#confirmInvite').html(message).data('id', inviteId).dialog('open');    
            return false;
    });
    
    $('#confirmInvite').dialog({
        autoOpen: false,
        title: 'Invite Confirmation',
        width: 'auto',
        resizable: false,
        modal: true,
        buttons: {
            Yes: function() {
                $(this).dialog("close");
                var inviteId = $(this).data('id');
                var surveyId = $('input#surveyId').val();
                
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/participantsInviteHandler',
                data: 
                {
                    inviteId: inviteId,
                    surveyId: surveyId
                },
                
                success:function(data){
                    if(data.message == "success" && data.status == "active")
                    {
                         var active = $('#surveyParticipants').dataTable();
                         var notPresent = $('#notPresentParticipants').dataTable();
                         var rowToInvite = $('#notPresentParticipants').find('tr#' + data.id);                
                         notPresent.fnDeleteRow(rowToInvite[0]);
                         
                         active.fnAddData([
                           '<a href="#" id="' + data.id + '" class="remove">Remove</a></td>', 
                           '<a href="#" id="' + data.id + '" class="editpts">Edit</a></td>', 
                           data.firstName,
                           data.lastName,
                           data.userName,
                           data.email,
                           data.instrument,
                           data.playerNumber,
                           data.desk,
                           data.playingPart,
                         ]
                         );
  
                         $('#surveyParticipants tr:last').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#surveyParticipants tr:last').children('td:nth-child(2)').attr('class', 'changers');
                         $('#surveyParticipants tr:last').children('td:nth-child(3)').attr('class', 'firstName').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(4)').attr('class', 'lastName').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(5)').attr('class', 'userName').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(6)').attr('class', 'email').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(7)').attr('class', 'instrument').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(8)').attr('class', 'playerNumber').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(9)').attr('class', 'desk').attr('id', data.id);
                         $('#surveyParticipants tr:last').children('td:nth-child(10)').attr('class', 'playingPart').attr('id', data.id);
                         active.fnFilterOnReturn();
                         notPresent.fnFilterOnReturn();
  
                         active.fnDraw();
                         notPresent.fnDraw();
                  
                    }
                    else if(data.message == "success" && data.status == "new")
                    {
                         var pending = $('#surveyPendingParticipants').dataTable();
                         var notPresent = $('#notPresentParticipants').dataTable();
                         var rowToInvite = $('#notPresentParticipants').find('tr#' + data.id);                
                         notPresent.fnDeleteRow(rowToInvite[0]);
                         
                         pending.fnAddData([
                           '<a href="#" id="' + data.id + '" class="premove">Remove</a></td>', 
                           '<a href="#" id="' + data.id + '" class="peditpts">Edit</a></td>', 
                           data.firstName,
                           data.lastName,
                           data.userName,
                           data.email,
                           data.instrument,
                           data.playerNumber,
                           data.desk,
                           data.playingPart,
                         ]
                         );
  
                         $('#surveyPendingParticipants tr:last').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(2)').attr('class', 'changers');
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(3)').attr('class', 'firstName').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(4)').attr('class', 'lastName').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(5)').attr('class', 'userName').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(6)').attr('class', 'email').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(7)').attr('class', 'instrument').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(8)').attr('class', 'playerNumber').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(9)').attr('class', 'desk').attr('id', data.id);
                         $('#surveyPendingParticipants tr:last').children('td:nth-child(10)').attr('class', 'playingPart').attr('id', data.id);
                         pending.fnFilterOnReturn();
                         notPresent.fnFilterOnReturn();
  
                         pending.fnDraw();
                         notPresent.fnDraw();
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
 
