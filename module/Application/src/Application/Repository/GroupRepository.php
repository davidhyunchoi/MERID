<?php
namespace Application\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository,
    Application\Document\GroupDocument;

class PieceRepository extends DocumentRepository
{
    
    public function create(GroupDocument $group)
    {
        $group->createdOn = $group->modifiedOn = time();
        $this->getDocumentManager()->persist($group);
        return $group;
    }
    
    public function update(GroupDocument $group) 
    {
        $query = $this->getDocumentManager()->createQueryBuilder('Application\Document\GroupDocument')
                        ->findAndUpdate()
                        ->returnNew()
                        ->field('id')->equals(new \MongoId($group->id))
                        ->field('project')->set($group->project)
                        ->field('name')->set($group->name)
                        ->field('members')->set($group->members)
                        ->field('modifiedOn')->set(time());
        $newGroup = $query->getQuery()->execute();
        //die(\Zend\Json\Json::encode($newPiece));
        return $newGroup;
    }
    
    public function getById($id)
    {
        return $this->find($id);
    }
    
    public function delete(GroupDocument $group)
    {
        $this->getDocumentManager()->remove($group);
        $this->getDocumentManager()->flush();
    }
    
}

