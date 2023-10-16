# Writable Relation Fields

Open up `DragonTreasureResourceTest` and check out
`testPostToCreateTreasureWithLogin()`. We've already talked a lot about making our
resources able to return relation fields. And the main trick was simply to populate
those fields from inside our data mapper.

One thing we *haven't* talked about is being able to *write* to one of these
relationships.

## Writing to the owner Property

When we use this `post()` endpoint, we don't *need* to send an `owner` field.
That's because, in our `DragonTreasureApiToEntityMapper`, we have some logic that
says

> Hey! If an `owner` is not sent in the JSON, automatically set the owner and
> to the currently authenticated user.

*But*, you are *allowed* to send the `owner` property and set it to *yourself*. Let's
try that. Set `owner` to `'/api/users/'.$user->getId()`.

## How Relation Fields are Deserialized

When we do that, it *should* hit *this* part of our test. Let's test it out!
At your terminal, run `symfony php bin/phpunit` then run *just* this test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

And... perfect! It *hits* and dumps a `UserApi` object. This is *really* important.
Let's dump the entire `$dto` so we can see things in more detail.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

Okay, *awesome*. When we send this JSON data, the serializer *deserializes* all of
this into a `DragonTreasureApi` object. This string goes onto the `name` property,
*this* string goes onto the `description` property, and so on. Over here, we see
that... string... string... 1,000... and 5. *Super simple*.

But something special happens when the field you're sending is a *relationship*,
meaning the property is an object that is *also* an `#[ApiResource]`. Specifically,
this IRI string is *transformed* into the `UserApi` object! How and *who* does that?
The *answer* is: a bit of team work between the serializer system and the *state
provider*.

Until now, the only time that the state provider is used (as far as we know) is when
we *fetch* a resource... like if we fetch a user here or here, or if we `PATCH` or
`DELETE` a user. In all of those cases, it will leverage the user state provider
to find the one or many users.

*But* there's one *other* spot where a state provider is used: when someone POST,
PATCHes or PUTs some JSON that contains an IRI on a relationship field.

During the deserialization process, the serializer takes this IRI string, *sees*
that it's for a `UserApi` object, then it calls its state provider to load that.
Whatever our state provider returns will ultimately be set onto the `owner` property
of `DragonTreasureApi`. This magic has *always* been happening... and I just *love*
understanding the mechanics behind it. Nerd alert!

## Mapping the Relation Field

Anyway, in our mapper, our job is pretty straightforward. We know that `$dto->owner`
is a `UserApi` object. And what we *ultimately* need is a `User` entity. So, once
again, we'll use the mapping system to go from `UserApi` over to `User`. Up here,
inject a `MicroMapperInterface $microMapper`.

And down here, say `$entity->setOwner()`... but use `$this->microMapper->map()` to
go from `$dto->owner` to `User::class`. And remember, any time we map a relationship,
we should add a `MAX_DEPTH` as well. Set `MicroMapperInterface::MAX_DEPTH` to `0`.

Using `0` is enough because that will cause our mapper to query for the `User` object...
and we don't need to map any specific fields from `UserApi` to `User.` We would only
need to do that if we were allowing `owner` to be an embedded object, like creating
a new one on the fly.... *or* if we were doing something crazy like adding the
`@id` to load a user... then modifying that user on the fly. Crazy,
probably-not-realistic things that we talked about in previous tutorials.

And even if a user *did* try this, API Platform wouldn't allow it because you can
only *write* embedded data on a field if you have the serialization groups set up
correctly.

Anyway, the only thing we're concerned about is making sure that we're loading the
correct `User` entity object. Run the test again and...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

It's *good*! We are *now* allowed to *write* the `owner` field.

Next: Let's shift our focus to making the `dragonTreasures` field on `User` writable.
This is a relation field... but because it's a collection, it'll need an extra trick
