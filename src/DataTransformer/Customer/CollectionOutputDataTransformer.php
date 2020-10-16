<?php

namespace App\DataTransformer\Customer;


use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\Customer\CollectionOutputDto;
use App\Entity\Customer;

class CollectionOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @param Customer $object
     * @param string $to
     * @param array $context
     * @return object|void
     */
    public function transform($object, string $to, array $context = [])
    {
        $dto = new CollectionOutputDto();
        $dto->id = $object->getId();
        $dto->fullName = $object->getFullName();
        $dto->email = $object->getEmail();
        $dto->country = $object->getCountry();

        return $dto;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === CollectionOutputDto::class && $data instanceof Customer;
    }
}
