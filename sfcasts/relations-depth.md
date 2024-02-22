# Dtos, Mapping & Max Depth of Relations

Head to `/api/users.jsonld` to see... a circular reference
coming from the serializer. Yikes! Let's think: API Platform serializes
whatever we return from the state provider. So head there.... and find where
the collection is created. Dump the DTOs. These are what's being serialized, so
the problem must be here.

Refresh and... no surprise: we see 5 `UserApi` objects. Ah, but *here's* the problem:
the `dragonTreasures` field holds an array of `DragonTreasure` *entity* objects...
and each has an `owner` that points to a `User` entity... and that points *back*
to a collection of `DragonTreasure` entities... which causes the serializer to
serializer forever and ever. But that's not even the *real* problem! I know, I'm
full of good news. The real problem is that the `UserApi` object should *really*
relate to a `DragonTreasureApi`, not a `DragonTreasure` entity.

Over in `UserApi`, this will now be an `array` of `DragonTreasureApi`. Once we start
going the DTO route, for maximum smoothness, we should relate DTOs to other DTOs...
instead of mixing them with entities.

To populate the DTO objects, go to the mapper: `UserEntityToApiMapper`. Down here,
for `dragonTreasures`, we can't do *this* anymore because that will give us
`DragonTreasure` *entity* objects. What we basically want to do is convert *from*
`DragonTreasure` to `DragonTreasureApi`. And so, once again, it's micro mapper to
the rescue!

## Micro-Mapping DragonTreasure -> DragonTreasureApi

Add `public function __construct()` with `private MicroMapperInterface $microMapper`.
Down here, add some *fancy* code: `$dto->dragonTreasures =` set to `array_map()`,
with a function that has a `DragonTreasure` argument. We'll finish that in a second...
but first pass the array that it will loop over:
`$entity->getPublishedDragonTreasures()->toArray()`.

So: we get an array of the published `DragonTreasure` objects and
PHP loops over them and calls our function for each one - passing the
`DragonTreasure`. Whatever we return will become an item inside
a new array that's set onto `dragonTreasures`. And what we want to return
is a `DragonTreasureApi` object. Do that with
`$this->microMapper->map($dragonTreasure, DragonTreasureApi::class)`.

## Circular Relationships

Cool! When we refresh to try it... we're greeted with a *different* circular
reference problem. Fun! This one comes from MicroMapper... and it's a problem
that will happen whenever you have relationships that refer to each other.

Think about it: we ask Micro Mapper to convert a `DragonTreasure` entity to
`DragonTreasureApi`. *Simple.* To do that, it uses our mapper. And guess what? In
our mapper, we ask it to convert the `owner` - a `User` entity - to an instance of
`UserApi`. To do that, micro mapper goes back to `UserEntityToApiMapper` and...
the process repeats. We're in a loop: to convert a `User` entity, we need to convert
a `DragonTreasure` entity... which means we need to convert its `owner`... which
is that same `User` entity.

## Setting Mapping Depth

The fix lives in your mapper, when calling the `map()` function. Pass
a *third* argument, which is a "context"... kind of an array of options. You
can pass whatever you want, but Micro Mapper itself only has 1 option that
it cares about. Set `MicroMapperInterface::MAX_DEPTH` to 1. 

Let's see what that does. When we refresh... look at the dump, which comes
from the state provider. It maps the `User` entities to `UserApi` objects... and
we see 5. We can *also* see that the `dragonTreasures` property *is* populated with
`DragonTreasureApi` objects. So it *did* do the mapping from `DragonTreasure` to
`DragonTreasureApi`. But when it went to map the `owner` of that `DragonTreasure`
to a `UserApi`, it's there... but it's *empty*. It's a *shallow* mapping.

When we pass `MAX_DEPTH => 1`, we're saying:

> Yo! I want you to fully map this `DragonTreasure` entity to `DragonTreasureApi`.
> That is depth 1. But if the micro mapper is called again to map any *deeper*,
> skip that.

Well, not exactly *skip*. When the mapper is called the 2nd time to map the
`User` entity to `UserApi`, it calls the `load()` method on that mapper... but
*not* `populate()`. So we end up with a `UserApi` object with an `id`... but nothing
else. That fixes our circular loop. And, we don't really care that the `owner`
property is an empty object... because our JSON never renders that deeply!

Watch. Remove the `dd()` so we can see the results. And... perfect! The result is
*exactly* what we expect! For `DragonTreasures`, we're only showing the IRI.

So, as a rule, when calling micro mapper from inside a mapper class, you'll probably
want to set `MAX_DEPTH` to `1`. Heck, we *could* set `MAX_DEPTH` to `0`! Though
the only reason to do that would be a *slight* performance improvement.

This time, when we map `$dragonTreasure` to `DragonTreasureApi`,
try `MAX_DEPTH => 0`. This will cause the depth to be hit *immediately*. When it
goes to map the `DragonTreasure` entity to `DragonTreasureApi`, it will use the
mapper, but *only* call the `load()` method. The `populate()` method will *never*
be called. Put the `dd()` back. What we end up with is a *shallow* object
for `DragonTreasureApi`.

This might seem weird, but it's *technically* okay... because this `dragonTreasures`
array is going to be rendered as IRI strings... and the only thing API Platform
needs to build that IRI is... the `id`! Check it out! Remove the dump and reload
the page. It looks *exactly* the same. We just saved ourselves a tiny bit of work.

So, to be on the safe side - in case you embed the object - use `MAX_DEPTH => 1`.
But if you know that you're using IRIs, you *can* set `MAX_DEPTH` to `0`.

Over here, let's do the *same* thing: `MicroMapperInterface::MAX_DEPTH` set to
0 because we know that we're only showing the IRI here as well.

## Forcing a JSON Array

One *other* thing you may have noticed is that `dragonTreasures` suddenly looks like
an *object* - with its squiggly braces instead of square brackets. Well, in PHP
it *is* an array - `array_map` returns an array with the `0` key set to something
and the `2` key to set to something. But because of the missing `1` key, when it's
serialized to JSON it looks like an *associative* array, or an "object" in JSON.

If we change the `toArray()` to `getValues()` and refresh the page... perfect! We're
back to a regular array of items.

Next: We can *read* from our new `DragonTreasureApi` resource, but we can't *write* to
it yet. Let's create a `DragonTreasureApiToEntityMapper` and re-add things like
security and validation.
