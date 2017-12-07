<?php

namespace Kunstmaan\MediaBundle\Repository;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\Entity\Translation;
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
    public function search($phrase, Folder $folder, $deep, $defaultLocale, $searchLocale)
    {
        $searchFields = ['name', 'description', 'copyright'];

        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('m');
        $qb->join('m.folder', 'f');

        $deletedCondition = $qb->expr()->andX()
            ->add($qb->expr()->neq('m.deleted', true))
            ->add($qb->expr()->neq('f.deleted', true));

        $searchCondition = $qb->expr()->orX();
        foreach ($searchFields as $field) {
            $searchCondition->add($qb->expr()->like('m.' . $field, ':phrase'));
        }

        //When the searchLocale is not the defaultLocale, search the translations as well
        if ($defaultLocale !== $searchLocale) {
            $qb->leftJoin(Translation::class, 't', 'WITH', 't.foreignKey = f.id AND t.objectClass = :class AND t.locale = :locale');

            //Only match the default translations where there are no translations
            $defaultSearchCondition = $qb->expr()->andX()
                ->add($searchCondition)
                ->add($qb->expr()->isNull('t.id'));

            //Match the translation (which is only joined on locale and objectClass)
            $translationSearch = $qb->expr()->andX()
                ->add($qb->expr()->like('t.content', ':phrase'))
                ->add($qb->expr()->in('t.field', ':fields'));

            $qb->setParameter('class', $this->getClassName());
            $qb->setParameter('fields', $searchFields);
            $qb->setParameter('locale', $searchLocale);
            $searchCondition = $qb->expr()->orX()
                ->add($defaultSearchCondition)
                ->add($translationSearch);
        }
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
