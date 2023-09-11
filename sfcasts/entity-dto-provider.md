# Entity Dto Provider

This entity class thing seems almost too good to be true. It gives us all of the flexibility, in theory, of a custom class. We can reuse all of the core Doctrine provider and processor stuff but, as we've seen, there are *two*, albeit *fixable*, catches.

First of all, right now, we're not allowed to have custom property names. That will cause an error when it tries to serialize. Second, *write* operations like `POST` or `PATCH` don't work at all. Well... if we, for example, *posted* a file to our endpoint, the data *would* be deserialized, but it wouldn't be saved to the database. We can try this because we already have a test set up. Open `UserResourceTest.php` and, down here, copy this `testPostToCreateUser()` Over here, we'll run that with

```terminal
symfony php bin/phpunit --filter=
```

and *paste*. This returns a 400 error, and if we open that up, we get this:

`Unable to generate an IRI for the item of type \"App\\ApiResource\\UserApi\"`.

Behind the scenes, the serializer *deserializes* this into a `UserApi` object. That `UserApi` object is *then* passed to the core Doctrine *persist* processor - the thing that normally saves entities to the database. But because `UserApi` is *not* an entity, that processor does *nothing*. *Then*, when `UserApi` is serialized back to JSON, the `$id` is *still* null because nothing was ever saved to the database. *Therefore*, the IRI can't be generated for it.

We *could* fix this by creating a custom state processor for `UserApi` that saves this to the database, but even if we *did*, the right operations, like `POST` and `PATCH`, just aren't designed to work out of the box with this entity class solution. The reason for that is a bit technical, but *really* important.

Internally, API Platform has a *central* object that it's working on. If we're fetching a *single* item, that central object *is* that single item. And that's really important. It's used, for example, in the `security` attribute, where we have `is_granted`, and this `object` here is going to be that central object. It makes sense because, if we're making a `Patch()` request, that means we're editing a dragon treasure, so the central object is going to be a `DragonTreasure` entity. And this concept of a central object is used in other places as well.

So what's the catch? Well, when you're using the `entityClass` solution with a *read* operation (so, one of these `GET` requests), the central object will *always* be the entity. So the `User` entity will be the central object, but with a *write* operation (most importantly, the `POST` operation to create a new user), that central object will suddenly be a `UserApi` object. So there's some *serious* inconsistency with where the central object, in some cases, is the *entity*, and in other cases, is your DTO. That's going to make things like setting up those security systems *very* difficult to do. It's also at the *heart* of all of the problems that we're talking about. If we can make the `UserApi` be the central object in *all* cases, *then* we'd have consistent security. That would also fix the problem of having custom properties. How can we make the `UserApi` the *consistent* central object? By writing a custom state provider that *returns* the `UserApi`.

Think about it. Right now, we know that the state provider for our `UserApi` class is the Doctrine collection provider. When the Doctrine collection provider finishes its job, it returns a `User` entity that *then* becomes the central object. We're going to extend the state provider and have it do the same thing, but return our *DTO* instead. This probably doesn't make a ton of sense yet, and that's okay. We're putting the puzzle together piece by piece until we can see the big picture at the end.

Check this out. Run

```terminal
./bin/console make:state-provider
```

and we're going to call this

```terminal
EntityToDtoStateProvider
```

We're creating a generic state provider that's going to work for *all* of the cases where we have API resource classes that represent an entity. This won't be specific to users. We're going to keep this *nice* and generic.

Here's our class, and we're going to use this later for `DragonTreasure`. Now, over in `UserApi.php`, we're going to set `provider` to `EntityToDtoStateProvider`. A moment ago, this was using the core Doctrine provider. *Now* it's using *our* provider. Of course, in `EntityToDtoStateProvider`, we could *manually* query for our `User` entity objects, turn those into `UserApi`s and *return* them. *But* that's the whole thing we're trying to *avoid*. We want to continue to reuse all of that nice Doctrine query logic, and that's really the beauty of the `stateOptions`.

To do that, just like before, we're going to decorate the core Doctrine provider. Say `public function __construct()` with `private ProviderInterface $collectionProvider`. And to help Symfony know which one to pass in, we'll use the `#[Autowire()]` attribute and say `service: CollectionProvider` (make sure you get the one from Doctrine ORM), followed by `::class`.

We're now passing in the core collection provider so, down here, say `$entities = $this->collectionProvider->provide()`, passing `$operation`, `$UriVariables`, and `$context` so that we're just calling the internal one, and below, `dd($entities)`.

Okay, head back over, refresh our endpoint, and... *got it*! We *are* calling the core provider, and it's returning a *paginator* object. That's not surprising, and if we want to see what's *inside* of that paginator object, we can say `dd(iterator_to_array($entities))`, which will loop over that *for* us. Over here... this gave us *five* `User` entity objects.

At this point, our new provider isn't doing anything special. We're still calling the core `CollectionProvider`. And if we were returning entities here, we'd pretty much be right back where we started, returning `User` entities just like our collection provider was doing. *Our* goal is to return `UserApi` objects, and we're going to do that *next*.

