<?php

namespace App\Service;

use App\Model\DataSet;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerService
{
    /**
     * @param string $className
     * @param array<int,array<string, mixed>> $array
     * @return array<int,object>
     */
    public function objectsFromArray(string $className, array $array): array
    {
        $objects = [];
        $serializer = new Serializer([new ObjectNormalizer()], []);
        foreach ($array as $objectArray) {
            $objects[] = $serializer->denormalize($objectArray, $className);
        }

        return $objects;
    }

    /**
     * @param string $json
     * @return array<int,array<string, mixed>>
     */
    public function arrayFromJson(string $json): array
    {
        $serializer = new Serializer([], [new JsonEncoder()]);
        $json = iconv('UTF-8', 'UTF-8//IGNORE', $json);
        if ($json === false) {
            throw new \Exception('Failed to convert json to UTF-8');
        }
        return $serializer->decode($json, 'json', ['as_collection' => true]);
    }
}
