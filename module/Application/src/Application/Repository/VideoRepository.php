<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\VideoDocument;

class VideoRepository extends DocumentRepository
{
    
    public function create(VideoDocument $video){
        $video->createdOn = $video->modifiedOn = time();
        $video->status = 'active';
        $this->getDocumentManager()->persist($video);
        return $video;
    }
    
    public function update(VideoDocument $video) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\VideoDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($video->id))
                        ->field('name')->set($video->name)
                        ->field('link')->set($video->link)
                        ->field('offsetTime')->set($video->offsetTime)
                        ->field('position')->set($video->position)
                        ->field('modifiedOn')->set(time());
        $newVideo = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newPiece));
        return $newVideo;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(VideoDocument $video){
        $this->getDocumentManager()->remove($video);
        $this->getDocumentManager()->flush();
    }
    
}

