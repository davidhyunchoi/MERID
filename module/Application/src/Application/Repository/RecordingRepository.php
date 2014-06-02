<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\RecordingDocument,
    Application\Document\UserDocument as User,
    Application\Document\ProjectDocument as Project;

class RecordingRepository extends DocumentRepository
{
    
    public function create(RecordingDocument $recording){
        $recording->createdOn = $recording->modifiedOn = time();
        $this->getDocumentManager()->persist($recording);
        return $recording;
    }
    
    public function update(RecordingDocument $recording) {
        $recording->modifiedOn = time();
        return $recording;
    }
    
    /**
     * Get all recordings which reference $user
     * @param \Application\Document\UserDocument $user
     * @return array of recordingDocuments
     */
    public function getByUser(User $user)
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\RecordingDocument')
                 ->field('user')->references($user);
        $cursor = $query->getQuery()->execute();
        $recordings = array();
        foreach($cursor as $recording){
            array_push($recordings, $recording);
        }
        return $recordings;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(RecordingDocument $recording){
        $this->getDocumentManager()->remove($recording);
        $this->getDocumentManager()->flush();
    }
    
    public function findAndLink($recordingId, Project $project){
        $recording = $this->find($recordingId);
        if(!$recording->projects->contains($project)){
            $recording->projects->add($project);    
        }
        $this->getDocumentManager()->persist($recording);
        return $recording;
    }
}

