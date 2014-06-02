<?php

namespace Application\Mapper;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\RepositoryManager,
    Application\Repository\ProjectRepository,
    Application\Mapper\BaseMapper,
    Application\Document\ProjectDocument as Project,
    Application\Document\ProjectMemberDocument,
    Application\Document\ResearcherDocument as Researcher,
    Application\Document\UserDocument as User;

class ProjectMapper extends BaseMapper {
    
    /**
     * @param \Application\Document\ProjectDocument $project - project with user provided information filled in
     * @param \Application\Document\UserDocument $user - user trying to create project
     * @return \Application\Document\ProjectDocument
     */
    public function create(Project $project, User $user)
    {
        $projectMemberMapper = $this->getMapperManager()->getMapper('Application\Mapper\ProjectMemberMapper');
        $projectMemberRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectMemberDocument');
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $demoRepo = $this->getDocumentManager()->getRepository('Application\Document\DemographicsQuestionDocument'); 
        
        $project = $projectRepo->create($project);
        $project->participants = new \Doctrine\Common\Collections\ArrayCollection();
        
        /*
         * creates researcher document, sets it as primary researcher
         * sets status to active; it's not waiting to accept invite
         * because it's the creator of the project
         */
        $primaryResearcher = new Researcher();
        $primaryResearcher->project = $project;
        $primaryResearcher->user = $user;
        $primaryResearcher->status = 'active';
        $primaryResearcher = $projectMemberRepo->create($primaryResearcher);
        $project->primaryResearcher = $primaryResearcher;
        $user->project_members->add($primaryResearcher);
        
        $demoQuestions = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($project->demographicQuestions as $question){
            $demoQuestions->add($demoRepo->find($question));
        }
        $project->demographicQuestions = $demoQuestions;
        
        $this->getDocumentManager()->flush();
        return $project;
    }
    
    /**
     *
     * @param type $projectId - id of project to be updated
     * @param type $data - data passed from the form
     */
    public function update($projectId, $data)
    {
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $project = $projectRepo->getById($projectId);
        
        foreach($data as $key=>$value)
        {
            $project->$key = $value;
        }
        $project = $projectRepo->update($project);
        $this->getDocumentManager()->flush();
        return $project;
    }
    
    /**
     * Use this function to determine if a user has permission to edit a project
     * or it's members
     * 
     * @param type $projectId - id of the project in question
     * @param \Application\Document\UserDocument $user - current user
     */
    public function hasPermission($projectId, User $user)
    {
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $project = $projectRepo->getById($projectId);
        
        foreach($user->project_members as $member)
        {
            if($project->primaryResearcher == $member || $project->researchers->contains($member))
            {
                return true;
            }
        }
        return false;     
    }
    
    public function viewAll($id) 
    {
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $projects = $projectRepo->viewAll($id);      
        return $projects;
    }
    
    public function viewById($id) 
    {
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $project = $projectRepo->viewById($id);      
        return $project;
    }
	
    public function getById($id) 
    {
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $project = $projectRepo->getById($id);      
        return $project;
    }
    
    public function getUnacceptedProjects($userId){
        $userRepo = $this->getDocumentManager()->getRepository('Application\Document\UserDocument');
        $user = $userRepo->getById($userId); 
 
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $projects = $projectRepo->getUnacceptedProjects($user);
        //die(\Zend\Json\Json::encode($projects));
        return $projects;
    }
    
   
    
    public function getProjectAccepts($project,$status,$user){     
        $projectRepo = $this->getDocumentManager()->getRepository('Application\Document\ProjectDocument');
        $participants = $projectRepo->getProjectAccepts($project,$status,$user);
        return $participants;
    }
    
    /**
     * get all projects where $user is a researcher
     * @param \Application\Document\UserDocument $user
     * @return type array of projectDocuments
     */
    public function getResearchProjects(User $user){
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectMemberDocument')
                        ->field('user')->references($user)
                        ->field('type')->equals('researcher')
                        ->field('status')->equals('active');
        $cursor = $query->getQuery()->execute();
        $projects = array();
        foreach($cursor as $item){
            array_push($projects, $item->project);
        }
        return $projects;
    }
    
    public function addResearcherToSurveys(User $user, Project $project)
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('project')->references($project);
        $cursor = $query->getQuery()->execute();
        foreach($cursor as $item){
            $users = $item->users;
            array_push($users, $user->id);
            $item->users = $users;
        }
        $this->getDocumentManager()->flush();
    }
    
    
    public function delete(Project $project)
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\ProjectDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($project->id))
                        ->field('status')->set("deleted");
         $newProject = $query->getQuery()->execute();
        
         $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->update()
                        ->multiple(true)
                        ->field('project')->references($project)
                        ->field('status')->set("deleted");
         
         $query->getQuery()->execute();
        return $newProject;
    }
    
  /*  public function removeResearcherFromSurveys(User $user, Project $project)
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\SurveyDocument')
                        ->field('project')->references($project);
        $cursor = $query->getQuery()->execute();
        foreach($cursor as $item){
            $users = $item->users;
            array_push($users, $user->id);
            $item->users = $users;
        }
        $this->getDocumentManager()->flush();      
    }*/
}


