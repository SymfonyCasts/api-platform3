<?php

namespace App\Factory;

use App\Entity\DragonTreasure;
use App\Repository\DragonTreasureRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<DragonTreasure>
 *
 * @method        DragonTreasure|Proxy create(array|callable $attributes = [])
 * @method static DragonTreasure|Proxy createOne(array $attributes = [])
 * @method static DragonTreasure|Proxy find(object|array|mixed $criteria)
 * @method static DragonTreasure|Proxy findOrCreate(array $attributes)
 * @method static DragonTreasure|Proxy first(string $sortedField = 'id')
 * @method static DragonTreasure|Proxy last(string $sortedField = 'id')
 * @method static DragonTreasure|Proxy random(array $attributes = [])
 * @method static DragonTreasure|Proxy randomOrCreate(array $attributes = [])
 * @method static DragonTreasureRepository|RepositoryProxy repository()
 * @method static DragonTreasure[]|Proxy[] all()
 * @method static DragonTreasure[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static DragonTreasure[]|Proxy[] createSequence(array|callable $sequence)
 * @method static DragonTreasure[]|Proxy[] findBy(array $attributes)
 * @method static DragonTreasure[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static DragonTreasure[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class DragonTreasureFactory extends ModelFactory
{
    private const TREASURE_NAMES = ['pile of gold coins', 'diamond-encrusted throne', 'rare magic staff', 'enchanted sword', 'set of intricately crafted goblets', 'collection of ancient tomes', 'hoard of shiny gemstones', 'chest filled with priceless works of art', 'giant pearl', 'crown made of pure platinum', 'giant egg (possibly a dragon egg?)', 'set of ornate armor', 'set of golden utensils', 'statue carved from a single block of marble', 'collection of rare, antique weapons', 'box of rare, exotic chocolates', 'set of ornate jewelry', 'set of rare, antique books', 'giant ball of yarn', 'life-sized statue of the dragon itself', 'collection of old, used toothbrushes', 'box of mismatched socks', 'set of outdated electronics (like floppy disks)', 'giant jar of pickles', 'collection of novelty mugs with silly sayings', 'pile of old board games', 'giant slinky', 'collection of rare, exotic hats'];

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
            'coolFactor' => self::faker()->numberBetween(1, 10),
            'description' => self::faker()->paragraph(),
            'isPublished' => true,
            'name' => self::faker()->randomElement(self::TREASURE_NAMES),
            'plunderedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-1 year')),
            'value' => self::faker()->numberBetween(1000, 1000000),
            'owner' => UserFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(DragonTreasure $dragonTreasure): void {})
        ;
    }

    protected static function getClass(): string
    {
        return DragonTreasure::class;
    }
}
