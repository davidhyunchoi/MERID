<?php

namespace Application\Mapper;

use Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\RepositoryManager,
    Application\Mapper\BaseMapper,
    Application\Document\RecordingDocument as Recording,
    Application\Document\VideoDocument as Video,
    Application\Document\PieceDocument as Piece,
    Application\Document\UserDocument as User,
    Application\Document\ProjectDocument as Project;

class RecordingMapper extends BaseMapper {
    
    /**
     * creates new recordingDocument
     * @param \Application\Document\RecordingDocument $recording a recordingDocument
     * @param type $pieces an array of pieceDocuments
     * @param type $videos an array of videoDocuments
     * @return \Application\Document\RecordingDocument a recordingDocument
     */
    public function create(Recording $recording, $pieces, $videos)
    {
        $pieceRepo = $this->getDocumentManager()->getRepository('Application\Document\PieceDocument');
        $videoRepo = $this->getDocumentManager()->getRepository('Application\Document\VideoDocument');
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        
        $recording = $recordingRepo->create($recording);
        
        $recording->pieces = new \Doctrine\Common\Collections\ArrayCollection();
        $recording->videos = new \Doctrine\Common\Collections\ArrayCollection();
        
        foreach($pieces as $piece){
            $piece = $pieceRepo->create($piece);
            $recording->pieces->add($piece);
        }
        
        foreach($videos as $video){
            $video = $videoRepo->create($video);
            $recording->videos->add($video);
        }
        
        return $recording;
    }
    public function getByUser($user)
    {
        $recordingRepo = $this->getDocumentManager()->getRepository('Application\Document\RecordingDocument');
        $recordings = $recordingRepo->getByUser($user);
        return $recordings;
    }
    /**
     * check if user has permission to view this recording
     * @param \Application\Document\UserDocument $user
     * @param type $recId id of recording we are checking
     * @param \Application\Document\ProjectDocument $project if supplied, grant
     *  permission if the user is on a project with this recording
     * @return boolean
     */
    public function hasPermission(User $user, $recId, Project $project = null){
        return true;
    }
    
}


