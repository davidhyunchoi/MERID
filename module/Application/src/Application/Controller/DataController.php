<?php

namespace Application\Controller;
use Zend\View\Model\ViewModel;
use Application\Controller\BaseController;
use Application\Document\ProjectDocument as Project;
use Application\Document\SurveyDocument as Survey;
use Application\Document\RecordingDocument as Recording;
use Application\Document\PieceDocument as Piece;
use Application\Document\CommentDocument as Comment;
use Application\Document\ResearcherVideoCommentDocument as ResearcherVideoComment;
use Application\Document\ResearcherCommentCommentDocument as ResearcherCommentComment;
use Application\Document\ParticipantCommentDocument as ParticipantComment;
use Application\Document\VideoDocument as Video;
use Application\Document\UserDocument as User;
use Application\Document\ProjectMemberDocument as ProjectMember;
use Application\Document\ParticipantDocument as Participant;
use Application\Document\ResearcherDocument as Researcher;
use Zend\View\Model\JsonModel;
use Zend\File\Transfer\Adapter\Http;
class DataController extends BaseController
{
    public function exportAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        $projects = array();
        $surveysInProject = array();
        foreach($user->project_members as $member){
            if($member->status == "active")
            $projects[] = $member->project;
            $surveys = $surveyMapper->getSurveysByProjectId($member->project->id);
            foreach($surveys as $survey)
            {
                $surveysInProject[] = $survey;
            }
        }
        $this->layout()->setVariable('currentUser',$user);
        $this->layout()->setVariable('projects', $projects);
        $this->layout()->setVariable('surveysInProject', $surveysInProject);  
        
