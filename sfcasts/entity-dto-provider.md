# Entities, DTO's & The "Central" Object

This entity class thing seems almost too good to be true. It gives us all the
flexibility, in theory, of a custom class, while reusing all the core Doctrine
provider and processor logic. But hold your horses because there are *two*, albeit
*fixable*, snags.

Most importantly, we're not allowed to have custom property names. This will cause
an error when it tries to serialize. Second, I haven't mentioned it yet, but
*write* operations - like `POST` or `PATCH` - don't work at all. Well... if we,
*posted* to our endpoint, the data *would* be deserialized... but it wouldn't be
saved to the database.

## The Problem with Write Operations

We can try this because we already have a test for it. Open `UserResourceTest`
and, down here, copy `testPostToCreateUser()`. Spin over and run that with:

```terminal
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... 400 error! Open that up. Uh oh:

> Unable to generate an IRI for the item of type `App\ApiResource\UserApi`.

Here's what happens. The serializer *deserializes* this JSON into a `UserApi` object.
Yay! That `UserApi` object is *then* passed to the core Doctrine *persist* processor:
the thing that normally saves entities to the database. But because `UserApi` is
*not* an entity, that processor does... *nothing*. *Then*, when `UserApi` is
serialized back to JSON, the `$id` is *still* null - because nothing was ever saved
to the database - and... so the IRI can't be generated for it.

We *could* fix this by creating a custom state processor for `UserApi` that saves
this to the database. But even if we *did*, the write operations, like `POST` and
`PATCH`, just aren't designed to work out of the box with this `entityClass` solution.
The reason... is a bit technical, but important.

## Understanding the "Central Object" for an Operation

Internally, for every API request, API Platform has a *central* object that it's
working on. If we fetch a *single* item, that central object *is* that single
item. And that's really important. It's used in various places, like the `security`
attribute: when we use `is_granted`, the `object` variable will be that "central"
object. For example, if we make a `Patch()` request, that means we're editing a dragon
treasure... so the central object will be a `DragonTreasure` entity. Easy peasy!

What's the catch? Well, when you use the `entityClass` solution with a *read*
operation (so, one of these `GET` requests), the central object will be the
*entity*. So the `User` entity will be the central object. But with a *write*
operation (most importantly, the `POST` operation to create a new user), that central
object will suddenly be a `UserApi` object. That causes some *serious* inconsistency:
the central object will sometimes be an entity... and other times the DTO. Good
luck making a `security` system that works with both of those... and isn't
completely confusing.

Also, when the `User` *entity* is the central object, *that's* when we run into
the problem that prevents us from having custom fields on our DTO.

So, if we could make the `UserApi` be the central object in *all* cases, *then*
we'd have consistent security... and we could *also* fix our big custom
properties problem.

How can we pull that off? By writing a custom state provider that *returns*
`UserApi` objects. Think about it: because the core Doctrine collection provider
returns `User` *entity* objects, *those* become the central objects. If we, instead,
return `UserDto` objects, problem solved. If this doesn't all make sense yet, I'm
not surprised. Let's walk through this step-by-step.

## Decorating the Core State Provider

Start by running:

```terminal
php bin/console make:state-provider
```

Call it `EntityToDtoStateProvider`. My goal is to create a *generic* state provider
that will work for *all* cases where we have an API resource class that pulls data
from an entity. So, we'll mostly keep user-specific code out of here.

[[[ code('a6aeac9b54') ]]]

Over in `UserApi`, set `provider` to `EntityToDtoStateProvider`.

[[[ code('b452854c45') ]]]

Ok! In `EntityToDtoStateProvider`, we could *manually* query for our `User`
entity objects, turn those into `UserApi` objects... then return them. *But* that's
the whole thing we're trying to *avoid*! We want to continue to reuse all of that
nice Doctrine query logic: that's the beauty of `stateOptions`.

To do that, like we've done before, we're going to *decorate* the core Doctrine
provider. Say `public function __construct()` with
`private ProviderInterface $collectionProvider`. And to help Symfony know which to
pass in, use the `#[Autowire()]` attribute and say `service: CollectionProvider`
(make sure you get the one from Doctrine ORM), followed by `::class`.

[[[ code('b452854c45') ]]]

Down here, add `$entities = $this->collectionProvider->provide()`, passing
`$operation`, `$uriVariables`, and `$context`. Below, `dd($entities)`

[[[ code('cd465e6798') ]]]

Let's see what happens! Head back over, refresh the endpoint, and... *got it*! We
*are* calling the core provider, and it's returning a *paginator* object. To see
what's hiding *inside* that `Paginator`, say `dd(iterator_to_array($entities))`.

[[[ code('610b7a07dc') ]]]

Back over here... this show *five* `User` entity objects.

At this point, our new provider isn't doing... *anything* special. If we returned
`$entities`, we'd be *exactly* where we started: with `User` entities as the
central object. Our goal is to return `UserApi` objects... and we're going to do
that *next*.
