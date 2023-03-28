# Query Extension: Auto-Filter a Collection

When we get a collection of treasures, we currently return *every*
treasure, even unpublished treasures. Probably some of these are unpublished.
We *did* add a filter to control this... but let's be honest, that's not the best
solution. Really, we need to *not* return unpublished treasures at all.

Find the [API Platform Upgrade Guide](https://api-platform.com/docs/core/upgrade-guide/#api-platform-2730)...
and search for the word "state" to find a section that talks about "providers" and
"processors". We talked about state processors earlier, like the `PersistProcessor`
on the `Put` and `Post`operations, which is responsible for saving the item to the
database.

## State Providers

But each operation also has something called a state *provider*. *This* is what's
responsible for *loading* the object or collection of objects. For example, when
we make a GET request for a single item, the `ItemProvider` is what's responsible
for taking the ID and querying the database. There's also a `CollectionProvider`
to load a collection of items.

So if we want to automatically hide unpublished treasures, one option would be to
*decorate* this `CollectionProvider`, very much like we did with the `PersistProvider`.
Except... that won't *quite* work. Why? The `CollectionProvider` from Doctrine executes
the query and returns the results. So all *we* would be able to do is *take* those
results... then hide the ones we don't want. That's... not ideal for performance -
imagine loading 50 treasures then only showing 10 - and it would confuse pagination.
What we *really* want to do is *modify* the query itself: to add a
`WHERE isPublished = true`.

## Testing for the Behavior

Luckily for us, this `CollectionProvider` "provides" its *own* extension point that
lets us do *exactly* that.

Before we dive in, let's update a test to show the behavior we want. Find
`testGetCollectionOfTreasures()`. Take control of these 5 treasures and
make them all `isPublished => true`... because right now, in `DragonTreasureFactory`,
`isPublished` is set to a random value.

*Then* add one more with `createOne` and `isPublished` false.

Awesome! And we *still* want to assert that this returns just 5 items. So...
let's make sure it fails:

```terminal
symfony php bin/console phpunit --filter=testGetCollectionOfTreasures
```

And... yea! It returns 6 items.

## Collection Query Extensions

Ok, to modify the query for a collection endpoint, we're going to create something
called a query extension. Anywhere in `src/` - I'll do it in the `ApiPlatform/`
directory - create a new class called `DragonTreasureIsPublishedExtension`. Make
this implement `QueryCollectionExtensionInterface`, then go to Code -> Generate or
Command + N on a Mac - and generate the one method we need: `applyToCollection()`.

This is pretty cool: it passes us the `$queryBuilder` and a few other pieces of
info. Then, we can *modify* that `QueryBuilder`. The best part? The `QueryBuilder`
*already* takes into account things like pagination and any filters that have been
applied. So those are *not* things we need to worry about.

*Also*, thanks to Symfony's autoconfiguration system, *just* by creating this class
and making it implement this interface, it will *already* be called whenever a
collection endpoint is used!

## Query Extension Logic

In fact, it will be called for *any* resource. So the first thing we need is
`if (DragonTreasure::class !== $resourceClass)` - fortunately it passes us the
class name - then return.

Below, *this* is where we get to work. Now, every `QueryBuilder` object has a
*root alias* that refers to the class or table that you're querying. Usually,
*we* create the `QueryBuilder`... like from inside a repository we say something
like `$this->createQueryBuilder('d')` and `d` becomes that "root alias". Then we
use that in other parts of the query.

However, in *this* situation, *we* didn't create the `QueryBuilder`, so *we* never
chose that root alias. It was chosen for us. What is it? It's: "banana". Actually,
I have no idea what it is! But we can get it with `$queryBuilder->getRootAliases()[0]`.

*Now* it's just normal query logic: `$queryBuilder->andWhere()` passing `sprintf()`.
This looks a little weird: `%s.isPublished = :isPublished`, then pass `$rootAlias`
followed by `->setParameter('isPublished', true)`.

Cool! Spin over to try this thing!

```terminal-silent
symfony php bin/console phpunit --filter=testGetCollectionOfTreasures
```

Mission accomplished! It's just that easy.

## Query Extensions on SubResources?

By the way, will this also work for subresources? For example, over in our
docs, we can *also* fetch a collection of treasures by going to
`/api/users/{user_id}/treasures`. Will this *also* hide the unpublished treasures?
The answer is... *yes*! So, it's not something you need to worry about. I won't
show it, but this *also* uses the query extension.

Oh, and if you wanted admin users to be able to see unpublished treasures, you could
add logic to *only* modify this query if the current user is *not* an admin.

Next up: this query extension fixes the collection endpoint! But... someone could
*still* fetch a *single* unpublished treasure directly by its id. Let's fix that!
