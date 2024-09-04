# Quick! Create a DragonTreasure DTO

Time to convert our `DragonTreasure` ApiResource into a *proper* DTO class!
We'll start by deleting a *ton* of stuff: *everything* related to API Platform
in `DragonTreasure`... so we have a clean slate to start from. We'll add back
what we need little-by-little. Goodbye filter stuff... the validators... all the
serialization group stuff... and then we can do some cleanup on our properties.
We had some *fairly* complex code in here... and while we won't add *all* of it
back, we *will* add the most important things.

[[[ code('3d288c604d') ]]]

Lemme scroll down to make sure we got everything. Yea, that should be it! We
*now* have a good old-fashioned, *boring* entity class. In `src/ApiPlatform/`,
let's also delete `AdminGroupsContextBuilder`. This was a complex way to make
fields readable or writable by our admin... but we're going to solve that with
`ApiProperty` security. Also get rid of the custom normalizer... which added a
field and an extra group. And finally, remove the custom
`DragonTreasureStateProvider` and `DragonTreasureStateProcessor` classes.

## Query Extensions are Still Called!

But we *did* keep *one* thing: `DragonTreasureIsPublishedExtension`. Because the
new system will *still* use the core Doctrine `CollectionProvider`, this query
extension stuff will *continue* to work and be called. That's just one less
thing we need to worry about.

Head over and refresh the documentation. Ok! Only `Quest` and `User`. Though,
you may notice some `DragonTreasure` stuff down here... because `UserApi` has a
relation to the `DragonTreasure` entity. So even though `DragonTreasure` isn't an
API resource, API Platform still tries to document what that *field* is on `User`.
It doesn't really matter, because we're going to fix that and *completely* use
API classes everywhere

## Creating the DTO Class

In `src/ApiResource/`, create the new class: `DragonTreasureApi`. 

[[[ code('fae02bb812') ]]]

Next, in `UserApi`, steal some of the basic code from our `#[ApiResource]`... paste that over
here, and, for now, delete `operations`. We can also get rid of these `use`
statements. Perfect!

We *will* use a `shortName` - `Treasure` - give this `10` items per page, and remove
the `security` line. The *most* important thing is that we have `provider` and
`processor` (just as they are here), and `stateOptions`, which will point to
`DragonTreasure::class`.

[[[ code('03015f665a') ]]]

Also grab the `$id` property. Like before, we don't *really* want this to be
part of our API, so it's `readable: false` and `writable: false`. Down here, add
`public ?string $name = null`.

[[[ code('945876c9ce') ]]]

*Great* start! We have one tiny class and... what the heck, let's just go try it!
Refresh the docs. Yes! Our Treasure operations are here! If we try the collection
endpoint... we get:

> No mapper found for `DragonTreasure` -> `DragonTreasureApi`

## Adding the Mapper Class

That's fantastic! The only real work we need to do is implement those mappers.
So let's go!

In the `src/Mapper/` directory, create a class called
`DragonTreasureEntityToApiMapper`. We've done this before: implement `MapperInterface`
and add the `#[AsMapper()]` attribute. We're going `from: DragonTreasure::class`
`to: DragonTreasureApi::class`.

[[[ code('34af062ebb') ]]]

And *just* like that, micro mapper knows to use this. Generate the
two methods for the interface: `load()` and `populate()`. For sanity,
add `$entity = $from`, and `assert()` that `$entity` is an
`instanceof DragonTreasure`.

[[[ code('8f41e6a0b4') ]]]

Down here, create the DTO object with `$dto = new DragonTreasureApi()`. And remember,
the job of `load()` is to create the object *and* put an identifier on it if there
is one. So add `$dto->id = $entity->getId()`. Finally, `return $dto`.

[[[ code('0bd9435748') ]]]

For `populate()`, steal a few lines from above that set the `$entity` variable...
then also say `$dto = $to`, and add one more `assert()` that `$dto` is an
`instanceof DragonTreasureApi`.

[[[ code('a8335d940e') ]]]

