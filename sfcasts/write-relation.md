# Writable Relation Fields

Open up `DragonTreasureResourceTest` and check out
`testPostToCreateTreasureWithLogin()`. We've talked a lot about making our
resources able to return relation fields. The main trick is simply to populate
those fields from inside our data mapper. Then API Platform handles transforming
them into IRIs.

One thing we *haven't* talked about is being able to *write* to one of these
relation fields.

## Writing to the owner Property

When we use this `post()` endpoint, we don't *need* to send an `owner` field.
That's because, nestled in `DragonTreasureApiToEntityMapper`, we have
code that says:

> If an `owner` is not sent in the JSON, automatically set it
> to the currently authenticated user.

*But*, you are *allowed* to send the `owner` property and set it to *yourself*.
Let's try that. Set `owner` to `'/api/users/'.$user->getId()`.

## How Relation Fields are Deserialized

When we do that, it *should* hit *this* part of our code. Battle stations! Run
`symfony php bin/phpunit` and execute *just* this test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

Perfect! It hits and dumps a `UserApi` object. *This* is *cool*. Actually,
dump the entire `$dto` so we can see things in more detail.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

Fantabulous. When we send this JSON data, the serializer *deserializes* all of
this into a `DragonTreasureApi` object. This string goes onto the `name` property,
*this* string goes onto the `description` property, and so on. Over here, we see
that: string... string... 1,000... and 5. *Super simple*.

But something special happens when the field you're sending is a *relation*,
meaning the property holds an object that is an `#[ApiResource]`. Specifically,
this IRI string is *transformed* into a `UserApi` object! But... how and *who*
does that? The *answer* is: a bit of team work between the serializer system and
the *state provider*.

Until now, as far as we know, the only time that the state provider is used is when
we *fetch* a resource... like if we fetch a user here or here, or if we `PATCH` or
`DELETE` a user. In all of those cases, API Platform leverages the user state provider
to find the one or many users.

*But* there's one *other* spot where a state provider is used: when someone sends
JSON that contains an IRI string on a relation field.

During the deserialization process, the serializer takes this IRI string, *sees*
that it's for a `UserApi` object, then it calls *its* state provider to load that.
Whatever that state provider returns will ultimately be set onto the `owner` property
of `DragonTreasureApi`. This magic has *always* been happening... but I just *love*
understanding the mechanics behind it. Nerd alert!

## Mapping the Relation Field

Anyway, in our mapper, our job is pretty straightforward. We know that `$dto->owner`
is a `UserApi` object. And what we *ultimately* need is a `User` entity. So, once
again, we'll use the mapping system to go from `UserApi` over to `User`. Up here,
inject a `MicroMapperInterface $microMapper`.

And below, say `$entity->setOwner()`... but use `$this->microMapper->map()` to
go from `$dto->owner` to `User::class`. And remember, any time we map a relationship,
we should add a `MAX_DEPTH` as well. Set `MicroMapperInterface::MAX_DEPTH` to `0`.

Using `0` is enough because that will cause our mapper to query for the `User` object...
it just won't continue and populate the individual property data from `UserApi` to
`User.` We would only need to do that if we were allowing `owner` to be an embedded
object, like creating a new one on the fly.... *or* if we were doing something crazy
like adding the `@id` to load a user... then modifying that user all at once. Crazy,
probably-not-realistic things that we talked about in previous tutorials.

And even if a user *did* try this right now, API Platform wouldn't allow it because
you can only *write* embedded data on a field if we've set up the serialization groups
for this.

Anyway, the only thing *we're* concerned about is making sure that we're loading
the correct `User` entity object. Run the test again and...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateTreasureWithLogin()
```

It's *good*! We are *now* allowed to *write* the `owner` field!

Next: Let's shift our focus to making the `dragonTreasures` field on `User` writable.
This is a relation field... but because it's a collection, it'll need an extra trick.
