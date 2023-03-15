# Only Allow Owners to Edit

New security goal: I want to allow *only* the *owner* of a treasure to edit it. Right
now, you're allowed to edit a treasure as long as you have this role. But that means
you can edit *anyone's* treasure. Someone keep changing my Velvis painting's
`coolFactor` to 0. Not cool.

## TDD: Testing the only Owners can Edit

Let's write a test for this. At the bottom say
`public function testPatchToUpdateTreasure`. And we'll start like normal:
`$user = UserFactory::createOne()` then `$this->browser->actingAs($user)`.

Since we're editing a treasure, let's `->patch()` to `/api/treasures/`... and then
we need a treasure to edit! Create one on top:
`$treasure = DragonTreasureFactory::createOne()`. And for this test, we want to make
sure that the `owner` is *definitely* this `$user`. Finish the URL with
`$treasure->getId()`.

For the data, send some `json` to update *just* the `value` field to `12345`,
then `assertStatusCode(200)` and we can `assertJsonMatches('value', 12345)`.

Excellent! Now. This *should* be allowed because we're the `owner`. Copy the
method name, then find your terminal and run:

```terminal
symfony bin/phpunit --filter=testPatchToUpdateTreasure
```

And... no surprise, it passes.

Now let's try the *other* case: let's log in as someone *else* and try to update
this treasure.

Copy the entire `$browser` section. We *could* create another test method, but
this will also work great all in one. Before this, add
`$user2 = UserFactory::createOne()` - then log in as *that* user. This time,
change the `value` to 6, 7, 8, 9 and, since this should *not* be allowed, assert
that the status code is 403.

When we try the test now:

```terminal-silent
symfony bin/phpunit --filter=testPatchToUpdateTreasure
```

It fails. This *is* being allowed, so the API returns a 200.

## More Complex security Expressions

So how can we make it so that only the *owner* of a treasure can edit it? Well, over
in `DragonTreasure`, the answer is all about the `security` option.

One thing that gets tricky with `Put` and `Patch` is that *both* of these are used
to edit users. So if you're going to have both of them, you need keep their `security`
options in sync. I'm actually going to remove `Put` so we can focus on `Patch`.

The string inside of `security` is an *expression*... and we can get kinda fancy.
fancy here. We can access is granted if you have `ROLE_TREASURE_EDIT` *and* if
`object.owner == user`.

Inside the security expression, Symfony gives us a few variable. One is `user`, which
is the current `User` object. Another is `object`, which will be the current object
for this operation. So the `DragonTreasure` object. So we're saying that access should
be allowed if the `DragonTreasure`s `owner` is equal to the currently authenticated
`user`. That's... exactly what we wanted!

So, try the test again!

```terminal-silent
symfony bin/phpunit --filter=testPatchToUpdateTreasure
```

And... uh oh! We downgraded to a 500 error! This is where that saved log file
comes in handy. I'll click to open that up. If this is hard to read, view the page
source. Much better. It says:

> Cannot access private property `DragonTreasure::$owner`.

And it's coming from Symfony's `ExpressionLanguage`. Ah, I know what I did wrong.
The expression language is *like* Twig... but not exactly the same. We can't do fancy
things like `.owner` because `owner` is a private property. We need to call
the public method.

Now:

```terminal-silent
symfony bin/phpunit --filter=testPatchToUpdateTreasure
```

It works!

## Preventing Changing Owners: securityPostDenormalize

But you know me, I've *gotta* make it trickier. Copy part of the test. This time,
log in as the owner and edit our *own* treasure. So far, this is all good. But now
try to change the `owner` to someone else: `$user2->getId()`.

Now maybe this *is* something you want allow. Maybe you say:

> If you can edit a `DragonTreasure`, they are free to assign it a different `owner`.

But let's pretend that we want to prevent this. So `assertStatus(403)`. Do you
think this test will pass? Try it:

```terminal-silent
symfony bin/phpunit --filter=testPatchToUpdateTreasure
```

It fails! It *did* allow us to change the `owner`! Spin back over to `DragonTreasure`.
The `security` expression is run *before* the new data is deserializerd onto our
object. In other words,the `object` will be the `DragonTreasure` from the *database*,
but before any of the new JSON is applied to it. This means that it's checking that
the *current* `owner` is equal to the currently logged in user, which is the main
case that we want to protect.

But sometimes you want to run security *after* the new data has been put onto the
object. In that case, use an option called `securityPostDenormalize`. Remember
denormalize is the process of taking the data and putting it onto the object. So
`security` will still run first... and make sure we're the original owner. Now we
can also say `object.getOwner() == user`.

That looks identical... but in this case `object` will be the `DragonTreasure` with
the *new* data. So we're checking that the *new* `owner` is *also* equal to the
currently logged-in user.

By the way, in `securityPostDenormalize`, you also have a `previous_object` variable,
which is equal to the object before denormalization. It's the same as `object` up
in the `security` option. But, we don't need that.

Try the test now:

```terminal-silent
symfony bin/phpunit --filter=testPatchToUpdateTreasure
```

We got it!

## Security vs Validation

This last example highlights the two different types of security checks. The
first check is determining whether or not the user can perform this operation at
all. Like: is the current user allowed to make a `PATCH` request for this treasure?
That depends on the current user and the current DragonTreasure in the database.

But this second check is saying:

> Okay, now that I know I'm allowed to make a `PATCH` request, am I allowed to change
> the data in this exact way?

This depends on the currently logged in user and the *new* version of the data.

I'm bringing up this difference because, for me, the first case - where you're
trying to figure out whether an operation is allowed at all - regardless of what
data is being sent - *is* a job for security. And this is exactly how I would
implement it.

However, the second case - where you're trying to figure out whether or not the
user is allowed to make *a* specific change to the data - like are they allowed
to change the `owner` or not - for me, I think that's better handled by the
validation layer.

I'm going to keep this in the security layer right now. But later when we talk about
custom validation, we'll move this into that.

Up next: can our `security` attribute flex enough to *also* allow admin users
to edit anyone's treasure?