The only property we have on our DTO right now is `name`, so all we need is
`$dto->name = $entity->getName()`. At the end, `return $dto`.

[[[ code('516aff71e1') ]]]

And, people! We just created a class that maps from the entity to the DTO...
and our state provider is using micro mapper internally... so I think this should...
just work!

And... it *does*! Wow! With *just* the API Resource class and this *one* mapper,
we now have a database-powered *custom* API Resource class. Woo!

## Adding A Relation Field

*Now* things get *interesting*. Every `DragonTreasure` entity has an *owner*, which
is a *relationship* to the `User` entity. In our API, we're going to have the same
relationship. But instead of this being a relation from `DragonTreasureApi` to a
`User` *entity* object, it will be to a `UserApi` object.

Check it out! Say `public ?UserApi $owner = null`.

[[[ code('b630e0c2fd') ]]]

Then let's go populate that in the mapper. Down here, say `$dto->owner =`... but...
hold on a second. This isn't as simple as saying `$entity->getOwner()`, because that's
a *user entity object*. We need a `UserApi` object! Can you think of anything
that's really good at converting a `User` entity to `UserApi`? That's right,
MicroMapper!

Up here on top, inject `private MicroMapperInterface $microMapper`...
and, down here, say `$dto->owner = $this->microMapper->map()` to map from
`$entity->getOwner()` - the `User` entity object - to `UserApi::class`.

[[[ code('5a99e8c76e') ]]]

How cool is that? One thing to be aware of is that if, in *your* system,
`$entity->getOwner()` might be `null`, you should code for that. Like, if you have
an owner, call the mapper, else just set `owner` to `null`... or don't set it at
all. For us, we're *always* going to have an owner, so this should be safe.

Let's try it! Refresh and... *oooh*. We have an `owner` field and it's
an IRI. Why *is* that showing up as an IRI? Because API Platform recognizes
that the `UserApi` object is an API *resource*. And how does it show API resources
that are relations? That's right! It sets them as an IRI. So that's *exactly* what
we wanted to see.

## Adding More Fields

Let's fill in the rest of the fields we need: I'll go through this super-fast. One
of the fields I'm adding is `$shortDescription`. That was a *custom* field before...
but it'll be simpler now. Another custom field we had was `$isMine`, which will
*also* just be a normal property.

[[[ code('c6689df4de') ]]]

Over in our mapper, let's set everything. I'll speed through the
boring parts. But `$shortDescription` *is* a bit interesting. Before, in
`DragonTreasure`, we had a `getShortDescription()` method and *that* was exposed
directly as the API field.

With the new setup, it's a normal property like anything else, and we handle setting
the custom data in our mapper: `$shortDescription` is equal to
`$entity->getShortDescription()`. Finally, for `$dto->isMine`, temporarily
hardcode that to `true`.

[[[ code('45559ed8cd') ]]]

Let's check it! Refresh and... that's *beautiful*!

In `tests/Functional/`, we have `DragonTreasureResourceTest`. In *here*, we have
`testGetCollectionOfTreasures()`, which tests to make sure that we only see
*published* items. If our query extension is still working, this will pass. This
also checks to make sure we see the correct keys.

Let's see if this works:

```terminal
symfony php bin/phpunit --filter=testGetCollectionOfTreasures
```

It *does*. Mind blown.

## Populating the Weird isMine Field

Before we finish, let's fix the hard coded `true` on `isMine`. This is easy, but
shows off just how nice it is to work with custom fields. In our mapper, this
is a *service*, so we can inject *other* services like the `$security` service.
*Then*, we can populate that with whatever data we want. So `isMine`
is true if `$this->security->getUser()` equals the `DragonTreasure`, `getOwner()`
(which is a `User` entity object).

[[[ code('3552c6a23a') ]]]

Try the test one more time to make sure this is working, and... it *is*. Woo!

Next: I want to dive deeper into relationships in our DTO-powered API.
Because, if you're not careful, we can get the dreaded infinite recursion!
