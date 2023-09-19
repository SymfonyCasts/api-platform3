# Controlling Fields without Groups

When your API resource is on an entity, serialization groups are a *must* because
you'll definitely have some properties that you want to show or *not* show. But
serialization groups add *complexity*. One of the big benefits of having a
separate class for your API is not *needing* serialization groups. Because... the
whole point of your API class is to represent your API... so, in theory, you'll
want *every* property to be part of your API.

But, in the real world, that's not always true. And we just ran into one case:
`password` should be a write-only field. Let's try to replicate some of the complexity
that our `User` entity *originally* had, but by avoiding serialization groups.

In `UserResourceTest`, down here, remove the `->dump()`... and after we
`->assertStatus(201)`, assert that the `password` property is *not* returned. To
do that, we can say `->use(function(Json $json))`. The `use()` function comes
from browser and there are a few different objects - like `Json` - that you can
*ask* it to pass you via the type-hint. In this case, browser takes the JSON from
the last response, puts it into a `Json` object and passes it to us. Use it by
saying `$json->assertMissing('password')`.

If we try that now:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

It *fails* because `password` *does* exist.

## readable: false

Okay, let's take a tour of *how* we can customize our API fields without groups.
One of the easiest, (and, coincidentally, my *favorite*) is to use `#[ApiProperty()]`
with `readable: false`.

We want this to be *writable*, but not *readable*.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... that fixes things! *Beautiful*.

Let's repeat this for `id`... because `id` is pretty useless since we have `@id`.
When we run that... it fails because `id` *is* being returned. So now, copy...
just the `readable: false` part... add `#[ApiProperty]` above `id`, paste, and
I'll also add `identifier: true`... just to be explicit.

And now...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

That *passes*.

## writable: false

Let's keep going. Copy the next test name - `testPatchToUpdateUser` - and run
it:

```terminal
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

It passes *immediately*! Yay! `->patch()` is already working. To dive deeper into
other ways we can hide or show fields, also send a `flameThrowingDistance` field
in the JSON set to 999. And down here, `->dump()` the response.

Before we try this, find `EntityClassDtoStateProcessor`. Right after we
set the `id`, `dump($data)`. Those two dumps will help us understand
*exactly* how this all works.

*Now* run the test:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

And... awesome. The first dump on top - from the state processor - shows
`flameThrowingDistance` 999, which means the field is *writable*. And below,
the response returned 999, which means the field is also *readable*. Yup...
this is a normal, boring field. If the user sends the field in JSON, that new
value *is* deserialized onto the object.

Ok, experimentation time! In `UserApi`, above the property, start with the same
`#[ApiProperty()]` and `readable: false`. We've already seen this.

When we run the test, on top, the "999" was *written* onto the `UserApi`,
but it doesn't show up in the response. It's writable, but not readable.

If we *also* pass `writable: false`... and try again. On top, the value is
just "10". The field is *not* writable, so the field in the JSON was ignored.
It's also not in the response: it's not readable or writable.

The readable/writable options alone are probably going to solve most situations.
But next, let's learn some other tricks and see why you probably want to make
sure that your identifier is *not* writable.
