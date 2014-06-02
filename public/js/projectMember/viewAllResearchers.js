$(document).ready(function(){
    $('#projectParticipants').dataTable(
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
    ]
    );
    
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
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/reinstateHandler',
            }
    ).yadcf([
        {column_number : 1 },
        {column_number : 2 },
        ]
    );
    
    $('#pendingParticipants').dataTable(
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
    ]
    );
    
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
                           data.userName,
                           data.email
                         ]
                         );
                         $('#removedParticipants tr:last').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(0)').attr('class', 'changers');
                         $('#removedParticipants tr:last').children('td:nth-child(1)').attr('class', 'userName').attr('id', data.id);
                         $('#removedParticipants tr:last').children('td:nth-child(2)').attr('class', 'email').attr('id', data.id);
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
                           data.userName,
                           data.email
                         ]
                         );
  
                         $('#projectParticipants tr:last').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(0)').attr('class', 'changers');
                         $('#projectParticipants tr:last').children('td:nth-child(1)').attr('class', 'userName').attr('id', data.id);
                         $('#projectParticipants tr:last').children('td:nth-child(2)').attr('class', 'email').attr('id', data.id);
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
 

