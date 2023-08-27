<?php

namespace App\Factory;

use App\Entity\SecurityUser;
use App\Repository\SecurityUserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<SecurityUser>
 *
 * @method        SecurityUser|Proxy                     create(array|callable $attributes = [])
 * @method static SecurityUser|Proxy                     createOne(array $attributes = [])
 * @method static SecurityUser|Proxy                     find(object|array|mixed $criteria)
 * @method static SecurityUser|Proxy                     findOrCreate(array $attributes)
 * @method static SecurityUser|Proxy                     first(string $sortedField = 'id')
 * @method static SecurityUser|Proxy                     last(string $sortedField = 'id')
 * @method static SecurityUser|Proxy                     random(array $attributes = [])
 * @method static SecurityUser|Proxy                     randomOrCreate(array $attributes = [])
 * @method static SecurityUserRepository|RepositoryProxy repository()
 * @method static SecurityUser[]|Proxy[]                 all()
 * @method static SecurityUser[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static SecurityUser[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static SecurityUser[]|Proxy[]                 findBy(array $attributes)
 * @method static SecurityUser[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static SecurityUser[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @phpstan-method        Proxy<SecurityUser> create(array|callable $attributes = [])
 * @phpstan-method static Proxy<SecurityUser> createOne(array $attributes = [])
 * @phpstan-method static Proxy<SecurityUser> find(object|array|mixed $criteria)
 * @phpstan-method static Proxy<SecurityUser> findOrCreate(array $attributes)
 * @phpstan-method static Proxy<SecurityUser> first(string $sortedField = 'id')
 * @phpstan-method static Proxy<SecurityUser> last(string $sortedField = 'id')
 * @phpstan-method static Proxy<SecurityUser> random(array $attributes = [])
 * @phpstan-method static Proxy<SecurityUser> randomOrCreate(array $attributes = [])
 * @phpstan-method static RepositoryProxy<SecurityUser> repository()
 * @phpstan-method static list<Proxy<SecurityUser>> all()
 * @phpstan-method static list<Proxy<SecurityUser>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Proxy<SecurityUser>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Proxy<SecurityUser>> findBy(array $attributes)
 * @phpstan-method static list<Proxy<SecurityUser>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Proxy<SecurityUser>> randomSet(int $number, array $attributes = [])
 */
final class SecurityUserFactory extends ModelFactory
{
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->safeEmail(),
            'firstName' => self::faker()->firstName(),
            'plainPassword' => 'tada',
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this->afterInstantiate(function (SecurityUser $securityUser) {
            if ($securityUser->getPlainPassword()) {
                $securityUser->setPassword(
                    $this->passwordHasher->hashPassword($securityUser, $securityUser->getPlainPassword())
                );
            }
        });
    }

    protected static function getClass(): string
    {
        return SecurityUser::class;
    }
}
