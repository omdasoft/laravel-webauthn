<?php

namespace Omdasoft\LaravelWebauthn\Support;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

class Serializer
{
    public function __construct(
        protected SymfonySerializer $serializer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $value): array
    {
        $json = $this->toJson($value);

        return json_decode($json, true);
    }

    public function toJson(mixed $value): string
    {
        return $this->serializer->serialize(
            $value,
            'json',
            [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
            ]
        );
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $desiredClass
     * @return T
     */
    public function fromJson(string $value, string $desiredClass): mixed
    {
        return $this->serializer->deserialize($value, $desiredClass, 'json');
    }

    /**
     * @template T
     *
     * @param  array<string, mixed>  $value
     * @param  class-string<T>  $desiredClass
     * @return T
     */
    public function fromArray(array $value, string $desiredClass): mixed
    {
        return $this->serializer->denormalize($value, $desiredClass);
    }

    public static function create(): self
    {
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

        /** @var SymfonySerializer $serializer */
        $serializer = (new WebauthnSerializerFactory($attestationStatementSupportManager))->create();

        return new self($serializer);
    }

    public static function make(): self
    {
        return app(self::class);
    }
}