        return new ViewModel();
    } 
    public function exportOneHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
        /******************* Exporting Selected Project *********************/
        $rawdata = $this->getRequest()->getContent();
        $projects = array();
        $surveys = array();
        $projectId = "";
        $exportParticipants = "";
        $surveyIdForComments = "";
        foreach(explode('&', $rawdata) as $chunk){
            $param = explode('=', $chunk);
            $key = urldecode($param[0]);
            $value = urldecode($param[1]);
            if($key == 'project')
            {
                $projectId = $value;
            }
            elseif($key == 'surveyIdForComments')
            {
                $surveyIdForComments = $value;
            }
            elseif($key == 'participants')
            {
                $exportParticipants = $value;
            }
            else
            {
                array_push($surveys, $value);
            }
        }       
        
        // Get the Project by the Id that was sent through AJAX //
        $project = $projectMapper->getById($projectId);
        $projectData = array();
        $str = "";
        $projJsonForm = "";
        $props = $project->getProperties();
        $otherProjectMembers = array();
        $participantIds = "";
        $participantNames = "";
        $researcherIds = "";
        $researcherNames = "";
        $dateCreated = "";
        $timeCreated = "";
        $lastModified = "";
        $date = "";
        $surveysWithinProject = $surveyMapper->getSurveysByProjectId($projectId);
        $surveyIdsList = "";
        // For each of the designated project properties
        foreach($props as $property)
        { 
            if($property == 'participants')
            {    
               foreach($project->participants as $participant)
               {
                   $participantIds = $participantIds . $participant->id . ' | ';
                   $participantNames = $participantNames . $participant->firstName . ' ' . $participant->lastName . ' | ';
               }
               $projectData[$property] = '[' . rtrim($participantIds, ' | ') . ']';
               $projectData['participantNames'] = '[' . trim($participantNames, ' | ') . ']'; 
            }
            elseif($property == 'researchers')
            {    
               foreach($project->researchers as $researcher)
               {
                   // add the researcher members for this project
                   $otherProjectMembers[] = $projectMemberMapper->getById($researcher->id);
                   $researcherIds = $researcherIds . $researcher->id . ' | ';
                   $researcherNames = $researcherNames . $researcher->user->name . ' | ';
               }
               $projectData[$property] = '[' . rtrim($researcherIds, ' | ') . ']';
               $projectData['researcherNames'] = '[' . trim($researcherNames, ' | ') . ']'; 
            }
            elseif($property == 'createdOn')
            {
                foreach($project->createdOn as $key=>$val)
                {
                    if($key == 'date')
                    {
                        $date = preg_split('/[\s]+/', $val);
                        $dateCreated = $date[0];
                        $timeCreated = $date[1];
                    }
                }
                $projectData['dateCreated'] = $dateCreated;
                $projectData['timeCreated'] = $timeCreated;
            }
            elseif($property == 'modifiedOn')
            {
                foreach($project->modifiedOn as $key=>$val)
                {
                    if($key == 'date')
                    {
                        $lastModified = $val;
                    }
                }
                $projectData['lastModified'] = $lastModified;
            }
            elseif ($property == 'primaryResearcher') 
            {
                // add the primary researcher member for this project
                $otherProjectMembers[] = $projectMemberMapper->getById($project->primaryResearcher->id);
                $projectData[$property] = $project->primaryResearcher->id;
                $projectData['primaryResearcherName'] = $project->primaryResearcher->user->name;
            }
            else
            {
                $projectData[$property] = $project->$property;
            }
        }
        foreach($surveysWithinProject as $survey)
        {
            $surveyIdsList =  $surveyIdsList. $survey->id . ' | ';
            $projectData['surveyIds'] = '[' . rtrim($surveyIdsList, ' | ') . ']';
        }
        
        // Transform this project data to one giant JSON form to be PSVified      
        foreach($projectData as $key=>$value)
        { 
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $str = $str . $jsoncoded;
        }
        $projJsonForm = '[{' . rtrim($str, ',') . '}]';
        
        /********************************************************************/
        
        /*********** Exporting All Surveys Under Selected Project ***********/

        $surveyData = array();
        $sprops = "";
        $sstr = "";
        $users = "";
        $surveyList = array();
        $survJsonFormArray = array();
        $surveysInProject = array();
        $surveyParticipants = "";
        $dateCreatedForSurvey = "";
        $timeCreatedForSurvey = "";
        $lastModifiedForSurvey = "";
        $dateForSurvey = "";
        //Get the surveys based on each id received
        foreach($surveys as $surveyId)
        {
            $survey = $surveyMapper->getById($surveyId);
            $surveysInProject[] = $survey;
        }
        
        // For each survey that is in the project
        foreach($surveysInProject as $survey)
        {
            $sprops = $survey->getProperties();
            foreach($sprops as $property)
            {
                if($property == 'project')
                {    
                    $surveyData[$property] = $survey->project->id;
                    $surveyData['projectName'] = $survey->project->name;
                }
                elseif($property == 'createdOn')
                {
                    foreach($survey->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForSurvey = preg_split('/[\s]+/', $val);
                            $dateCreatedForSurvey = $dateForSurvey[0];
                            $timeCreatedForSurvey = $dateForSurvey[1];
                        }
                    }
                    $surveyData['dateCreated'] = $dateCreatedForSurvey;
                    $surveyData['timeCreated'] = $timeCreatedForSurvey;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($survey->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForSurvey = $val;
                        }
                    }
                    $surveyData['lastModified'] = $lastModifiedForSurvey;
                }
                elseif($property == 'recording')
                {
                    $surveyData[$property] = $survey->recording->id;
                    $surveyData['recordingName'] = $survey->recording->title;
                }
                elseif($property == 'primaryResearcher')
                {
                    $surveyData[$property] = $survey->project->primaryResearcher->user->name;
                }
                elseif($property == 'selectedRecording')
                {
                }
                elseif($property == 'users')
                {
                    foreach($survey->users as $user)
                    {
                        $surveyParticipant = $projectMemberMapper->getParticipantByUserId($user);
                        $users =  $users . $user . ' | ';
                        $surveyParticipants = $surveyParticipants . $surveyParticipant->firstName . ' ' . $surveyParticipant->lastName . ' | ';
                    }
                    $surveyData[$property] = '[' . rtrim($users, ' | ') . ']';
                    $surveyData['participants'] = '[' . trim($surveyParticipants, ' | ') . ']';
                }
                else
                {
                    $surveyData[$property] = $survey->$property;
                }
            }
            $surveyData['attachedComments'] = count($commentMapper->getAllCommentsBySurvey($survey));
            
            // Store each property data for each survey in the surveyList
            array_push($surveyList, $surveyData);
            $users = str_replace($users, '', $users);
            $surveyParticipants = str_replace($surveyParticipants, '', $surveyParticipants);
        }
        
        //For each set of survey properties, turn it into JSON forms
        foreach($surveyList as $eachSurvey)
        {
            foreach($eachSurvey as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $sstr = $sstr . $jsoncoded;
            }
            // Stuff it into the Json Form Array
            array_push($survJsonFormArray, $sstr);
            $sstr = str_replace($sstr, '', $sstr);
        }        
        /******************************************************************/

        /*********** Exporting All Comments Under Survey ******************/
        $cstr = "";
        $cprops = "";
        $viewers = "";
        $viewerNames = "";
        $viewerName = "";
        $commentsData = array();
        $commentList = array();
        $commJsonFormArray = array();
        $surveyWithComments = "";
        $surveyWithCommentsTitle = "";
        $videos = "";
        $dateCreatedForComment = "";
        $timeCreatedForComment = "";
        $lastModifiedForComment = "";
        $dateForComment = "";
        if($surveyIdForComments != null)
        {
        $surveyWithComments = $surveyMapper->getById($surveyIdForComments);
        $surveyWithCommentsTitle = $surveyWithComments->title;
        $comments = $commentMapper->getAllCommentsBySurvey($surveyWithComments);
        
        foreach($comments as $comment)
        {
            $cprops = $comment->getProperties();
            foreach($cprops as $property)
            {
                if($property == 'survey')
                {    
                    $commentsData[$property] = $comment->survey->id;
                    $commentsData['surveyTitle'] = $comment->survey->title;
                    $commentsData['recordingTitle'] = $comment->survey->recording->title;
                }
                elseif($property == 'video')
                {
                    /*
                    foreach($surveyWithComments->recording->videos as $video) 
                    {
                        $videos = $videos . $video->id . ' | ';
                    }
                    $commentsData[$property] = '[' . rtrim($videos, ' | ') . ']';
                     
                    */
                }
                elseif($property == 'participant')
                {
                    if($comment->participant != null)
                    {
                        $commentsData[$property] = $comment->participant->id;
                        $commentsData['participantAuthor'] = $comment->participant->firstName . ' ' . $comment->participant->lastName;
                    }
                    else
                    {
                        $commentsData[$property] = null;
                        $commentsData['participantAuthor'] = null;
                    }
                }
                elseif($property == 'researcher')
                {
                    if($comment->researcher != null)
                    {
                        $commentsData[$property] = $comment->researcher->id;
                        $commentsData['researcherAuthor'] = $comment->researcher->user->name;
                    }
                    else
                    {
                        $commentsData[$property] = null;
                        $commentsData['researcherAuthor'] = null;
                    }
                }
                elseif($property == 'viewers')
                {
                       foreach($comment->viewers as $viewer) 
                       {
                           $projectMember = $projectMemberMapper->getById($viewer);
                           $viewers = $viewers . $viewer . ' | ';
                          
                           if($projectMember->type == 'participant')
                           {
                               $viewerName = $projectMember->firstName . $projectMember->lastName;
                           }
                           else
                           {
                               $viewerName = $projectMember->user->name;
                           }
                           $viewerNames = $viewerNames . $viewerName . ' | ';
                       }
                       $commentsData[$property] = '[' . rtrim($viewers, ' | ') . ']';
                       $commentsData['viewerNames'] = '[' . trim($viewerNames, ' | ') . ']';
                }
                elseif($property == 'comment')
                {
                    if($comment->comment != null)
                    {
                        $commentsData[$property] = $comment->comment->id;
                    }
                    else
                    {
                        $commentsData[$property] = null;
                    }
                }
                elseif($property == 'createdOn')
                {
                    foreach($comment->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForComment = preg_split('/[\s]+/', $val);
                            $dateCreatedForComment = $dateForComment[0];
                            $timeCreatedForComment = $dateForComment[1];
                        }
                    }
                    $commentsData['dateCreated'] = $dateCreatedForComment;
                    $commentsData['timeCreated'] = $timeCreatedForComment;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($comment->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForComment = $val;
                        }
                    }
                    $commentsData['lastModified'] = $lastModifiedForComment;
                }
                else
                {
                    $commentsData[$property] = $comment->$property;
                }
                $commentsData['projectId'] = $comment->survey->project->id;
                $commentsData['projectName'] = $comment->survey->project->name;
                $commentsData['primaryResearcher'] = $comment->survey->project->primaryResearcher->user->name;
            }
            array_push($commentList, $commentsData);
            $viewers = str_replace($viewers, '', $viewers);
            $videos = str_replace($videos, '', $videos);
            $viewerNames = str_replace($viewerNames, '', $viewerNames);
        }
        foreach($commentList as $eachComment)
        {
            foreach($eachComment as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $cstr = $cstr . $jsoncoded;
            }
            array_push($commJsonFormArray, $cstr);
            $cstr = str_replace($cstr, '', $cstr);
        }   
        } 
        /******************************************************************/

        /*********** Exporting All Project Members Under Project ***********/
        /********************Participants*********************/
        $otherProjectMembersData = array();
        $otherProjectMemberList = array();
        $otherPartJsonFormArray = array();
        $pprops = "";
        $otherpprops = "";
        $pstr = "";
        $opstr = "";
        $participantsData = array();
        $participantList = array();
        $partJsonFormArray = array();
        $dateCreatedForParticipant = "";
        $timeCreatedForParticipant = "";
        $lastModifiedForParticipant = "";
        $dateForParticipant = "";
        // Get all the participants under the project //
        $participantsInProject = $projectMemberMapper->getAllParticipants($projectId);
        foreach($participantsInProject as $participant)
        {
            $pprops = $participant->getProperties();
            foreach($pprops as $property)
            {
                if($property == 'project')
                {    
                    $participantsData[$property] = $participant->project->id;
                    $participantsData['projectName'] = $participant->project->name;
                }
                elseif($property == 'user')
                {
                    $participantsData[$property] = $participant->user->id;
                    $participantsData['email'] = $participant->user->email;
                }
                elseif($property == 'type')
                {
                    $participantsData[$property] = $participant->getType();
                }
                elseif($property == 'createdOn')
                {
                    foreach($participant->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForParticipant = preg_split('/[\s]+/', $val);
                            $dateCreatedForParticipant = $dateForParticipant[0];
                            $timeCreatedForParticipant = $dateForParticipant[1];
                        }
                    }
                    $participantsData['dateCreated'] = $dateCreatedForParticipant;
                    $participantsData['timeCreated'] = $timeCreatedForParticipant;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($participant->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForParticipant = $val;
                        }
                    }
                    $participantsData['lastModified'] = $lastModifiedForParticipant;
                }
                else
                {
                    $participantsData[$property] = $participant->$property;
                }
            }
            array_push($participantList, $participantsData);
        }
        foreach($participantList as $eachParticipant)
        {
            foreach($eachParticipant as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $pstr = $pstr . $jsoncoded;
            }
            array_push($partJsonFormArray, $pstr);
            $pstr = str_replace($pstr, '', $pstr);
        }   
      
        /********************Primary Researchers and Researchers*********************/
        // get all the other project members
        $dateCreatedForOtherMember = "";
        $timeCreatedForOtherMember = "";
        $lastModifiedForOtherMember = "";
        $dateForOtherMember = "";
        
        foreach($otherProjectMembers as $otherProjectMember)
        {
            $name = $otherProjectMember->user->name;
            $names = preg_split('/[\s]+/', $name);
            $otherpprops = $otherProjectMember->getProperties();
            foreach($otherpprops as $property)
            {
                if($property == 'project')
                {    
                     $otherProjectMembersData[$property] = $otherProjectMember->project->id;
                     $otherProjectMembersData['projectName'] = $otherProjectMember->project->name;
                }
                elseif($property == 'user')
                {
                     $otherProjectMembersData[$property] = $otherProjectMember->user->id;
                     $otherProjectMembersData['email'] = $otherProjectMember->user->email;
                }
                elseif($property == 'type')
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->getType();
                }
                elseif($property == 'createdOn')
                {
                    foreach($otherProjectMember->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForOtherMember = preg_split('/[\s]+/', $val);
                            $dateCreatedForOtherMember = $dateForOtherMember[0];
                            $timeCreatedForOtherMember = $dateForOtherMember[1];
                        }
                    }
                    $otherProjectMembersData['dateCreated'] = $dateCreatedForOtherMember;
                    $otherProjectMembersData['timeCreated'] = $timeCreatedForOtherMember;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($otherProjectMember->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForOtherMember = $val;
                        }
                    }
                    $otherProjectMembersData['lastModified'] = $lastModifiedForOtherMember;
                }
                elseif($property == 'firstName')
                {
                    $otherProjectMembersData[$property] = $names[0];
                }
                elseif($property == 'lastName')
                {
                    $otherProjectMembersData[$property] = $names[1];
                }
                elseif($property == 'userName')
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->user->username;
                }
                elseif($property == 'instrument')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'playerNumber')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'playingPart')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'desk')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'demographicAnswers')
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->user->demographicAnswers;
                }
                else
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->$property;
                }
            }
            array_push($otherProjectMemberList, $otherProjectMembersData);
        }
        foreach($otherProjectMemberList as $eachOtherProjectMember)
        {
            foreach($eachOtherProjectMember as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $opstr = $opstr . $jsoncoded;
            }
            array_push($otherPartJsonFormArray, $opstr);
            $opstr = str_replace($opstr, '', $opstr);
        }   
        /*******************************************************************/

         /*********** Exporting All Surveys Under Selected Project By Default ***********/
    
        $dsurveyData = array();
        $dsprops = "";
        $dsstr = "";
        $dusers = "";
        $dsurveyList = array();
        $dsurvJsonFormArray = array();
        $dsurveysInProject = $surveyMapper->getSurveysByProjectId($projectId);  
        $dsurveyParticipants = "";
        $ddateCreatedForSurvey = "";
        $dtimeCreatedForSurvey = "";
        $dlastModifiedForSurvey = "";
        $ddateForSurvey = "";
        
        // For each survey that is in the project
        foreach($dsurveysInProject as $dsurvey)
        {
            $dsprops = $dsurvey->getProperties();
            foreach($dsprops as $property)
            {
                if($property == 'project')
                {    
                    $dsurveyData[$property] = $dsurvey->project->id;
                    $dsurveyData['projectName'] = $dsurvey->project->name;
                }
                elseif($property == 'createdOn')
                {
                    foreach($dsurvey->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $ddateForSurvey = preg_split('/[\s]+/', $val);
                            $ddateCreatedForSurvey = $ddateForSurvey[0];
                            $dtimeCreatedForSurvey = $ddateForSurvey[1];
                        }
                    }
                    $dsurveyData['dateCreated'] = $ddateCreatedForSurvey;
                    $dsurveyData['timeCreated'] = $dtimeCreatedForSurvey;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($dsurvey->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dlastModifiedForSurvey = $val;
                        }
                    }
                    $dsurveyData['lastModified'] = $dlastModifiedForSurvey;
                }
                elseif($property == 'recording')
                {
                    $dsurveyData[$property] = $dsurvey->recording->id;
                    $dsurveyData['recordingName'] = $dsurvey->recording->title;
                }
                elseif($property == 'primaryResearcher')
                {
                    $dsurveyData[$property] = $dsurvey->project->primaryResearcher->user->name;
                }
                elseif($property == 'selectedRecording')
                {
                }
                elseif($property == 'users')
                {
                    foreach($dsurvey->users as $duser)
                    {
                        $dsurveyParticipant = $projectMemberMapper->getParticipantByUserId($duser);
                        $dusers =  $dusers . $duser . ' | ';
                        $dsurveyParticipants = $dsurveyParticipants . $dsurveyParticipant->firstName . ' ' . $dsurveyParticipant->lastName . ' | ';
                    }
                    $dsurveyData[$property] = '[' . rtrim($dusers, ' | ') . ']';
                    $dsurveyData['participants'] = '[' . trim($dsurveyParticipants, ' | ') . ']';
                }
                else
                {
                    $dsurveyData[$property] = $dsurvey->$property;
                }
            }
            $dsurveyData['attachedComments'] = count($commentMapper->getAllCommentsBySurvey($dsurvey));
            // Store each property data for each survey in the surveyList
            array_push($dsurveyList, $dsurveyData);
            $dusers = str_replace($dusers, '', $dusers);
            $dsurveyParticipants = str_replace($dsurveyParticipants, '', $dsurveyParticipants);
        }
        
        //For each set of survey properties, turn it into JSON forms
        foreach($dsurveyList as $eachdSurvey)
        {
            foreach($eachdSurvey as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $dsstr = $dsstr . $jsoncoded;
            }
            // Stuff it into the Json Form Array
            array_push($dsurvJsonFormArray, $dsstr);
            $dsstr = str_replace($dsstr, '', $dsstr);
        }        
        /******************************************************************/
        /*********** Exporting All Comments Under Surveys by Default*****************/
           
        $dcommentsData = array();
        $dcommentsList = array();
        $dcomprops = "";
        $dcomstr = "";
        $dcomJsonFormArray = array();
        $alldComments = array();
        $dviewers = "";
        $dvideos = "";
        $dviewerNames = "";
        $dviewerName = "";
        $ddateCreatedForComment = "";
        $dtimeCreatedForComment = "";
        $dlastModifiedForComment = "";
        $ddateForComment = "";
          
        $dcommentsInSurvey = array();
        foreach($dsurveysInProject as $survey)
        {
             $dcommentsInSurvey = $commentMapper->getAllCommentsBySurvey($survey);
             foreach($dcommentsInSurvey as $comment)
             {
                 $alldComments[] = $comment;
             }
        }
        foreach($alldComments as $dcomment)
        {
             $dcomprops = $dcomment->getProperties();
             foreach($dcomprops as $property)
             {
                if($property == 'survey')
                {    
                    $dcommentsData[$property] = $dcomment->survey->id;
                    $dcommentsData['surveyTitle'] = $dcomment->survey->title;
                    $dcommentsData['recordingTitle'] = $dcomment->survey->recording->title;
                }
                elseif($property == 'video')
                {
                    /*
                    foreach($surveyWithComments->recording->videos as $video) 
                    {
                        $videos = $videos . $video->id . ' | ';
                    }
                    $commentsData[$property] = '[' . rtrim($videos, ' | ') . ']';
                     
                    */
                }
                elseif($property == 'participant')
                {
                    if($dcomment->participant != null)
                    {
                        $dcommentsData[$property] = $dcomment->participant->id;
                        $dcommentsData['participantAuthor'] = $dcomment->participant->firstName . ' ' . $dcomment->participant->lastName;
                    }
                    else
                    {
                        $dcommentsData[$property] = null;
                        $dcommentsData['participantAuthor'] = null;
                    }
                }
                elseif($property == 'researcher')
                {
                    if($dcomment->researcher != null)
                    {
                        $dcommentsData[$property] = $dcomment->researcher->id;
                        $dcommentsData['researcherAuthor'] = $dcomment->researcher->user->name;
                    }
                    else
                    {
                        $dcommentsData[$property] = null;
                        $dcommentsData['researcherAuthor'] = null;
                    }
                }
                elseif($property == 'viewers')
                {
                       foreach($dcomment->viewers as $dviewer) 
                       {
                           $projectMember = $projectMemberMapper->getById($dviewer);
                           $dviewers = $dviewers . $dviewer . ' | ';
                          
                           if($projectMember->type == 'participant')
                           {
                               $dviewerName = $projectMember->firstName . $projectMember->lastName;
                           }
                           else
                           {
                               $dviewerName = $projectMember->user->name;
                           }
                           $dviewerNames = $dviewerNames . $dviewerName . ' | ';
                       }
                       $dcommentsData[$property] = '[' . rtrim($dviewers, ' | ') . ']';
                       $dcommentsData['viewerNames'] = '[' . trim($dviewerNames, ' | ') . ']';
                }
                elseif($property == 'comment')
                {
                    if($dcomment->comment != null)
                    {
                        $dcommentsData[$property] = $dcomment->comment->id;
                    }
                    else
                    {
                        $dcommentsData[$property] = null;
                    }
                }
                elseif($property == 'createdOn')
                {
                    foreach($dcomment->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $ddateForComment = preg_split('/[\s]+/', $val);
                            $ddateCreatedForComment = $ddateForComment[0];
                            $dtimeCreatedForComment = $ddateForComment[1];
                        }
                    }
                    $dcommentsData['dateCreated'] = $ddateCreatedForComment;
                    $dcommentsData['timeCreated'] = $dtimeCreatedForComment;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($dcomment->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dlastModifiedForComment = $val;
                        }
                    }
                    $dcommentsData['lastModified'] = $dlastModifiedForComment;
                }
                else
                {
                    $dcommentsData[$property] = $dcomment->$property;
                }
                $dcommentsData['projectId'] = $dcomment->survey->project->id;
                $dcommentsData['projectName'] = $dcomment->survey->project->name;
                $dcommentsData['primaryResearcher'] = $dcomment->survey->project->primaryResearcher->user->name;
             }
             array_push($dcommentsList, $dcommentsData);
             $dviewers = str_replace($dviewers, '', $dviewers);
             $dvideos = str_replace($dvideos, '', $dvideos);
             $dviewerNames = str_replace($viewerNames, '', $viewerNames);
        }
        foreach($dcommentsList as $eachComment)
        {
            foreach($eachComment as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $dcomstr = $dcomstr . $jsoncoded;
            }
            array_push($dcomJsonFormArray, $dcomstr);
            $dcomstr = str_replace($dcomstr, '', $dcomstr);
        }
        /******************************************************************/
        
        return new JsonModel(array(
              "message" => "success",
              "project" => $project->name,
              "proj" => $projJsonForm,
              "exportParticipants" => $exportParticipants,
              "bulkSurvey" => $survJsonFormArray,
              "bulkParticipant" => $partJsonFormArray, 
              "otherMembers" => $otherPartJsonFormArray,
              "bulkComment" => $commJsonFormArray,
              "surveyWithCommentsTitle" => $surveyWithCommentsTitle,
              "dbulkSurvey" => $dsurvJsonFormArray,
              "dbulkComment" => $dcomJsonFormArray
        ));
    }

    /******************************************************************/
    public function exportAllHandlerAction()
    {
        $user = $this->getAuthenticatedUser();
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        $recordingMapper = $this->getMapperManager()->getMapper('Application\Mapper\RecordingMapper');
        $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
        $pieceRepo = $this->getDocumentManager()->getRepository('Application\Document\PieceDocument');
        $videoRepo = $this->getDocumentManager()->getRepository('Application\Document\VideoDocument');
        
        /********************* Exporting All Projects **********************/
        $rawdata = $this->getRequest()->getContent();
        $projectIds = array();
        $props = "";
        $otherProjectMembers = array();
        $projects = array();
        $projectData = array();
        $projectList = array();
        $str = "";
        $participantIds = "";
        $participantNames = "";
        $researcherIds = "";
        $researcherNames = "";
        $dateCreated = "";
        $timeCreated = "";
        $lastModified = "";
        $date = "";
        $projJsonFormArray = array();
        $surveysWithinProject = array();
        $surveyIdsList = "";
        
        foreach(explode('&', $rawdata) as $chunk){
            $param = explode('=', $chunk);
            $key = urldecode($param[0]);
            $value = urldecode($param[1]);
            array_push($projectIds, $value);
        }        
        // Gets the projects based on the ids
        foreach($projectIds as $projectId)
        {
            $project = $projectMapper->getById($projectId);
            $projects[] = $project;
        }
        
        // For each project
        foreach($projects as $project)
        {
            $props = $project->getProperties();
            foreach($props as $property)
            {
                   if($property == 'participants')
                   {    
                        foreach($project->participants as $participant)
                        {
                            $participantIds = $participantIds . $participant->id . ' | ';
                            $participantNames = $participantNames . $participant->firstName . ' ' . $participant->lastName . ' | ';
                        }
                        $projectData[$property] = '[' . rtrim($participantIds, ' | ') . ']';
                        $projectData['participantNames'] = '[' . trim($participantNames, ' | ') . ']'; 
                    }
                    elseif($property == 'researchers')
                    {    
                        foreach($project->researchers as $researcher)
                        {
                            // add the researcher members for this project
                            $otherProjectMembers[] = $projectMemberMapper->getById($researcher->id);
                            $researcherIds = $researcherIds . $researcher->id . ' | ';
                            $researcherNames = $researcherNames . $researcher->user->name . ' | ';
                        }
                        $projectData[$property] = '[' . rtrim($researcherIds, ' | ') . ']';
                        $projectData['researcherNames'] = '[' . trim($researcherNames, ' | ') . ']'; 
                    }
                    elseif($property == 'createdOn')
                    {
                        foreach($project->createdOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $date = preg_split('/[\s]+/', $val);
                                $dateCreated = $date[0];
                                $timeCreated = $date[1];
                            }
                        }
                        $projectData['dateCreated'] = $dateCreated;
                        $projectData['timeCreated'] = $timeCreated;
                    }
                    elseif($property == 'modifiedOn')
                    {
                        foreach($project->modifiedOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $lastModified = $val;
                            }
                        }
                        $projectData['lastModified'] = $lastModified;
                    }   
                    elseif ($property == 'primaryResearcher') 
                    {
                        // add the primary researcher member for this project
                        $otherProjectMembers[] = $projectMemberMapper->getById($project->primaryResearcher->id);
                        $projectData[$property] = $project->primaryResearcher->id;
                        $projectData['primaryResearcherName'] = $project->primaryResearcher->user->name;
                    }
                    else
                    {
                        $projectData[$property] = $project->$property;
                    }
            }
            $surveysWithinProject = $surveyMapper->getSurveysByProjectId($project->id);
            foreach($surveysWithinProject as $survey)
            {
                $surveyIdsList =  $surveyIdsList. $survey->id . ' | ';
                $projectData['surveyIds'] = '[' . rtrim($surveyIdsList, ' | ') . ']';
            }
            array_push($projectList, $projectData);
            $participantIds = str_replace($participantIds, '', $participantIds);
            $participantNames = str_replace($participantNames, '', $participantNames);
            $researcherIds = str_replace($researcherIds, '', $researcherIds);
            $researcherNames = str_replace($researcherNames, '', $researcherNames);
            $surveyIdsList = str_replace($surveyIdsList, '', $surveyIdsList);
        }
        // For each project data, turn it into JSON form
        foreach($projectList as $eachProject)
        {
            foreach($eachProject as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $str = $str . $jsoncoded;
            }
            array_push($projJsonFormArray, $str);
            $str = str_replace($str, '', $str);
        }
        /*****************************************************************/
        
        /******************** Exporting All Surveys **********************/
        $surveyData = array();
        $sprops = "";
        $sstr = "";
        $users = "";
        $surveyList = array();
        $survJsonFormArray = array();
        $surveysInProject = array();
        $surveyParticipants = "";
        $dateCreatedForSurvey = "";
        $timeCreatedForSurvey = "";
        $lastModifiedForSurvey = "";
        $dateForSurvey = "";
        // For each project, get all the surveys and store it into the surveysInProject array
        foreach($projectIds as $projectId)
        {
            $surveys = $surveyMapper->getSurveysByProjectId($projectId);
            foreach($surveys as $survey)
            {
                $surveysInProject[] = $survey;
            }
        }
        foreach($surveysInProject as $survey)
        {
            $sprops = $survey->getProperties();
            foreach($sprops as $property)
            {
                          if($property == 'project')
                {    
                    $surveyData[$property] = $survey->project->id;
                    $surveyData['projectName'] = $survey->project->name;
                }
                elseif($property == 'createdOn')
                {
                    foreach($survey->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForSurvey = preg_split('/[\s]+/', $val);
                            $dateCreatedForSurvey = $dateForSurvey[0];
                            $timeCreatedForSurvey = $dateForSurvey[1];
                        }
                    }
                    $surveyData['dateCreated'] = $dateCreatedForSurvey;
                    $surveyData['timeCreated'] = $timeCreatedForSurvey;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($survey->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForSurvey = $val;
                        }
                    }
                    $surveyData['lastModified'] = $lastModifiedForSurvey;
                }
                elseif($property == 'recording')
                {
                    $surveyData[$property] = $survey->recording->id;
                    $surveyData['recordingName'] = $survey->recording->title;
                }
                elseif($property == 'primaryResearcher')
                {
                    $surveyData[$property] = $survey->project->primaryResearcher->user->name;
                }
                elseif($property == 'selectedRecording')
                {
                }
                elseif($property == 'users')
                {
                    foreach($survey->users as $user)
                    {
                        $surveyParticipant = $projectMemberMapper->getParticipantByUserId($user);
                        $users =  $users . $user . ' | ';
                        $surveyParticipants = $surveyParticipants . $surveyParticipant->firstName . ' ' . $surveyParticipant->lastName . ' | ';
                    }
                    $surveyData[$property] = '[' . rtrim($users, ' | ') . ']';
                    $surveyData['participants'] = '[' . trim($surveyParticipants, ' | ') . ']';
                }
                else
                {
                    $surveyData[$property] = $survey->$property;
                }
            }
            $surveyData['attachedComments'] = count($commentMapper->getAllCommentsBySurvey($survey));
            
            array_push($surveyList, $surveyData);
            $users = str_replace($users, '', $users);
            $surveyParticipants = str_replace($surveyParticipants, '', $surveyParticipants);
        }
        foreach($surveyList as $eachSurvey)
        {
            foreach($eachSurvey as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $sstr = $sstr . $jsoncoded;
            }
            array_push($survJsonFormArray, $sstr);
            $sstr = str_replace($sstr, '', $sstr);
        }
        
        
        /******************************************************************/
        
        /***********************Export All Comments****************************/
       
        $commentsData = array();
        $commentsList = array();
        $comprops = "";
        $comstr = "";
        $comJsonFormArray = array();
        $allComments = array();
        $viewers = "";
        $videos = "";
        $commentsInSurvey = array();
        $viewerNames = "";
        $viewerName = "";
        $dateCreatedForComment = "";
        $timeCreatedForComment = "";
        $lastModifiedForComment = "";
        $dateForComment = "";
        
        foreach($surveysInProject as $survey)
        {
             $commentsInSurvey = $commentMapper->getAllCommentsBySurvey($survey);
             foreach($commentsInSurvey as $comment)
             {
                 $allComments[] = $comment;
             }
        }
        foreach($allComments as $comment)
        {
             $comprops = $comment->getProperties();
             foreach($comprops as $property)
             {
                if($property == 'survey')
                {    
                    $commentsData[$property] = $comment->survey->id;
                    $commentsData['surveyTitle'] = $comment->survey->title;
                    $commentsData['recordingTitle'] = $comment->survey->recording->title;
                }
                elseif($property == 'video')
                {
                    /*
                    foreach($surveyWithComments->recording->videos as $video) 
                    {
                        $videos = $videos . $video->id . ' | ';
                    }
                    $commentsData[$property] = '[' . rtrim($videos, ' | ') . ']';
                     
                    */
                }
                elseif($property == 'participant')
                {
                    if($comment->participant != null)
                    {
                        $commentsData[$property] = $comment->participant->id;
                        $commentsData['participantAuthor'] = $comment->participant->firstName . ' ' . $comment->participant->lastName;
                    }
                    else
                    {
                        $commentsData[$property] = null;
                        $commentsData['participantAuthor'] = null;
                    }
                }
                elseif($property == 'researcher')
                {
                    if($comment->researcher != null)
                    {
                        $commentsData[$property] = $comment->researcher->id;
                        $commentsData['researcherAuthor'] = $comment->researcher->user->name;
                    }
                    else
                    {
                        $commentsData[$property] = null;
                        $commentsData['researcherAuthor'] = null;
                    }
                }
                elseif($property == 'viewers')
                {
                       foreach($comment->viewers as $viewer) 
                       {
                           $projectMember = $projectMemberMapper->getById($viewer);
                           $viewers = $viewers . $viewer . ' | ';
                          
                           if($projectMember->type == 'participant')
                           {
                               $viewerName = $projectMember->firstName . $projectMember->lastName;
                           }
                           else
                           {
                               $viewerName = $projectMember->user->name;
                           }
                           $viewerNames = $viewerNames . $viewerName . ' | ';
                       }
                       $commentsData[$property] = '[' . rtrim($viewers, ' | ') . ']';
                       $commentsData['viewerNames'] = '[' . trim($viewerNames, ' | ') . ']';
                }
                elseif($property == 'comment')
                {
                    if($comment->comment != null)
                    {
                        $commentsData[$property] = $comment->comment->id;
                    }
                    else
                    {
                        $commentsData[$property] = null;
                    }
                }
                elseif($property == 'createdOn')
                {
                    foreach($comment->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForComment = preg_split('/[\s]+/', $val);
                            $dateCreatedForComment = $dateForComment[0];
                            $timeCreatedForComment = $dateForComment[1];
                        }
                    }
                    $commentsData['dateCreated'] = $dateCreatedForComment;
                    $commentsData['timeCreated'] = $timeCreatedForComment;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($comment->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForComment = $val;
                        }
                    }
                    $commentsData['lastModified'] = $lastModifiedForComment;
                }
                else
                {
                    $commentsData[$property] = $comment->$property;
                }
                $commentsData['projectId'] = $comment->survey->project->id;
                $commentsData['projectName'] = $comment->survey->project->name;
                $commentsData['primaryResearcher'] = $comment->survey->project->primaryResearcher->user->name;
             }
             array_push($commentsList, $commentsData);
             $comstr = str_replace($comstr, '', $comstr);
             $viewers = str_replace($viewers, '', $viewers);
             $videos = str_replace($videos, '', $videos);
             $viewerNames = str_replace($viewerNames, '', $viewerNames);
        }
        foreach($commentsList as $eachComment)
        {
            foreach($eachComment as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $comstr = $comstr . $jsoncoded;
            }
            array_push($comJsonFormArray, $comstr);
            $comstr = str_replace($comstr, '', $comstr);
        }
        
        /**********************************************************************/
        
        /******************** Exporting All Project Members **********************/
        
        /********************Participants*********************/

        $otherProjectMembersData = array();
        $otherProjectMemberList = array();
        $otherPartJsonFormArray = array();
        $projMemberUsers = array();
        $pprops = "";
        $otherpprops = "";
        $pstr = "";
        $opstr = "";
        $participantsData = array();
        $participantList = array();
        $partJsonFormArray = array();
        $participantsInProject = array();
        $participListByProject = array();
        $jsonParticipListByProject = array();
        $dateCreatedForParticipant = "";
        $timeCreatedForParticipant = "";
        $lastModifiedForParticipant = "";
        $dateForParticipant = "";
        
        // For formatting purposes, for each project, get the participants
        foreach($projects as $project)
        {
            $participantsInProject = $projectMemberMapper->getAllParticipants($project->id);
            
            // for each participants in project
            foreach($participantsInProject as $participant)
            {
                $projMemberUsers[] = $participant;
                $pprops = $participant->getProperties();
                foreach($pprops as $property)
                {
                     if($property == 'project')
                    {    
                        $participantsData[$property] = $participant->project->id;
                        $participantsData['projectName'] = $participant->project->name;
                    }
                    elseif($property == 'user')
                    {
                        $participantsData[$property] = $participant->user->id;
                        $participantsData['email'] = $participant->user->email;
                    }
                    elseif($property == 'type')
                    {
                        $participantsData[$property] = $participant->getType();
                    }
                    elseif($property == 'createdOn')
                    {
                        foreach($participant->createdOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $dateForParticipant = preg_split('/[\s]+/', $val);
                                $dateCreatedForParticipant = $dateForParticipant[0];
                                $timeCreatedForParticipant = $dateForParticipant[1];
                            }
                        }
                        $participantsData['dateCreated'] = $dateCreatedForParticipant;
                        $participantsData['timeCreated'] = $timeCreatedForParticipant;
                    }
                    elseif($property == 'modifiedOn')
                    {
                        foreach($participant->modifiedOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $lastModifiedForParticipant = $val;
                            }
                        }
                        $participantsData['lastModified'] = $lastModifiedForParticipant;
                    }
                    else
                    {
                        $participantsData[$property] = $participant->$property;
                    }
                }
           // Stores participants as one grouping   Participants Properties //   
                            //|//                       //|//
                            //|//                       //|//
                array_push($participantList, $participantsData);
                unset($participantsData); // Clear for next project properties
                $participantsData = array(); 
            }
           // Stores each grouping by Project 
            array_push($participListByProject, $participantList); // Clear for next participants list in project
            unset($participantList);
            $participantList = array(); 
        }
        foreach($participListByProject as $participList)
        {
            foreach($participList as $eachParticipant)
            {
                foreach($eachParticipant as $key=>$value)
                {
                    $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                    $pstr = $pstr . $jsoncoded;
                }
                array_push($partJsonFormArray, $pstr);
                $pstr = str_replace($pstr, '', $pstr);
            }
            array_push($jsonParticipListByProject, $partJsonFormArray);
            unset($partJsonFormArray);
            $partJsonFormArray = array();  
        }
        
        /********************Primary Researchers and Researchers*********************/
        $dateCreatedForOtherMember = "";
        $timeCreatedForOtherMember = "";
        $lastModifiedForOtherMember = "";
        $dateForOtherMember = "";
       
        foreach($otherProjectMembers as $otherProjectMember)
        {
            $name = $otherProjectMember->user->name;
            $names = preg_split('/[\s]+/', $name);
            $projMemberUsers[] = $otherProjectMember;
            $otherpprops = $otherProjectMember->getProperties();
            foreach($otherpprops as $property)
            {
                if($property == 'project')
                {    
                     $otherProjectMembersData[$property] = $otherProjectMember->project->id;
                     $otherProjectMembersData['projectName'] = $otherProjectMember->project->name;
                }
                elseif($property == 'user')
                {
                     $otherProjectMembersData[$property] = $otherProjectMember->user->id;
                     $otherProjectMembersData['email'] = $otherProjectMember->user->email;
                }
                elseif($property == 'type')
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->getType();
                }
                elseif($property == 'createdOn')
                {
                    foreach($otherProjectMember->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForOtherMember = preg_split('/[\s]+/', $val);
                            $dateCreatedForOtherMember = $dateForOtherMember[0];
                            $timeCreatedForOtherMember = $dateForOtherMember[1];
                        }
                    }
                    $otherProjectMembersData['dateCreated'] = $dateCreatedForOtherMember;
                    $otherProjectMembersData['timeCreated'] = $timeCreatedForOtherMember;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($otherProjectMember->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForOtherMember = $val;
                        }
                    }
                    $otherProjectMembersData['lastModified'] = $lastModifiedForOtherMember;
                }
                elseif($property == 'firstName')
                {
                    $otherProjectMembersData[$property] = $names[0];
                }
                elseif($property == 'lastName')
                {
                    $otherProjectMembersData[$property] = $names[1];
                }
                elseif($property == 'userName')
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->user->username;
                }
                elseif($property == 'instrument')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'playerNumber')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'playingPart')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'desk')
                {
                    $otherProjectMembersData[$property] = null;
                }
                elseif($property == 'demographicAnswers')
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->user->demographicAnswers;
                }
                else
                {
                    $otherProjectMembersData[$property] = $otherProjectMember->$property;
                }
            }
            array_push($otherProjectMemberList, $otherProjectMembersData);
        }
        foreach($otherProjectMemberList as $eachOtherProjectMember)
        {
            foreach($eachOtherProjectMember as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $opstr = $opstr . $jsoncoded;
            }
            array_push($otherPartJsonFormArray, $opstr);
            $opstr = str_replace($opstr, '', $opstr);
        }   
        /*************************************************************************/
        
        /******************** Exporting All Users **********************/
        $allUsers = array();
        $usersData = array();
        $usersList = array();
        $userJsonFormArray = array();
        $ustr = "";
        $uprops = "";
        $members = "";
        $dateCreatedForUser = "";
        $timeCreatedForUser = "";
        $lastModifiedForUser = "";
        $dateForUser = "";
        
        foreach($projMemberUsers as $projMember)
        {
             $allUsers[] = $userMapper->getById($projMember->user->id);
        }
        foreach($allUsers as $user)
        {
            $uprops = $user->getProperties();
            foreach($uprops as $property)
            {
                if($property == 'project_members')
                {
                    foreach($user->project_members as $member)
                    {
                        $members =  $members . $member->id . ' | ';
                    }
                    $usersData[$property] = '[' . rtrim($members, ' | ') . ']';
                }
                elseif($property == 'createdOn')
                {
                    foreach($user->createdOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $dateForUser = preg_split('/[\s]+/', $val);
                            $dateCreatedForUser = $dateForUser[0];
                            $timeCreatedForUser = $dateForUser[1];
                        }
                    }
                    $usersData['dateCreated'] = $dateCreatedForUser;
                    $usersData['timeCreated'] = $timeCreatedForUser;
                }
                elseif($property == 'modifiedOn')
                {
                    foreach($user->modifiedOn as $key=>$val)
                    {
                        if($key == 'date')
                        {
                            $lastModifiedForUser = $val;
                        }
                    }
                    $usersData['lastModified'] = $lastModifiedForUser;
                }
                elseif($property == 'name')
                {
                    if($user->isResearcher == false)
                    {
                        $usersData[$property] = $user->project_members[0]->firstName . ' ' .$user->project_members[0]->lastName;
                    }
                    else
                    {
                        $usersData[$property] = $user->$property;
                    }
                }
                elseif($property == 'username')
                {
                    if($user->isResearcher == false)
                    {
                        $usersData[$property] = $user->project_members[0]->userName;
                    }
                    else
                    {
                        $usersData[$property] = $user->$property;
                    }
                }
                else
                {
                    $usersData[$property] = $user->$property;
                }
            }
            array_push($usersList, $usersData);
            $members = str_replace($members, '', $members);
        }
        foreach($usersList as $eachUser)
        {
            foreach($eachUser as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $ustr = $ustr . $jsoncoded;
            }
            array_push($userJsonFormArray, $ustr);
            $ustr = str_replace($ustr, '', $ustr);
        }
        $userJsonFormArray = array_values(array_filter(array_unique($userJsonFormArray)));
        
        /***************************************************************/   
        
        /**************** Exporting All Recordings By Users ****************/
      
        $recordingsData = array();
        $recordingsList = array();
        $recJsonFormArray = array();
        $recordingsByUser = array();
        $allrecordings = array();
        $rstr = "";
        $rprops = "";
        $videoIds = "";
        $videoTitles = "";
        $pieceIds = "";
        $pieceTitles = "";
        $dateCreatedForRecording = "";
        $timeCreatedForRecording = "";
        $lastModifiedForRecording = "";
        $dateForRecording = "";
        $recordingOccurrenceDate = "";
        $recordingIds = "";
        $recordingProjects = "";
        $recordListByUser = array();
        $jsonRecordListByUser = array();
        // for each user 
        foreach($allUsers as $user)
        {
            $recordingsByUser = $recordingMapper->getByUser($user);
            foreach($recordingsByUser as $recording)
            {
                $allrecordings[] = $recording;
                $rprops = $recording->getProperties();
                foreach($rprops as $property)
                {
                    if($property == 'pieces')
                    {    
                        foreach($recording->pieces as $piece)
                        {
                            $pieceIds =  $pieceIds . $piece->id . ' | ';
                            $pieceTitles = $pieceTitles . $piece->title . ' | ';
                        }
                        $recordingsData[$property] = '[' . rtrim($pieceIds, ' | ') . ']';
                        $recordingsData['pieceTitles'] = '[' . rtrim($pieceTitles, ' | ') . ']';
                    }
                    elseif($property == 'user')
                    {
                        $recordingsData[$property] = $recording->user->id;
                    }
                    elseif($property == 'projects')
                    {
                        foreach($recording->projects as $project)
                        {
                            $recordingIds = $recordingIds . $project->id . ' | ';
                            $recordingProjects = $recordingProjects . $project->name . ' | ';
                        }
                        $recordingsData[$property] = '[' . rtrim($recordingIds, ' | ') . ']'; 
                        $recordingsData['projectNames'] = '[' . rtrim($recordingProjects, ' | ') . ']';
                    }
                    elseif($property == 'videos')
                    {
                        foreach($recording->videos as $video)
                        {
                            $videoIds =  $videoIds . $video->id. ' | ';
                            $videoTitles = $videoTitles . $video->name . ' | ';
                        }
                        $recordingsData[$property] = '[' . rtrim($videoIds, ' | ') . ']';
                        $recordingsData['videoTitles'] = '[' . rtrim($videoTitles, ' | ') . ']';
                    }
                    elseif($property == 'createdOn')
                    {
                        foreach($recording->createdOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $dateForRecording = preg_split('/[\s]+/', $val);
                                $dateCreatedForRecording = $dateForRecording[0];
                                $timeCreatedForRecording = $dateForRecording[1];
                            }
                        }
                        $recordingsData['dateCreated'] = $dateCreatedForRecording;
                        $recordingsData['timeCreated'] = $timeCreatedForRecording;
                    }
                    elseif($property == 'modifiedOn')
                    {
                        foreach($recording->modifiedOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $lastModifiedForRecording = $val;
                            }
                        }
                        $recordingsData['lastModified'] = $lastModifiedForRecording;
                    }
                    elseif($property == 'recordingTime')
                    {
                        foreach($recording->recordingTime as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $recordingOccurrenceDate = preg_split('/[\s]+/', $val);
                            }
                            $recordingsData[$property] = $recordingOccurrenceDate[0];
                        }
                    }
                    else
                    {
                        $recordingsData[$property] = $recording->$property;
                    }
                }
                array_push($recordingsList, $recordingsData);
                $pieceIds = str_replace($pieceIds, '', $pieceIds);
                $pieceTitles = str_replace($pieceTitles, '', $pieceTitles);
                $recordingIds = str_replace($recordingIds, '', $recordingIds);
                $recordingProjects = str_replace($recordingProjects, '', $recordingProjects);
                $videoIds = str_replace($videoIds, '', $videoIds);
                $videoTitles = str_replace($videoTitles, '', $videoTitles);
                unset($recordingsData);
                $recordingsData = array(); 
            }
            array_push($recordListByUser, $recordingsList);
            unset($recordingsList);
            $recordingsList = array(); 
        }
        
        foreach($recordListByUser as $recordList)
        {
            foreach($recordList as $eachRecording)
            {
                foreach($eachRecording as $key=>$value)
                {
                    $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                    $rstr = $rstr . $jsoncoded;
                }
                array_push($recJsonFormArray, $rstr);
                $rstr = str_replace($rstr, '', $rstr);
            }
            array_push($jsonRecordListByUser, $recJsonFormArray);
            unset($recJsonFormArray);
            $recJsonFormArray = array();  
        }
        $serialized = array_map('serialize', $jsonRecordListByUser);
        $unique = array_unique($serialized);
        $jsonRecordListByUser = array_values(array_filter(array_intersect_key($jsonRecordListByUser, $unique)));
        
        /*******************************************************************/

        /******************** Exporting All Pieces ************************/
        $piecesData = array();
        $piecesList = array();
        $pieprops = "";
        $pistr = "";
        $dateCreatedForPiece = "";
        $timeCreatedForPiece = "";
        $lastModifiedForPiece = "";
        $dateForPiece = "";
        $pieJsonFormArray = array();
        $allPieces = array();
        $uniqueRecordings = array_map('unserialize', array_unique(array_map('serialize', $allrecordings)));

        foreach($uniqueRecordings as $recording)
        {
            foreach($recording->pieces as $piece)
            {
                $allPieces[] = $pieceRepo->getById($piece->id);
            }
        }
        foreach($allPieces as $piece)
        {
             $pieprops = $piece->getProperties();
             foreach($pieprops as $property)
             {
                  if($property == 'createdOn')
                    {
                        foreach($piece->createdOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $dateForPiece = preg_split('/[\s]+/', $val);
                                $dateCreatedForPiece = $dateForPiece[0];
                                $timeCreatedForPiece = $dateForPiece[1];
                            }
                        }
                        $piecesData['dateCreated'] = $dateCreatedForPiece;
                        $piecesData['timeCreated'] = $timeCreatedForPiece;
                    }
                    elseif($property == 'modifiedOn')
                    {
                        foreach($piece->modifiedOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $lastModifiedForPiece = $val;
                            }
                        }
                        $piecesData['lastModified'] = $lastModifiedForPiece;
                    } 
                    else
                    {
                        $piecesData[$property] = $piece->$property;
                    }
             }
             array_push($piecesList, $piecesData);
        }
        foreach($piecesList as $eachPiece)
        {
            foreach($eachPiece as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $pistr = $pistr . $jsoncoded;
            }
            array_push($pieJsonFormArray, $pistr);
            $pistr = str_replace($pistr, '', $pistr);
        }
        
        
        /******************************************************************/
        
        /******************** Exporting All Videos ************************/
        
        $videosData = array();
        $videosList = array();
        $vidprops = "";
        $vistr = "";
        $dateCreatedForVideo = "";
        $timeCreatedForVideo = "";
        $lastModifiedForVideo = "";
        $dateForVideo = "";
        $vidJsonFormArray = array();
        $allVideos = array();

        foreach($uniqueRecordings as $recording)
        {
            foreach($recording->videos as $video)
            {
                $allVideos[] = $videoRepo->getById($video->id);
            }
        }
        foreach($allVideos as $video)
        {
             $vidprops = $video->getProperties();
             foreach($vidprops as $property)
             {
                 if($property == 'recording')
                 {
                    foreach($uniqueRecordings as $recording)
                    {
                        foreach($recording->videos as $rvideo)
                        {
                            if($video->id == $rvideo->id)
                            {
                                $videosData[$property] = $recording->id;
                            }
                        }
                    }
                 }
                 elseif($property == 'createdOn')
                 {
                        foreach($video->createdOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $dateForVideo = preg_split('/[\s]+/', $val);
                                $dateCreatedForVideo = $dateForVideo[0];
                                $timeCreatedForVideo = $dateForVideo[1];
                            }
                        }
                        $videosData['dateCreated'] = $dateCreatedForVideo;
                        $videosData['timeCreated'] = $timeCreatedForVideo;
                 }
                 elseif($property == 'modifiedOn')
                 {
                        foreach($video->modifiedOn as $key=>$val)
                        {
                            if($key == 'date')
                            {
                                $lastModifiedForVideo = $val;
                            }
                        }
                        $videosData['lastModified'] = $lastModifiedForVideo;
                 }
                 else
                 {
                    $videosData[$property] = $video->$property;
                 }
             }
             array_push($videosList, $videosData);
        }
        foreach($videosList as $eachVideo)
        {
            foreach($eachVideo as $key=>$value)
            {
                $jsoncoded = json_encode($key) . ':' . json_encode($value) . ',';
                $vistr = $vistr . $jsoncoded;
            }
            array_push($vidJsonFormArray, $vistr);
            $vistr = str_replace($vistr, '', $vistr);
        }
        /******************************************************************/
        
        
        
        return new JsonModel(array(
              "message" => "success",
              "bulkProject" => $projJsonFormArray,
              "bulkSurvey" => $survJsonFormArray,
              "bulkParticipant" => $jsonParticipListByProject,
              "otherMembers" => $otherPartJsonFormArray,
              "bulkUser" => $userJsonFormArray,
              "bulkRecording" => $jsonRecordListByUser,
              "bulkPiece" => $pieJsonFormArray,
              "bulkVideo" => $vidJsonFormArray,
              "bulkComment" => $comJsonFormArray
        ));
    }
    
    public function importAction()
    {
        $user = $this->getAuthenticatedUser();
        $this->layout()->setVariable('currentUser',$user);
        return new ViewModel();
    }
    
    // Data Import Function //
    public function importHandlerAction()
    {
        $request= $this->getRequest();
        $type = $_POST['type']; // the type that is specified in the select option
        $fileInfo = $request->getFiles(); // gets the file information 
        
        /*********************** Instructional Documentation **************************
        Basically, this is where all the import takes place. After selecting an option, it will 
        parse the txt file. The first line has the attributes, which will be stored into an array.
        All of the other lines beneath are attribute values that match it. Based on the attribute 
        values for the specific type of data (project, participants, recordings, etc.), it will create
        a new object that will be stored into the database. The attribute values will be set according
        to the values stored into the dataset array. 
         
        *****************************************************************************/
        // All repositories
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $surveyRepo = $this->getDocumentManager()->getRepository('Application\Document\SurveyDocument');
        $videoRepo = $this->getDocumentManager()->getRepository('Application\Document\VideoDocument');
        $pieceRepo = $this->getDocumentManager()->getRepository('Application\Document\PieceDocument');
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $commentRepo = $this->getDocumentManager()->getRepository('Application\Document\CommentDocument');
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $surveyMapper = $this->getMapperManager()->getMapper('Application\Mapper\SurveyMapper');
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        $recordingMapper = $this->getMapperManager()->getMapper('Application\Mapper\RecordingMapper');
        $commentMapper = $this->getMapperManager()->getMapper('Application\Mapper\CommentMapper');
        $filename = "";
        $filelocation = "";
        $status = "";
        
        /*** Get the file location and name ***/
        foreach($fileInfo->toArray() as $param)
        {
            foreach($param as $key=>$value)
            {
                if($key == 'name')
                {
                    $filename = $value;
                }
                elseif($key == 'tmp_name')
                {
                    $filelocation = $value;
                }
            }
        }
        $lines = file($filelocation);         /**** This line gets all the content by lines****/
        $delimiter = "\t"; // delimiter by tabs
        $attributes = array();
        $values = array();
        $dataSet = array();
        $projects = array();
        $surveys = array();
        $participants = array();
        $participantList = "";
        $videos = array();
        $pieces = array();
        $users = array();
        $comments = array();
        $recordings = array();
        $badSets = array();
        $set = array();
        $projSet = "";
        // get all the attributes and store it into the array
        $splitAttributes = explode($delimiter, $lines[0]);
        foreach ($splitAttributes as $attribute)
        {
            $attributes[] = trim($attribute);   
        }
    // if projects is selected    
    if($type == 'Projects')
    {
        foreach ($lines as $key=>$line)
        {
            // anything below the first line are attributes that will be stored into the array
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $projects[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($projects[$i]);
        }
        $projects = array_values($projects);

        foreach($projects as $project)
        {
           if(count($project) != count($attributes))
           {
               $badSets[] = $project;
           }
           else
           {
               $set = array_combine($attributes, $project);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for project
        foreach($dataSet as $set)
        {
            $primaryResearcher = $projectMemberMapper->getById($set['primaryResearcher']);
            $parSet = array();
            // bracketed values are parsed by getting rid of brackets and getting values separated by space
            $partSet = trim($set['participants'], ']');
            $partSet = trim($partSet, '[');
            $partSet = str_replace('|', '', $partSet);
            $partSet = preg_split('/[\s]+/', $partSet); 
            
            foreach($partSet as $part)
            {
                $parSet[] = $projectMemberMapper->getById($part);
            }
            // create a new project object based on the set of attributes from text file
            $project = new Project(array(
                    "id" => $set['id'],
                    "description" => $set['description'],
                    "institution" => $set['institution'],
                    "name" => $set['name'],
                    "orchestraName" => $set['orchestraName'],
                    "rationale" => $set['rationale'],
                    "status" => $set['status'],
                    "primaryResearcher" => $primaryResearcher,
                    "participants" => $parSet
                ));
             $projectRepo->create($project);
        }
    }      
    // if participants option is selected
    elseif($type == 'Participants')
    {
        foreach ($lines as $key=>$line)
        {
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $participants[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($participants[$i]);
        }
        $participants = array_values($participants);

        foreach($participants as $participant)
        {
           if(count($participant) != count($attributes))
           {
               $badSets[] = $participant;
           }
           else
           {
               $set = array_combine($attributes, $participant);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for participant
        foreach($dataSet as $set)
        {
            //$user = $userMapper->getById($set['user']);
            // get project by the project id in the file.
            $project = $projectMapper->getById($set['project']);
            
            // if the project member is a participant
            if($set['type'] == 'participant')
            {        
                // create a new participant object, set attributes
                $participant = new Participant(array(
              //    "id" => $set['id'],
                    "firstName" => $set['firstName'],
                    "lastName" => $set['lastName'],
                    "desk" => $set['desk'],
                    "instrument" => $set['instrument'],
                    "playerNumber" => $set['playerNumber'],
                    "playingPart" => $set['playingPart'],
                    "status" => $set['status'],
                    "userName" => $set['userName'],
                    "project" => $project,
           //       "user" => $user
                ));
                $participant = $projectMemberRepo->create($participant);
                $proj_members = array();
                $proj_members[] = $participant;
                $project_members[] = $participant; 
                
                // create a user object affiliated with that participant
                $pauser = new User(array(
                    "email" => $set['email'],
                    "isResearcher" => false,
                    "status" => $set['status'],
                    "project_members" => $proj_members
                ));
                $userRepo->create($pauser);
                unset($proj_members);
                $proj_members = array();
                
                $project->participants = $project_members;
                $participant->user = $pauser;
                $projectRepo->update($project);
                $projectMemberRepo->update($participant);
                $this->getDocumentManager()->flush();
            }
            // if the project member is a researcher
            elseif($set['type'] == 'researcher')
            {
                $researcherParticipant = new Researcher(array(
                    "id" => $set['id'],
                    "status" => $set['status'],
                    "project" => $project,
  //                "user" => $user
                ));
                $projectMemberRepo->create($researcherParticipant);
            }
        }
    }   
    // if surveys is selected
    elseif($type == 'Surveys')
    {
        foreach ($lines as $key=>$line)
        {
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $surveys[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($surveys[$i]);
        }
        $surveys = array_values($surveys);

        foreach($surveys as $survey)
        {
           if(count($survey) != count($attributes))
           {
               $badSets[] = $survey;
           }
           else
           {
               $set = array_combine($attributes, $survey);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for survey
        foreach($dataSet as $set)
        {
            $surveyProject = $projectMapper->getById($set['project']);
            $surveyRecording = $recordingRepo->getById($set['recording']);
            
            $usSet = array();
            // bracketed values are parsed by getting rid of brackets and getting values separated by space
            $userSet = trim($set['users'], ']');
            $userSet = trim($userSet, '[');
            $userSet = str_replace('|', '', $userSet);
            $userSet = preg_split('/[\s]+/', $userSet); 
            
            foreach($userSet as $user)
            {
                $usSet[] = $user;
            }
            
            // create a survey object and its attributes
            $survey = new Survey(array(
                    "title" => $set['title'],
                    "prompt" => $set['prompt'],
                    "pToPComments" => $set['pToPComments'],
                    "rToRComments" => $set['rToRComments'],
                    "project" => $surveyProject,
                    "recording" => $surveyRecording,
                    "status" => $set['status'],
                    "users" => $usSet
                ));
            $surveyRepo->create($survey);
        }
    }
    // if comments is selected
    elseif($type == 'Comments')
    {
        foreach ($lines as $key=>$line)
        {
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $comments[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($comments[$i]);
        }
        $comments = array_values($comments);

        foreach($comments as $comment)
        {
           if(count($comment) != count($attributes))
           {
               $badSets[] = $comment;
           }
           else
           {
               $set = array_combine($attributes, $comment);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for comment
        foreach($dataSet as $set)
        {
            $commentSurvey = $surveyMapper->getById($set['survey']);
            
            if($set['researcher'] != 'null')
            {
                $viewSet = array();
                // bracketed values are parsed by getting rid of brackets and getting values separated by space
                $viewerSet = trim($set['viewers'], ']');
                $viewerSet = trim($viewerSet, '[');
                $viewerSet = str_replace('|', '', $viewerSet);
                $viewerSet = preg_split('/[\s]+/', $viewerSet);

                foreach ($viewerSet as $viewer) {
                     $viewSet[] = $viewer;
                }
 
                if($set['comment'] == 'null')
                {
                    $researcher = $projectMemberMapper->getById($set['researcher']);
                    $commentLink = $commentMapper->getById($set['comment']);
                    
                    // create a new researcher comment object
                    $comment = new ResearcherCommentComment(array(
                        "id" => $set['id'],
                        "text" => $set['text'],
                        "status" => $set['status'],
                        "survey" => $commentSurvey,
                        "researcher" => $researcher,
                        "comment" => $commentLink,
                        "viewers" => $viewSet
                    ));
                }
                else
                {
                    $researcher = $projectMemberMapper->getById($set['researcher']);
                    
                    // create a new researcher video comment object
                    $comment = new ResearcherVideoComment(array(
                        "id" => $set['id'],
                        "text" => $set['text'],
                        "status" => $set['status'],
                        "survey" => $commentSurvey,
                        "researcher" => $researcher,
                        "viewers" => $viewSet
                    ));
                }
                $commentRepo->create($comment);
            }
            else
            {
                $participant = $projectMemberMapper->getById($set['participant']);
                if($set['viewable'] == 'false')
                {
                    $set['viewable'] = false;
                }
                elseif($set['viewable'] == 'true')
                {
                    $set['viewable'] = true;
                }
                
                // create a new participant comment object
                $comment = new ParticipantComment(array(
                    "id" => $set['id'],
                    "text" => $set['text'],
                    "status" => $set['status'],
                    "survey" => $commentSurvey,
                    "participant" => $participant,
                    "viewable" => $set['viewable']
                ));
                $commentRepo->create($comment);
            }
        }
    }
    // if recordings is selected
    elseif($type == 'Recordings')
    {
        foreach ($lines as $key=>$line)
        {
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $recordings[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($recordings[$i]);
        }
        $recordings = array_values($recordings);

        foreach($recordings as $recording)
        {
           if(count($recording) != count($attributes))
           {
               $badSets[] = $recording;
           }
           else
           {
               $set = array_combine($attributes, $recording);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for recording
        foreach($dataSet as $set)
        {
            $proSet = array();
            // bracketed values are parsed by getting rid of brackets and getting values separated by space
            $projSet = trim($set['projects'], ']');
            $projSet = trim($projSet, '[');
            $projSet = str_replace('|', '', $projSet);
            $projSet = preg_split('/[\s]+/', $projSet);  
           
            foreach($projSet as $proj)
            {
                $proSet[] = $projectMapper->getById($proj);
            }
            
            $viSet = array();
            // bracketed values are parsed by getting rid of brackets and getting values separated by space
            $videoSet = trim($set['videos'], ']');
            $videoSet = trim($videoSet, '[');
            $videoSet = str_replace('|', '', $videoSet);
            $videoSet = preg_split('/[\s]+/', $videoSet);  
            foreach($videoSet as $video)
            {
                $viSet[] = $videoRepo->getById($video);
            }              
            $piSet = array();
            // bracketed values are parsed by getting rid of brackets and getting values separated by space
            $pieceSet = trim($set['pieces'], ']');
            $pieceSet = trim($pieceSet, '[');
            $pieceSet = str_replace('|', '', $pieceSet);
            $pieceSet = preg_split('/[\s]+/', $pieceSet); 
            
            foreach($pieceSet as $piece)
            {
                $piSet[] = $pieceRepo->getById($piece);
            }
            
            $user = $userMapper->getById($set['user']);
            
            // create a new recording object
            $recording = new Recording(array(
                    "id" => $set['id'],
                    "location" => $set['location'],
                    "title" => $set['title'],
                    "recordingTime" => $set['recordingTime'],
                    "projects" => $proSet,
                    "videos" => $viSet,
                    "pieces" => $piSet,
                    "user" => $user
                ));
                $recordingRepo->create($recording);
        }
    }
    // if users is selected
    elseif($type == 'Users')
    {
        foreach ($lines as $key=>$line)
        {
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $users[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($users[$i]);
        }
        $users = array_values($users);

        foreach($users as $user)
        {
           if(count($user) != count($attributes))
           {
               $badSets[] = $user;
           }
           else
           {
               $set = array_combine($attributes, $user);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for user
        foreach($dataSet as $set)
        {
            // if user is a researcher
            if($set['isResearcher'] == 'true')
            {
                $memSet = array();
                // bracketed values are parsed by getting rid of brackets and getting values separated by space
                $projMemSet = trim($set['project_members'], ']');
                $projMemSet = trim($projMemSet, '[');
                $projMemSet = str_replace('|', '', $projMemSet);
                $projMemSet = preg_split('/[\s]+/', $projMemSet);  
                foreach($projMemSet as $projMem)
                {
                    $memSet[] = $projectMemberMapper->getById($projMem);
                }
                    
                $isResearcher = true;
                $user = new User(array(
                    "id" => $set['id'],
                    "bio" => $set['bio'],
                    "email" => $set['email'],
                    "isResearcher" => $isResearcher,
                    "mailAdd" => $set['mailAdd'],
                    "name" => $set['name'],
                    "org" => $set['org'],
                    "password" => $set['password'],
                    "phoneNumber" => $set['phoneNumber'],
                    "researchInterests" => $set['researchInterests'],
                    "status" => $set['status'],
                    "username" => $set['username'],
                    "wos" => $set['wos'],
                    "project_members" => $memSet
                ));
                $userRepo->create($user);
            }
            // if user is not a researcher
            elseif($set['isResearcher'] == 'false')
            {
                $memSet = array();
                $projMemSet = trim($set['project_members'], ']');
                $projMemSet = trim($projMemSet, '[');
                $projMemSet = str_replace('|', '', $projMemSet);
                $projMemSet = preg_split('/[\s]+/', $projMemSet);  
                foreach($projMemSet as $projMem)
                {
                    $memSet[] = $projectMemberMapper->getById($projMem);
                }
                
                $isResearcher = false;
                $user = new User(array(
                    "id" => $set['id'],
                    "isResearcher" => $isResearcher,
                    "password" => $set['password'],
                    "email" => $set['email'],
                    "status" => $set['status'],
                    "project_members" => $memSet
                ));
                $userRepo->create($user);
            }
        }
    }
    // if pieces is selected
    elseif($type == 'Pieces')
    {
        foreach ($lines as $key=>$line)
        {
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $pieces[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($pieces[$i]);
        }
        $pieces = array_values($pieces);

        foreach($pieces as $piece)
        {
           if(count($piece) != count($attributes))
           {
               $badSets[] = $piece;
           }
           else
           {
               $set = array_combine($attributes, $piece);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for piece
        foreach($dataSet as $set)
        {
            // create a piece object 
            $piece = new Piece(array(
                    "id" => $set['id'],
                    "composer" => $set['composer'],
                    "title" => $set['title'],
                    "arranger" => $set['arranger'],
                    "genre" => $set['genre'],
                ));
                $pieceRepo->create($piece);
        }
    }   
    // if videos is selected
    elseif($type == 'Videos')
    {
        foreach ($lines as $key=>$line)
        {
            if($key > 0)
            {
                $splitValues = explode($delimiter, $line);
                foreach($splitValues as $val)
                {
                    $values[] = trim($val);
                }
                $videos[] = $values;
                unset($values);
                $values = array();
            }
        }
        for ($i = 1 ; $i < 1000; $i += 2) {
            unset($videos[$i]);
        }
        $videos = array_values($videos);

        foreach($videos as $video)
        {
           if(count($video) != count($attributes))
           {
               $badSets[] = $video;
           }
           else
           {
               $set = array_combine($attributes, $video);  
               $dataSet[] = $set;
           }
        }
        // dataSet is a compilation of sets, where each set has attributes for video
        foreach($dataSet as $set)
        {
            // create a video object
            $video = new Video(array(
                    "id" => $set['id'],
                    "name" => $set['name'],
                    "link" => $set['link'],
                    "offsetTime" => $set['offsetTime'],
                    "startTime" => $set['startTime'],
                    "endTime" => $set['endTime'],
                    "position" => $set['position'],
                    "status" => $set['status']
                ));
                $videoRepo->create($video);
        }
    }   
        // if there is any bad data set, status is dataSetError
        if(count($badSets) > 0)
        {
            $status = 'dataSetError';
        }
        // else success
        else
        {
            $status = 'success';
        }
    
        $this->getDocumentManager()->flush();
        return new JsonModel(array(
              "message" => $status,
              "badData" => $badSets,
              "dataSet" => $dataSet,
              "pro" => $projSet
        ));
    }
}