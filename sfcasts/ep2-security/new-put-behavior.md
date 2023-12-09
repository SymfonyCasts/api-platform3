# New PUT Behavior

Find your terminal and manually clear the cache directory:

```terminal-silent
rm -rf var/cache/*
```

I'm doing this so that, when we run all or our tests

```terminal-silent
symfony php bin/phpunit
```

we see a deprecation warning, which is fascinating. It says:

> Since API Platform 3.1: in API Platform 4, `PUT` will always replace the data.
> set `extraProperties["standard_put"]` to `true` on every operation to avoid breaking
> PUT's behavior. Use `PATCH` for the old behavior.

Okay... what does that mean? Right now, it means nothing has changed: our `PUT`
operation behaves like it always has. But, in API Platform 4, the behavior of `PUT`
will change dramatically. And, at some point between now and then, we need to
opt *into* that new behavior so that it doesn't suddenly break when we upgrade to
version 4 in the future.

## What's Changing in PUT

So what's changing exactly? Head over to the API docs and refresh. Use the `GET`
collection endpoint... and hit "Execute", so we can get a valid ID.

Great: we have a treasure with ID 1.

Right now, if we send a `PUT` request with this ID, we can send just *one*
field to update just that *one* thing. For example, we can send `description`
to change *only* that.

Oh, but before we Execute this, we *do* need to be logged in. In my other tab, I'll
fill in the login form. Perfect. *Now* execute the `PUT` operation.

Yup: we pass only the `description` field, and it *updates* only the `description`
field: all the other fields remain the same.

Whelp, it turns out that this is *not* how `PUT` is supposed to work according
to the HTTP Spec. `PUT` is *supposed* to be a "replace". What I mean is, if we send
only one field, the `PUT` operation is supposed to take that new resource - which
is just the one field - and *replace* the existing resource. That's a complicated
way of saying that, when using PUT, you need to send *every* field, even the fields
that aren't changing. Otherwise, they'll be set to `null`.

If that sounds kind of crazy, I kind of agree, but there are valid technical reasons
for why this is the case. The point is that: this is how `PUT` is *supposed* to work
and in API Platform 4, this is how `PUT` *will* work.

Honestly, it makes `PUT` less useful. So you'll notice that I'll pretty much
exclusively use `PATCH` going forward.

## Moving to the new PUT Behavior

So whether we like it or not, at some point between now and API platform 4, we
need to tell API Platform that it is okay for it to change the behavior
of `PUT` to the "new" way. Let's do that now by adding some extra config to
every `ApiResource` attribute in our app.

***TIP
To solve this globally for all your resources at once, you can add this as a default
in the API Platform configuration:

```yml
# config/packages/api_platform.yaml
    api_platform:
        defaults:
        extra_properties:
            standard_put: true
```
***

Open `src/Entity/DragonTreasure.php`... and add a new option called `extraProperties`
set to an array with `standard_put` set to `true`:

[[[ code('ea885696ed') ]]]

That's it! Copy that... because we're going to need that down here on this
`ApiResource`... even though it doesn't have a `PUT` operation:

[[[ code('9e203f8e8e') ]]]

Then, over in `User`, add that to both of the `ApiResource` spots as well:

[[[ code('d2d3f33582') ]]]

Now when we run our tests, the deprecation is gone! We're not *using* the `PUT`
operation in any tests, so everything still passes.

## Seeing the New Behavior

To see the new behavior, try out the `PUT` endpoint again: still sending just *one*
field. This time... check it out! A 422 validation error! All the fields that we
did *not* include were set to null... and that caused the validation failure.

So... this makes `PUT` a bit less useful... and we'll lean a lot more on `PATCH`.
If you don't want to have a `PUT` operation at all anymore, that makes a lot of sense.
One *unique* thing about the new `PUT` behavior is that you could use it to create
*new* objects... which could be useful in some edge-cases... or an absolute
nightmare from a security standpoint as we now need to worry about objects
being edited or *created* via the same `PUT` operation. For that reason, as we go
along, you'll see me remove the `PUT` operation in some cases.

Next: let's get more complex with security by making sure that a `DragonTreasure`
can only be edited by its owner.
