<?php

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpFactory
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly HttpClientInterface $client){}

    private function handleApi(string $url, string $method = 'GET', array $options = []): ?ResponseInterface
    {
        try {
            return $this->client->request($method, $url, $options);
        } catch (TransportExceptionInterface $e) {
            print $e; return null;
        }
    }

    public function handleData(mixed $data, string $type, array $context = ['groups' => ['default:write']])
    {
        $values = $this->serializer->deserialize($data, $type, 'json', $context);
        $object = $this->validator->validate($values);

        if ($object->count()) {
            $errors = array(); /** @var ConstraintViolationInterface $v */
            foreach ($object as $v) $errors[$v->getPropertyPath()] = $v->getMessage();
            return $errors;
        }

        return $values;
    }
}