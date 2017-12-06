<?php

namespace Kunstmaan\MediaBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Kunstmaan\MediaBundle\Entity\Folder;
use Kunstmaan\MediaBundle\Entity\Media;

/**
 * MediaRepository
 */
class MediaRepository extends EntityRepository
{
    /**
     * @param Media $media
     */
    public function save(Media $media)
    {
        $em = $this->getEntityManager();
        $em->persist($media);
        $em->flush();
    }

    /**
     * @param Media $media
     */
    public function delete(Media $media)
    {
        $em = $this->getEntityManager();
        $media->setDeleted(true);
        $em->persist($media);
        $em->flush();
    }

    /**
     * @param int $mediaId
     *
     * @return object
     * @throws EntityNotFoundException
     */
    public function getMedia($mediaId)
    {
        $media = $this->find($mediaId);
        if (!$media) {
            throw new EntityNotFoundException();
        }

        return $media;
    }

    /**
     * @param integer $pictureId
     *
     * @return object
     * @throws EntityNotFoundException
     */
    public function getPicture($pictureId)
    {
        $em = $this->getEntityManager();

        $picture = $em->getRepository('KunstmaanMediaBundle:Image')->find($pictureId);
        if (!$picture) {
            throw new EntityNotFoundException();
        }

        return $picture;
    }

    /**
     * @param string $phrase
     * @param Folder $folder
     * @param bool $deep
     * @return Query
     */
    public function search($phrase, Folder $folder, $deep)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('m');
        $qb->join('m.folder', 'f');
        $qb->where('f.deleted != true');

        $deletedCondition = $qb->expr()->andX()
            ->add($qb->expr()->neq('m.deleted', true))
            ->add($qb->expr()->neq('f.deleted', true));

        $searchCondition = $qb->expr()->orX()
            ->add($qb->expr()->like('m.name', ':phrase'))
            ->add($qb->expr()->like('m.description', ':phrase'))
            ->add($qb->expr()->like('m.copyright', ':phrase'));
        $qb->setParameter('phrase', '%' . $phrase . '%');

        if ($deep) {
            //Also search the current folder we're in so use gte and lte
            $folderCondition = $qb->expr()->andX()
                ->add($qb->expr()->gte('f.lft', ':left'))
                ->add($qb->expr()->lte('f.rgt', ':right'));
            $qb->setParameter('left', $folder->getLeft());
            $qb->setParameter('right', $folder->getRight());
        } else {
            $folderCondition = $qb->expr()->eq('m.folder', ':folder');
            $qb->setParameter('folder', $folder);
        }

        $conditions = $qb->expr()->andX()
            ->add($deletedCondition)
            ->add($searchCondition)
            ->add($folderCondition);
        $qb->where($conditions);

        return $qb->getQuery();
    }
}
