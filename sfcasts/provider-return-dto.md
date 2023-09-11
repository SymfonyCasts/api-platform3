# Provider: Transforming Entities to DTOs

Let's keep track of the goal: when we first used `stateOptions`, it triggered the
core Doctrine collection provider to be used. That's *great*... except that it
returns `User` *entities*, meaning that the `User` entities became the *central*
objects internally for the `UserApi` endpoints. That causes a serious limitation
when serializing: our `UserApi` properties need to match our `User` properties...
otherwise the serializer explodes.

To fix that and give us *full* control, we're created our own state provider that
calls the core collection provider. But instead of returning these `User` entity
objects, we're going to return `UserApi` objects so that *they* become the *central*
objects and serialize *normally*.

## Mapping to the DTO

So, *our* job is simple: loop over these `User` entity objects and convert them to
`UserApi` objects. This is.... kind of boring. But that's also kine of beautiful
Create a `$dtos` array and then `foreach` over `$entities as $entity`. *Then* 
add to the `$dtos` array by calling a new method: `mapEntityToDto($entity)`.

Hit "alt" + "enter" to add that method at the bottom. This will return an `object`.
Well, in this case, it will be a `UserApi` object... but we're trying to keep this
class generic. I'll paste on some logic - you can copy this from the code block on
this page - then hit "alt" + "enter" to add the missing `use` statement. This code
*is* user-specific... but we'll make it more generic later so this class can be
used for dragon treasures.

But isn't this refreshingly boring and understandable code? Just transferring
properties from the `User` $entity... onto the DTO. The only thing that's *kind of*
fancy is where we change this collection to an array... because this proeprty is
an `array` on `UserApi`.

*That's it*. Finally, at the bottom, `return $dtos`.

Thanks to this, the central objects will be `UserApi` objects... and *these* will
be serialized normally... without any fanciness of trying to serializer from a
`User` entity onto a `UserApi`.

Moment of truth. It works... with the same result as before! But *now* we have the
power to add custom properties.

## Adding Custom Properties

Add the `public int $flameThrowingDistance` back. Then, in the provider, *this* is
where we have an opportunity to set those custom properties, like
`$dto->flameThrowingDistance = rand(1, 10)`.

And... *voilà*! We are so freakin' dangerous right now! we're reusing the core
Doctrine `CollectionProvider`, but with the ability to add custom fields. Oh! And
I forgot to mention: our JSON-LD fields `@id` and `@type` are *back*. We did it!

## Fixing Pagination

Though, we're now missing *pagination*. We can see our filter stuff is documented...
but the `hydra:view` field that documents the pagination I gone! Ok, in reality,
pagination *does* still work. Watch: if I go to `?page=2`, the first "user 1" user...
becomes *"user 6"*. Yup, internally, the core `CollectionProvider` from Doctrine is
*still* reading the current page and querying for the correct set of objects *for*
that page. We're missing the `hdra:view` field at the bottom that *describes* the
pagination simple because we're no longer returning an object that implements
`PaginationInterface`.

Remember, this `$entities` variable is actually a `Pagination` object. Now that we're
just returning an array, it makes API Platform *think* that we don't support
pagination.

The solution is dead-simple. Instead of returning `$dtos`,
`return new TraversablePaginator()` with a new `\ArrayIterator()` of `$dtos`.
For the other arguments, we can grab those from the original paginator. To help,
`assert($entities instanceof Paginator)` (the one from Doctrine ORM). Then, down
here, usex `$entities->getCurrentPage()`, `$entities->getItemsPerPage()`, and
`$entities->getTotalItems()`.

The core collection provider already did all that hard work for us.

When we refresh... the results, of course, don't change. But down here,
`hydra:view` is back!

Next: Let's get this working for our item operations, like `GET` one or `PATCH`.
We;ll also leverage our new system to readd something to `UserApi` that we
*previously* had. But this time... we're going to do it in a much cooler way.
