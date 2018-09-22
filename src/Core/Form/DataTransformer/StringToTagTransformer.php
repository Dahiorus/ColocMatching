<?php

namespace App\Core\Form\DataTransformer;

use App\Core\Entity\Tag\Tag;
use App\Core\Repository\Tag\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\DataTransformerInterface;

class StringToTagTransformer implements DataTransformerInterface
{
    /** @var TagRepository */
    private $tagRepository;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->tagRepository = $entityManager->getRepository(Tag::class);
    }


    public function transform($value)
    {
        /** @var Tag $value */
        if (empty($value))
        {
            return "";
        }

        return $value->getValue();
    }


    public function reverseTransform($value)
    {
        /** @var string $value */
        if (empty($value))
        {
            return null;
        }

        try
        {
            /** @var Tag $tag */
            $tag = $this->tagRepository->findOneByValue($value);

            if (empty($tag))
            {
                $tag = new Tag($value);
            }

            return $tag;
        }
        catch (NonUniqueResultException $e)
        {
            throw new \RuntimeException("Unexpected error while getting a tag from [$value]", 0, $e);
        }
    }

}
