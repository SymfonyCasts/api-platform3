# Other Conditional Field Strategies

Let's keep playing with how we can hide or show fields. Remove the `#[ApiProperty]`.
Then, on top, set the `normalizationContext` option. We used this in previous
tutorials... but this time, instead of `groups`, we're going to set a key called
`AbstractNormalizer::IGNORED_ATTRIBUTES` and *then* set to an array. Inside, put
`flameThrowingDistance`.

Whether a field is readable or writable really comes down to the serializer. This
tells the serializer:

> Yo! When you're normalizing - so going *to* JSON - ignore this property.

This should make it *writable*, but not *readable*. When we try it...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

That's exactly what happens! To make it *not* writable, do the same thing with
`denormalizationContext`. Copy that, put a "de" on the front of it, and now when
we try it:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Yup! `flameThrowingDistance` is "1" - so it is *not* writable, and down here...
it's not readable either. *Sweet*.

So this is just a different option that should work the same as `ApiProperty`...
though I *have* seen complex cases where this context option worked when the
`ApiProperty` solution did not. Anyway, delete those.

## The #[Ignore] Attribute

The last way to ignore a field - if you want to ignore it completely - is to
add an attribute called... `#[Ignore]`! This comes from Symfony's serializer system.
When we try the test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Perfect: is is *not* writable nor readable. Cool!

Okay, let's reset all that dummy code. Get rid of the `#[Ignore]`... and let's see
if we have any extra `use` statements up here. Then, over in our processor, remove
the `->dump()`... and in our test, get rid of that extra field and the other
`->dump()`. All clean!

## Avoiding Writable on the Identifier

On this topic of readable and writable, right now, we can actually *change* the
`id` field in a `PATCH` request. Watch: set this to `47`... which I just made up,
and... it *fails* with a 500 error!

Open up the error:

> Entity 47 not found.

That's coming from *our* state processor. It's coming from down here... it reads
the `id` up here and tries to find that in the database... but it's not there. If
we *had* used a valid `id`, it would have queried for that *other* `User` entity
and updated the proeprties onto *that*!. That's a *big* no-no. We do *not* want
the `id` to be writable

Let's look at the full flow. First, our provider found the original `User` entity
with the `id` from the URL... and mapped that over to a `UserApi` object. Good
so far. Then, during deserialization, the `id` on the `UserApi` object was 
*changed* to `47`. Finally, in the state processor, we tried to query for an entity
with `id=47`... which is *ultimately* what we would have saved to the database.

Over in `UserApi`, to fix this, above `id`, add `writable: false`. Or we could
use the `#[Ignore]` attribute that we saw a second ago... since we don't want this
to be readable *or* writable. The `id` property really just helps generate the IRI...
but it's not *really* part of our API.

If we run that test now... it *passes* because it's *ignoring* new `id` field
in the JSON. Life is *good

Ok, while we're here, in `UserApi`, there are two other properties that, for now,
I want to make read-only. Above `$dragonTreasures`, make this `writable: false`...
though we *are* going to make this writable later.

Below, do the same thing for `$flameThrowingDistance`... because this is a
fake property that we're generating as a random number.

## Using "security" to hide/show a field

Oh, and another way to control whether a field is readable or writable is the
`security` attribute. For example, if `$flameThrowingDistance` were only readable
or writable if you had a certain *role*, you could use the `security` attribute
to check for that role. We'll see this a bit later.

## Different Input/Output Classes?

Finally, I want to mention one last strategy for conditional fields... even though
we won't do it. If the input JSON and output JSON for your API resource start to
look *really* different, it *is* possible to have separate classes for your input
and your output. You could have something like a `UserApiRead` and a *separate*
`UserApiWrite`. The `UserApiRead` would be used for the *read* operations like
`GET` and `GET` collection. And `UserApiWrite` would be used for `PUT`, `PATCH`,
and `POST` operations.

Though, full disclosure: I haven't actually played with this yet. It should work,
but there are probably some road bumps and details along the way. One other thing
to keep in mind is that, on `UserApiWrite`, you could, in theory, set the `output`
to `UserApiRead`. That would allow the user to send data in the format of
`UserApiWrite`, but be returned JSON from `UserApiRead`. But, to make this work,
after saving the `UserApiWrite` in your state processor, you would need to turn
it into a `UserApiRead` and return *that*.

Anyway, that's definitely more advanced, but if that's interesting and you try it
out, let me know!

Next up: Let's polish our new API resource by re-adding validation and security.
