<?php

namespace Application\Mapper;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\RepositoryManager,
    Application\Mapper\BaseMapper,
    Application\Document\ParticipantDocument as Participant,
    Application\Document\ResearcherDocument as Reseacher,
    Application\Document\ProjectMemberDocument as ProjectMember,
    Application\Document\ProjectDocument as Project,
    Application\Document\UserDocument as User;

class ProjectMemberMapper extends BaseMapper {
    
    /**
     * 
     * @param type $email - email address of projectMember being created
     * @param type $projectMember - projectMember document
     * @param type $isResearcher - true if we're creating a researcher
     * @param type $currentProject - if adding members to a project that exists, pass the project
     * @param type $baseUrl url of current host
     * @return returns the new projectMember
     * @throws Exception
     */
    public function create($email, $projectMember, $isResearcher, $currentProject = null, $baseUrl) 
    {
        $userMapper = $this->getMapperManager()->getMapper('Application\Mapper\UserMapper');
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        
        $user = $userRepo->getByEmail($email);       
        $inviteSent = null;
        // if the user and the project are already in the system
        if($user && $currentProject)
        {	 
            //check to see if the user alread belongs to the project
            foreach($user->project_members as $member){
                if($member->project == $currentProject){ 
        	    //return null; //TODO: should throw some sort of informative exception
                    throw new \Exception('Project Member already exists!');
                }
            }
        }
        //if the user doesn't exist, create one
        else if(!$user)
        {
            $user = new User(array(
                "email" => $email,
                "status"=> "new",
                "project_members" => new \Doctrine\Common\Collections\ArrayCollection(),
                ));
            $inviteSent = $userMapper->inviteUser($user); 
            $user = $inviteSent['user'];

            $emailService = $this->getServiceManager()->get('application.email');
            $message = $emailService->inviteToMeridMessage($baseUrl, $inviteSent['user'], $inviteSent['code']);
            $emailService->sendEmail($user->email, $message);
        } 

        
        $projectMember->user = $user;
        $projectMember->project = $currentProject;
        $projectMember->status = "new";
        $projectMember = $projectMemberRepo->create($projectMember);
        
        $user->project_members->add($projectMember);
        /*
         * if the user does not yet have researcher access set it to whatever
         * this new access level is
         */
        if(!$user->isResearcher)
        {
            $user->isResearcher = $isResearcher;
        }
        
        /*
         * if we're adding members to an established project add references
         * from the project to the new member
         */
        if($currentProject)
        {
            $isResearcher ? $currentProject->researchers->add($projectMember) : 
                $currentProject->participants->add($projectMember);
        }
        
        $this->getDocumentManager()->flush();
      
        return $projectMember;
    }
    
	
	    
    public function getParticipantByUserId($userId)
    {
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $participant = $projectMemberRepo->getParticipantByUserId($userId);
        return $participant;
    }
    
    /**
     *
     * @param type $memberId - id of member to be updated
     * @param type $data - data passed from the form
     * @param type $multi - says whether or not this is being used in series, in
     *  which case you shouldn't flush every time, you should flush after all
     *  the participants are updated.
     */
    public function update($memberId, $data, $multi = false)
    {
        $memberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $member = $memberRepo->find($memberId);
        
        foreach($data as $key=>$value)
        {
            if($key == 'email')
            {
                $member->user->email = $value;
            }
            else
            {
                $member->$key = $value;
            }
        }
        $member = $memberRepo->update($member);
        $multi ? : $this->getDocumentManager()->flush();
        return $member;
    }
    
    /**
     * Gets all participants regardless of status based on a projectId
     * @param type $projectId of project in question
     * @return type an array of participant documents
     */
    public function getParticipants($projectId)
    {
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $participants = $projectMemberRepo->getParticipantsByProject($projectRepo->find($projectId));
        return $participants;
    }
    
    /**
     * Gets all participants regardless of status based on a projectId
     * @param type $projectId of project in question
     * @param type $status of the participants, either active, new, or removed
     * @return type an array of participant documents
     */
    public function getParticipantsByStatus($projectId, $status)
    {
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $participants = $projectMemberRepo->getParticipantsByProjectAndStatus($projectRepo->find($projectId), $status);
        return $participants;
    }
    
    /**
     * This is a duplicate function and should be removed upon refactoring
     * @param type $projectId
     * @return type
     */
    public function getAllParticipants($projectId)
    {
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $participants = $projectMemberRepo->getAllParticipantsByProject($projectMapper->getById($projectId));
        return $participants;
    }
    
    /**
     * Use this function to determine if a user can view a project's participants
     * 
     * @todo we need to decide who gets view privleges for a project; should
     *  probably add a third variable for distinguishing researcher and participant
     *  accessible information...
     * @param type $projectId
     * @param \Application\Document\UserDocument $user
     */
    public function canViewParticipants($projectId, User $user)
    {
        return true;
    }
    
    public function getMemberByProjectAndUser($project,$user){
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $member = $projectMemberRepo->getMemberByProjectAndUser($project,$user);
        return $member;
    }
    
    public function getById($id) 
    {
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $projectMember = $projectMemberRepo->getById($id);      
        return $projectMember;
    }
    
    /**
     * Get all researchers (barring the primary researcher) of the project in 
     *  question
     * @param type $projectId of project in question
     * @return type array of researcherDocuments
     */
    public function getSecondaryResearchers($projectId)
    {
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $projectMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMapper');
        $researchers = $projectMemberRepo->getSecondaryResearchersByProject($projectMapper->getById($projectId));
        return $researchers;
    }
}


