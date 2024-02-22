# Provider: Transforming Entities to DTOs

Let's keep track of the goal. When we first used `stateOptions`, it triggered the
core Doctrine collection provider to be used. That's *great*... except that it
returns `User` *entities*, meaning that *those* became the *central* objects
for the `UserApi` endpoints. That causes a serious limitation when serializing:
our `UserApi` properties need to match our `User` properties... otherwise the
serializer explodes.

To fix that and give us *full* control, we've created our own state provider that
calls the core collection provider. But instead of returning these `User` entity
objects, we're going to return `UserApi` objects so that *they* become the *central*
objects and serialize *normally*.

## Mapping to the DTO

Create a `$dtos` array and `foreach` over `$entities as $entity`. *Then* 
add to the `$dtos` array by calling a new method: `mapEntityToDto($entity)`.

[[[ code('610b7a07dc') ]]]

Hit "alt" + "enter" to add that method at the bottom. This will return an `object`.
Well... it will be a `UserApi` object... but we're trying to keep this
class generic. I'll paste in some logic - you can copy this from the code block on
this page - then hit "alt" + "enter" to add the missing `use` statement. This code
*is* user-specific... but we'll make it more generic later, so we can reuse this
class for dragon treasures.

[[[ code('b5a06d6bc9') ]]]

But isn't this refreshingly boring and understandable code? Just transferring
properties from the `User` `$entity`... onto the DTO. The only thing that's
*kind of* fancy is where we change this collection to an array... because this
property is an `array` on `UserApi`.

Finally, at the bottom of `provide()`, `return $dtos`.

[[[ code('9a93af1586') ]]]

Thanks to this, the central objects will be `UserApi` objects... and *these* will
be serialized normally: no fanciness where the serializer tries to go from a
`User` entity into a `UserApi`.

Drumoll please! Tada! It works... with the same result as before! But *now* we have
the power to add custom properties.

## Adding Custom Properties

Add back the `public int $flameThrowingDistance`. 

[[[ code('046822b3f9') ]]]

Then, in the provider, *this* is
where we have an opportunity to set those custom properties, like
`$dto->flameThrowingDistance = rand(1, 10)`.

[[[ code('87506a00cb') ]]]

And... *voilà*! We are so freakin' dangerous right now! We're reusing the core
Doctrine `CollectionProvider`, but with the ability to add custom fields. Oh! And
I forgot to mention: the JSON-LD fields `@id` and `@type` are *back*. We did it!

## Fixing Pagination

Though, it looks like we're now missing *pagination*. The filter is documented...
but the `hydra:view` field that documents the pagination is gone! Ok, really,
pagination *does* still work. Watch: if I go to `?page=2`, the first "user 1" user...
becomes *"user 6"*. Yup, internally, the core `CollectionProvider` from Doctrine is
*still* reading the current page and querying for the correct set of objects *for*
that page. We're missing the `hdra:view` field at the bottom that *describes* the
pagination simply because we're no longer returning an object that implements
`PaginationInterface`.

Remember, this `$entities` variable is actually a `Pagination` object. Now that we're
just returning an array, it makes API Platform *think* that we don't support
pagination.

The solution is dead-simple. Instead of returning `$dtos`,
`return new TraversablePaginator()` with a new `\ArrayIterator()` of `$dtos`.
For the other arguments, we can grab those from the original paginator. To help,
`assert($entities instanceof Paginator)` (the one from Doctrine ORM). Then, down
here, use `$entities->getCurrentPage()`, `$entities->getItemsPerPage()`, and
`$entities->getTotalItems()`.

[[[ code('bdbdef05a3') ]]]

The core collection provider already did all that hard work for us. What a pal.
Refresh now. The results don't change... but down here, `hydra:view` is back!

Next: Let's get this working for our item operations, like `GET` one or `PATCH`.
We'll also leverage our new system to add something to `UserApi` that we
*previously* had.... but this time, we're going to do it in a much cooler way.
