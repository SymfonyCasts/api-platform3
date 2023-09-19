# IGNORED_ATTRIBUTES

Coming soon...

There's one other way you can do this, and both ways are functionally identical, so
you can use whichever you prefer. We'll set the `normalizationContext` up here, which
is something we set on our entities in previous tutorials, but instead of `groups`,
we're going to set a key called `AbstractNormalizer::IGNORED_ATTRIBUTES`, which we'll
*then* set to an array. And here, we can say `flameThrowingDistance`. This basically
says `When we're normalizing (when we're *going* to JSON), I want to ignore that
property`. This should make it *writable*, but not *readable*. When we try it... just
as expected, it's writable, but *not* readable. And we can do the same thing with
`denormalizationContext`. Copy that, put a "de" on the front of it, and now, it
shouldn't be writable *or* readable. And... yep! The "flameThrowingDistance" is "1",
so it was *not* writable, and down here... it's not readable either. *Sweet*. Again,
these are just different options, but they should all work the same. You may also
find that occasionally, for some reason, one doesn't work quite the way you expect it
to, so it's good to know that you have options. Let's go ahead and delete those.

Another way you can do this, which is nice and convenient, is to just ignore it
completely. Down here, we can use an attribute called `#[Ignore]`. This comes from
Symfony's serializer system, and it makes it *not* readable and *not* writable. It's
just ignored entirely. Over here, we can see that it was *not* written and it's not
readable. Cool!

Okay, let's reset all that dummy code. Get rid of the `#[Ignore]`... and let's see if
we have any extra `use` statements up here. Then, over in our processor, we'll get
rid of that `->dump()`... and in our test, we'll get rid of that extra field and the
other `->dump()` down here. Cool.

One more thing I want to point out here is that, right now, we can actually change
the `id` in a `PATCH` request. We'll set this to `47`, which I just made up, and...
it *fails* with a 500 error. If we open this really quick, it says `Entity 47 not
found`, and that's coming from our state processor. So it's actually coming from down
here. It reads the `id` up here and attempts to find that in the database, but it's
not there. If we *had* used a valid `id`, it would have changed to and updated a
different `User` entity. So... that's a *big* no-no. We do *not* want the `id` to be
writable.

So the full flow is this: Our provider found the original `User` entity with this
`id`, mapped that over to a `UserApi` object, the `id` on the `UserApi` object was
then *changed* to `47`, and then we tried to query for an entity with that `id`,
which is *ultimately* what we would have saved to the database.

Over in `UserApi.php`, to fix this, we're going to add `writable: false`, and we can
also use the the `#[Ignore]` attribute that we saw a second ago, since we don't
really want this to be readable *or* writable. The `id` property really just ends up
being the IRI, but it's not actually part of our API. If we run that test now... it
*passes* because it's *ignoring* that new `id`. It's not trying to query for it. Life
is *good*.

All right, while we're here, over in `UserApi.php`, there's two other properties
that, at least for now, we want to make read-only. Above `$dragonTreasures`, let's
make this `writable: false`. We'll talk about this more later, and maybe we'll allow
`$dragonTreasures` to be created or *set* on a user, but for now, we'll just say
`writable: false`. Down here, let's do the same thing for `$flameThrowingDistance`,
because this is really just a fake property that we're generating as a random number
anyway.

One other way to control whether a field is readable or writable, and we will see
this in a second, is the `security` attribute. For example, if
`$flameThrowingDistance` were, perhaps, only readable or writable if you had a
certain *role*, then you could use the `security` attribute to check for that role
here. That's relatepertains more to security than just general functionality, but
it's one more handy way to *show* or *not show* and *write* or *not write* a field.

Something else I should mention, even though we're not actually going to do it, is
that if your input and output for your class starts to look really different, it *is*
possible to have separate classes for your input and your output. You could have
something like a `UserApiRead` and a *separate* `UserApiWrite`. The `UserApiRead`
would just be used for the *read* operations like `GET` and `GET` collection. And
`UserApiWrite` would be used for `PUT`, `PATCH`, and `POST` operations. Full
disclosure, I haven't actually done this before, and there's probably a couple of
things we would need to worry about. This might be a case where, with `UserApiWrite`,
we would actually need to set the `output` to `UserApiRead` so that the user can
*send* data. Anyway, I don't want to go into too much detail, and if that doesn't
make sense to you, don't worry about it. But for those of you that *might* have that
case, I wanted to at least raise that as a possibility. As I said, that's not
something I have *personally* experimented with yet, but it's something to consider.

Next up: Let's polish our new API resource by re-adding validation and security.
