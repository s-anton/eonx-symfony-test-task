<?php

namespace App\DataTransformer\Customer;


use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\Customer\ItemOutputDto;
use App\Entity\Customer;

class ItemOutputDataTransformer implements DataTransformerInterface
{

    /**
     * @param Customer $object
     * @param string $to
     * @param array $context
     * @return object|void
     */
    public function transform($object, string $to, array $context = [])
    {
        $dto = new ItemOutputDto();
        $dto->id = $object->getId();
        $dto->fullName = $object->getFullName();
        $dto->email = $object->getEmail();
        $dto->country = $object->getCountry();
        $dto->username = $object->getUsername();
        $dto->gender = $object->getGender();
        $dto->city = $object->getCity();
        $dto->phone = $object->getPhone();

        return $dto;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === ItemOutputDto::class and $data instanceof Customer;
    }
}
