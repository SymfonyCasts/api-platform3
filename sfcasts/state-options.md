# stateOptions + entityClass Magic

When we create a non-entity API resource, *we're* responsible for loading and saving
the data. What's *frustrating* is that, if we make a custom state provider for
`UserApi`, it will do the *exact* same thing as the core Doctrine state
provider: query the database. It's a *bummer* to reinvent all of that logic ourselves.
This, historically, has been the Achille's heel of DTO's.

## Checking out the Core CollectionProvider

Crack open the core `CollectionProvider` from Doctrine ORM. If you ever wanted to see
what the `CollectionProvider` looks like, *here* it is! It's more complex than I
imagined. It creates the `QueryBuilder`, calls `handleLinks()` (which intelligently
joins to other tables based on the data you need), and houses the query extension
system. In the last tutorial, we created a query extension for `DragonTreasure` so
it would only return *published* items. And *part* of that extension system, though
we can't see it here, is where pagination and filtering is added.

*So*, this class gives us a *lot*... and I want to reuse it. So, darn it, let's
yolo this thing and try to!

## Trying to use the CollectionProvider

Head over to `UserApi`, say `provider`, and point to `CollectionProvider`
(the one from Doctrine ORM).

Let's see what happens! At the browser, go to the endpoint *directly* -
`/api/users.jsonld`. And... we get an *error*:

> Call to a member function `getRepository()` on null.

Coming from the core `CollectionProvider`. Boo. But not surprising. Our `UserApi`
isn't an entity... and so when it tries to figure out how to query for it, explosions!

## Hello stateOptions + entityClas

But *psst*... want to hear a secret? There *is* a way we can hint to the provider
that data for this class should come from the `User` entity. It looks like this:
`stateOptions` set it to a `new Options` object (making sure to grab the one from
ORM), and inside, `entityClass: User::class`.

Let's see what happens now! When we head over and refresh... whoa! It looks like
that worked! We see "totalItems: 11"... with items 1-11 all right here. We only have
an `$id` property, but I guess that makes sense... since we only have an `$id`
property inside our `UserApi`.

Let's add a few more properties! How about `public ?string $email = null`
and `public ?string $username = null`. Both of these properties also live in
our `User` entity.

When we refresh... those pop up too! This is *working*.... but how? What the heck
is going on?

## How this all Works

If we could peek under API Platform's hood, we would see that the underlying
API resource objects *are* `UserApi`. So what we're seeing here *is* the JSON for
a collection of `UserApi` objects.

*But* there are *several* places in the system that look for `stateOptions` and,
if it's present, will use the `entityClass` from that. The `CollectionProvider` we
opened a moment ago - the one from Doctrine ORM - is one of those cases. It grabs
the `entityClass` from `stateOptions` if there is one... then uses that when it does
the query.

In fact, as soon as we have this `stateOptions` + `entityClass` thing, API Platform
sets the provider and the processor *automatically* to the core Doctrine ones.
So we don't even *need* to have the `provider` key: it's set for us.

Okay, but if the provider is querying for `User` *entity* objects, *how* and *when*
is that converted to `UserApi` objects... so that they can be serialized to JSON?
The *answer* is *during* serialization... and it's a bid odd. Thanks to
`stateOptions`, API Platform is actually serializing the `User` *entity* object.
But to get the list of the properties that it should serialize, it reads the
metadata from `UserApi`. Then, it grabs the property values *from* `User`...
and puts them onto a `UserApi` instance. Essentially, it serializes the `User` entity
*into* a `UserApi` object... and *then* to JSON.

This seems to work well... but with one, major limitation.

## Limitation: No Custom Properties

Add a property that is *not* on our entity, like
`public int $flameThrowingDistance = 0`. There is *no* `$flameThrowingDistance`
property over on `User`.

When we try this... *explosion*! If we scroll down a bit, we see that this comes
from the *normalizer* system... which is part of the serializer. It
looks at `UserApi`, thinks "Oh, I need a `$flameThrowingDistance` field",
tries to fetch that from `User`, and, since it's not there, boom!

So the colossal, monstrous, titanic limitation of the `entityClass` strategy is...
we *can't* have extra fields on our `UserApi` class. But no worries: we'll find a
path around this in the next chapter. For now, remove the extra property.

Oh, and one *other* limitation that you may have noticed is that we don't have the
JSON-LD fields `@id` or `@type`. We'll handle that while we're fixing the issue with
custom fields... like the multitasking wizards we are.

## Adding a Relation Property

Let's add another property: `public array $dragonTreasures = []`? We *do*
have a `$dragonTreasures` property over on `User` that holds a collection of
`DragonTreasure` objects.

So if we go over and test this out... it works fine! Though, *surprisingly*,
it's *embedding* the `dragonTreasures` instead of returning them as IRIs. This is
the same problem we saw earlier, and the fix is the same.

I *do* want to point out one interesting thing about this, though. When it embeds
the `dragonTreasures`, one of the properties is `owner`. Right now, that *owner*
is actually the `User` entity. Since the `User` *entity* is no longer an API resource,
it embeds it and uses the random `genid` thing.

I'll talk about this more in a bit, but once we start creating DTOs and using *those*
instead of entities, we'll probably want to use DTOs for *all* of our API resources...
instead of mixing entities and DTOs... because it creates issues like this.

Anyway, fix this by advertising that this is an `array` of `DragonTreasure`. I'm
using a slightly different array syntax there, but it doesn't really matter.

If we try this again... back to IRIs! Woo!

## Built-in Pagination

So far, we know that `stateOptions` does *three* things. One: It automatically sets
the provider and processor to use the core Doctrine provider and processor. Two:
the provider is smart enough to query from this entity. This also works for
*single* items, like `/users/1.jsonld`. And three: The serializer
*serializes* the `User` entity *into* a `UserApi` object.

The fact that `stateOptions` causes the core Doctrine state provider to be used has
some *very* important other side effects. First, we get pagination *for free*. Add
`paginationItemsPerPage: 5`, go over, and refresh. We see that the total number of
items is "11"... but it only shows *five*... and the pages are down here.

Second, the collection provider also makes the query extension system work. We don't
have any query extensions for `User`, but we *do* have one for `DragonTreasure`.
Later on, when we convert `DragonTreasure` to its own DTO class, this extension is
*still* going to work.

The third and final goodie is that the *filter* system still works! Watch:
above `UserApi`, add `#[ApiFilter()]` with `SearchFilter::class` and `properties:`
with `username` set to `partial`.

Go back and look at the documentation... *whoops*. I autocompleted the
`SearchFilter` from ODM. Delete that, then I'll hit Alt+Enter to grab the one
from `ORM`.

Refresh the docs again... and look at the `/api/users` endpoint. It *is* advertising
that there's a `username` filter, and it *is* going to work! In the other tab,
add `?username=Clumsy`.

And... yes! It only returns those 5 results! So the filter system works!
Though, one thing to note is that, when we say `username`, we're referring to the
`$username` property on the `User` *entity*. As far as the filter is concerned, we
don't even *need* a `username` in `UserApi`.

So: we're reusing all of this core Doctrine provider logic, we have pagination,
filters and.... it's the best thing since ice cream sandwiches. Except... for that
big, scary limitation: that our DTO can't have custom fields. And... that's really
the whole point of a DTO: to gain the flexibility of having different fields than
your entity. So let's see how to fix that limitation *next*.
