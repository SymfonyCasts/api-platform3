# DTO & Security

Our `DragonTreasureApi` is looking great! Back when this resource was our on entity,
we added *quite* a few customizations *and* included tests for those. The plan
*now* is to put those back piece-by-piece and see how we can simplify the
implementation inside our new DTO-powered setup.

Run all the dragon treasure tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

Quite a few fail... and *one* of them says:

> Current response status code is 422, but 403 expected.

This `testPostToCreateTreasureDeniedWithoutScope` is related to security, and that
makes sense. We haven't added any of the security stuff to `DragonTreasureApi`.
So let's do that!

## Adding Security Back

Start like we did with the `UserApi`: by specifying the exact operations we want.
Start with `new Get()`, `new GetCollection()`, and `new Post()`. In our original
system, `Post()` had a `security` option set to `'is_granted("ROLE_TREASUURE_CREATE)`.

This is directly related to that test failure, which checks to make sure that our
API token *has* that role on it. Well... if I spell "create" correctly, at least.

We also had a `new Patch()` operation and that *also* had a `security` option,
which leveraged a custom voter to check if the current user can `EDIT` this treasure.
More on that in a minute.

And *finally*, we had `new Delete()`. Last time, we said:

> Hey, only admins can delete.

Repeat that with `is_granted("ROLE_ADMIN")`.

Okay, we had *six* failures earlier and now:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

We're down to five. Progress! Let's zoom in on `testPatchToUpdateTreasure` and
run *just* that:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

Back over here... let's take a look at what that's doing. Ok, it creates a `User`,
a treasure, logs in as the owner, tries to change the *value* of that treasure,
makes sure we get a 200 status code, and finally, checks that we see the updated
value. Right now, we're getting a 403 status code instead of 200.

## Updating the Security Voter for the DTO

A *403* status is a *security* failure. For some reason, we're not allowed to make
a `Patch()` request to this treasure, even though we're the owner!

Let's think: our `Patch()` request is using `is_granted("EDIT", object)`. This
`"EDIT", object` thing is handled by a custom voter called `DragonTreasureVoter`
that we created in a previous tutorial. So, either this voter is not being
called or its saying that we shouldn't have access.

To see what's going on under the hood, let's `dump($attribute, $subject)`. This
`supports()` method is called *any* time a security decision is made across the entire
system, so that *should* get hit.

When we run the test again:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

There's the dump! It dumps `EDIT`, which comes from the `Patch()` operation.
But here's the kicker: the *object* is now a `DragonTreasureApi`. And that makes
sense. *But* our `DragonTreasureVoter` was written to work with the *entity*, *not*
`DragonTreasureApi`.

No problem! Let's update this voter to work with `DragonTreausreApi`. For clarity,
*rename* this to `DragonTreasureApiVoter`. *Then*, we'll support if
`DragonTreasureApi` is the `$subject`. And down here, this `$subject` should also
be `DragonTreasureApi`. `dd($subject)` here... and below, let's fix the code. This
says that if the user doesn't have this role (actually a *scope*, which relates to
the token scopes), then return `false`.

Then, the most important part is this: if the `$subject` - which is a
`DragonTreasureApi` - has an owner that equals `$user` - the currently authenticated -
then return true: access granted

Comment out this `dd()` really quick. What we need now is `$subject->owner`.

Well, that's not *quite* right... and if we put that `dd()` back, we can see why.
Run the test:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

This dump - the `$subject` is, of course, a `DragonTreasureApi`. And remember, its
`owner` property *isn't* a `User` entity: it's a `UserApi` object. So we can't
just *compare* the `UserApi` object to the `$user` entity object.

We *also* need to be careful because of our mapper. Thanks to the depth, the `UserApi`
*isn't* populated: it's a *shallow* object. That's okay - we can compare the id
of the objects - just keep this in mind.

So, the tl;dr is: compare the `id` property to `$user->getId()`. Oh, and it
didn't autocomplete `getId()`... but we can help our editor by making this
`instanceof` check specifically that this is a `User` entity, which it always
will be in our app.

*Now* use `getId()`... and I'll code defensively by adding a `?`... in case
this `DragonTreasureApi` doesn't have an owner - like for a treasure we're creating
right now.

Ok! Head over and try it now!

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

## Adding the application/merge-patch+json Header

Progress! The current response status code is now *415*. This is thanks to a small
detail we talked about a few times:

> The content-type `application/json` is not supported. Supported MIME types are
> `application/merge-patch+json`.

Whenever we make a PATCH request, we need to have a `headers` key with `Content-Type`
set to `application/merge-patch+json`. The reason we didn't have to have that before,
as I mentioned in a previous tutorials... is due to some funny business with formats
which made that, accidentally, unnecessary for this resource. *But* now we *do*
need it.

Let's quickly add that to all of our `patch()` requests. There's a *bunch* of them.
Done!

Let's see if we have any luck!

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

And... *ooh*... it *dies*. It hit our dump! That's coming from
`DragonTreasureApiToEntityMapper`: when the `owner` is sent in the JSON. Let's comment
this out for a moment so we can see the full picture. Run the test again:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

> Current response status code is 200, but 422 expected.

Coming from down on line 157. So, looking at our test, *most* of it passes. Line
157 is *way* down here. This means that we *are* able to send a `patch()` request
and have that update.

And the full *flow* here is really interesting! When we make a `patch()` request
to a treasure, API Platform starts by using our data provider to find the
`DragonTreasure` entity. Then we *map* that to a `DragonTreasureApi` object. Next,
the new `value` is deserialized onto that `DragonTreasureApi`. Finally, in our
processor, we map the *updated* `DragonTreasureApi` *back* to a `DragonTreasure`
entity, and *that* is ultimately what saves. The `DragonTreasureApi` is then
*serialized* and returned as JSON.

So this *is* working... and I *love* how all the pieces come together.

## Updating the Custom Validator

Where we're *failing* is all the way down here. This checks to see if we're allowed
to change the `owner` to someone else. We log in in as `$user` and edit our *own*
treasure... but trying to change the treasure to *another* owner. This is like
a super generous dragon that sneaks into other dragon's caves and leaves treasure
there. That's super nice... but not something we want to allow.

Previously, we had a custom validator that prevented this. So let's re-add that!

Open `DragonTreasureApi` and find the `$owner` property. ADd `#[IsValidOwner]`:
a validator we created in an earlier tutorial.

Look in the `src/Validator/` directory. Previously, this validator expected its
constraint to be used above a property that held a `User` *entity*. *We're* putting
it on a property that holds a `UserApi`. So like with the voter, we need to update
it for the new reality.

Right here, `assert()` that `$value` is an `instanceof UserApi`.

Down here, we need to see check if the value (meaning the `UserApi` that's on this
property) is *not* equal to the currently authenticated user. Once again, we'll use
the `id`s to compare this. And... *also* once again, I'll use `assert()` to help
my editor. Now... it's happy about the `getId()`... but not about my missing
semicolon!

Moment of truth! Run that test:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php --filter=testPatchToUpdateTreasure
```

And... it passes! Try *everything*:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

And... ah! We're down to just *three* failures. And these are all related to the
same thing: the `isPublished` property. Our `DragonTreasureApi` doesn't have an
`isPublished` property yet. We saved *that* for last because it's a *little*
different and interesting. Let's tackle it *next*.
