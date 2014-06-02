$(document).ready(function(){
    $('#projectParticipants').dataTable(
            {
                     "bSort": true,
                     "bPaginate": true,
                     "bFilter": true,
                     "bJQueryUI": true,
                     "bAutoWidth": true,
                     "bLengthChange": true,
                     "bSortable": false,
                     "bRender": true,
                     "bDestroy": true,
                     /*
                     "aoColumns": [
                        { "sWidth": "60px" },
                        { "sWidth": "45px" },
                        { "sWidth": "90px" },
                        { "sWidth": "90px" },
                        { "sWidth": "90px" },
                        { "sWidth": "110px" },
                        { "sWidth": "90px" },
                        { "sWidth": "90px" },
                        { "sWidth": "90px" },
                        { "sWidth": "90px" }
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
    
    $('#removedParticipants').dataTable(
            {
                     "bSort": true,
                     "bPaginate": true,
                     "bFilter": true,
                     "bJQueryUI": true,
                     "bAutoWidth": false,
                     "bLengthChange": true,
                     "bSortable": false,
                     "bRender": true,
                     "bDestroy": true,
                     /*
                     "aoColumns": [
                        { "sWidth": "80px" },
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
    
    $('#pendingParticipants').dataTable(
            {
                     "bSort": true,
                     "bPaginate": true,
                     "bFilter": true,
                     "bJQueryUI": true,
                     "bAutoWidth": false,
                     "bLengthChange": true,
                     "bSortable": false,
                     "bRender": true,
                     "bDestroy": true,
                     /*
                     "aoColumns": [
                        { "sWidth": "110px" },
                        { "sWidth": "170px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" },
                        { "sWidth": "110px" }
                     ] 
                     */
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/removeHandler',
            }
    ).yadcf([
        {column_number: 2},
        {column_number: 3},
        {column_number: 4},
        {column_number: 5},
        {column_number: 6},
        {column_number: 7},
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
            $('#projectParticipants td').each(function() {
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
                    email = $('tbody').find('input.email').val(),
                    userName = $('tbody').find('input.userName').val(),
                    instrument = $('tbody').find('input.instrument').val(),
                    playerNumber = $('tbody').find('input.playerNumber').val(),
                    desk = $('tbody').find('input.desk').val(),
                    playingPart = $('tbody').find('input.playingPart').val();
            
                $.ajax({
                dataType: 'json',
                        type: 'POST',
                        url: '/projectMember/editHandler',
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
                        $('#msg').html('Failed changes!');
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
                var value = $('input.hidden').val(removeId);
              
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/projectMember/removeHandler',
                data: value, 
                
                success:function(data){
                    if(data.message == "success")
                    { 
                        var active = $('#projectParticipants').dataTable();
                        var removed = $('#removedParticipants').dataTable();
                        var rowToRemove = $('#projectParticipants').find('tr#' + data.id);
                        active.fnDeleteRow(rowToRemove[0]);          
                        
                        removed.fnAddData([
                           '<a href="#" id="' + data.id + '" class="reinstate">Reinstate</a></td>', 
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
                         $('#removedParticipants tr:last').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#removedParticipants tr:last').children('td:nth-child(2)').attr('class', 'firstName').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(3)').attr('class', 'lastName').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(4)').attr('class', 'userName').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(5)').attr('class', 'email').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(6)').attr('class', 'instrument').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(7)').attr('class', 'playerNumber').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(8)').attr('class', 'desk').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(9)').attr('class', 'playingPart').attr('id', data.id);
                         active.fnFilterOnReturn();
                         removed.fnFilterOnReturn();
                        /*
                           '<td class="changers"><a href="#" id="' + data.id + '" class="reinstate">Reinstate</a></td>', 
                           '<td id="' + data.id + '" class="userName">' + data.userName + '</td>',
                           '<td id="' + data.id + '" class="instrument">' + data.instrument + '</td>',
                           '<td id="' + data.id + '" class="playerNumber">' + data.playerNumber + '</td>',
                           '<td id="' + data.id + '" class="desk">' + data.desk + '</td>',
                           '<td id="' + data.id + '" class="playingPart">' + data.playingPart + '</td>' 
                        */
                        active.fnDraw();
                        removed.fnDraw();
                    }
                    else
                    {
                         $('#msg').html('Failed changes!');
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
    
    $('a.reinstate').live('click', function(){
            var reinstateId = this.id;
            var participant = $('#' + this.id + '.userName').text();
            var message = 'Are you sure you want to reinstate participant ' + participant + ' ?';
            $('#confirmReinstate').html(message).data('id', reinstateId).dialog('open');    
            return false;
    });
    
    $('#confirmReinstate').dialog({
        autoOpen: false,
        title: 'Reinstate Confirmation',
        width: 'auto',
        resizable: false,
        modal: true,
        buttons: {
            Yes: function() {
                $(this).dialog("close");
                var reinstateId = $(this).data('id');
                var value = $('input.hidden').val(reinstateId);
             
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/projectMember/reinstateHandler',
                data: value, 

                success:function(data){
                    if(data.message == "success")
                    {
                         var active = $('#projectParticipants').dataTable();
                         var removed = $('#removedParticipants').dataTable();
                         var rowToReinstate = $('#removedParticipants').find('tr#' + data.id);                
                         removed.fnDeleteRow(rowToReinstate[0]);
                         
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
  
                         $('#projectParticipants tr:last').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#projectParticipants tr:last').children('td:nth-child(2)').attr('class', 'changers');
                         $('#projectParticipants tr:last').children('td:nth-child(3)').attr('class', 'firstName').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(4)').attr('class', 'lastName').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(5)').attr('class', 'userName').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(6)').attr('class', 'email').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(7)').attr('class', 'instrument').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(8)').attr('class', 'playerNumber').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(9)').attr('class', 'desk').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(10)').attr('class', 'playingPart').attr('id', data.id);
                         active.fnFilterOnReturn();
                         removed.fnFilterOnReturn();
                            /*
                           '<tr id="' + data.id + '">'+       
                           '<td class="changers"><a href="#" id="' + data.id + '" class="remove">Remove</a></td>', 
                           '<td class="changers"><a href="#" id="' + data.id + '" class="editpts">Edit</a></td>', 
                           '<td id="' + data.id + '" class="userName">' + data.userName + '</td>',
                           '<td id="' + data.id + '" class="instrument">' + data.instrument + '</td>',
                           '<td id="' + data.id + '" class="playerNumber">' + data.playerNumber + '</td>',
                           '<td id="' + data.id + '" class="desk">' + data.desk + '</td>',
                           '<td id="' + data.id + '" class="playingPart">' + data.playingPart + '</td>' + 
                           '</tr>'
                           */
                         active.fnDraw();
                         removed.fnDraw();
                        
                    }
                    else
                    {
                         $('#msg').html('Failed changes!');
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
 

