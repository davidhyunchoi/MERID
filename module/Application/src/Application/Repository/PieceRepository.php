<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\PieceDocument;

class PieceRepository extends DocumentRepository
{
    
    public function create(PieceDocument $piece){
        $piece->createdOn = $piece->modifiedOn = time();
        $this->getDocumentManager()->persist($piece);
        return $piece;
    }
    
    public function update(PieceDocument $piece) {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\PieceDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($piece->id))
                        ->field('composer')->set($piece->composer)
                        ->field('title')->set($piece->title)
                        ->field('arranger')->set($piece->arranger)
                        ->field('genre')->set($piece->genre)
                        ->field('modifiedOn')->set(time());
        $newPiece = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newPiece));
        return $newPiece;
    }
    
    public function getById($id){
        return $this->find($id);
    }
    
    public function delete(PieceDocument $piece){
        $this->getDocumentManager()->remove($piece);
        $this->getDocumentManager()->flush();
    }
    
}

