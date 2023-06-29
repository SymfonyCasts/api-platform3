# Custom Validator

If you need to control *how* a field like `isPublished` is *set* based on *who*
is logged in, you have two different situations.

## Protecting a Field vs Protecting its Data

First, if you need to prevent certain users from writing to this field *entirely*,
that's what security is for. The easiest option is to use the `#[ApiProperty(security: ...)]`
option that we used earlier above the property. Or you could get fancier and add
a dynamic `admin:write` group via a context builder. Either way, we're preventing
this field from being written *entirely*.

The second situation is when a user *should* be allowed to write to a field... but
the valid data they're allowed to *set* depends on who they are. Like maybe a user
is allowed to set `isPublished` to `false`... but they're not allowed to set it
to `true` unless they're an admin.

Let me give you a different example. Right now, when you create a `DragonTreasure`,
we force the client to pass an `owner`. We can see this in
`testPostToCreateTreasure()`. We're going to fix this in a few minutes so that
we can leave this field *off*... and then it'll be set automatically to whoever
is authenticated.

But right now, the `owner` field is allowed and required. But *who* they are allowed
to *assign* as the `owner` depends on who is logged in. For normal users, they should
only be allowed to assign *themselves* as a user. But for admins, they should be
able to assign *anyone* as the `owner`. Heck, maybe in the future we get crazier
and there are clans of dragons... and you can create treasures and assign them
to anyone in your clan  The point is: the question isn't *if* we can set this field,
but *what* data we're *allowed* to set it to. And that depends on *who* we are.

## Solving with Security or Validation?

Ok, actually, we solved this problem earlier for the `Patch()` operation. Let me
show you. Find `testPatchToUpdateTreasure()`. Then... let's run just that test:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

And... it passes. This test checks 3 things. First, we log in as the user that
owns the `DragonTreasure` and make an update. That's the happy case!

Next, we log in as a *different* user and try to edit the first user's
`DragonTreasure`. That is *not* allowed. And *that* is a proper use of `security`:
we don't own this `DragonTreasure`, so we are not *at all* allowed to edit it.
That's what the `security` line is protecting.

For the last part, we log in again as the owner of this `DragonTreasure`. But then
we try to change the owner to someone else. That's also *not* allowed and *this*
is the situation we're talking about. It's currently handled by
`securityPostDenormalize()`. But I want to handle it instead with *validation*.
Why? Because the question we're answering is this:

> Is the `owner` data that's sent valid?

And... validating data is... the job of validation!

Remove the  `securityPostDenormalize()`:

[[[ code('bd19ffd202') ]]]

And to prove this was important, run the test again:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

Yup! It failed on line 132... which is this one down here. Let's rewrite this
with a custom validator, which is actually a lot nicer.

## Creating the Custom Validation

Oh but because this will fail via validation when we're done, change to
`assertStatus(422)`:

[[[ code('91c485445c') ]]]

The idea is that we *are* allowed to PATCH this user, but we sent invalid data:
we can't set this owner to someone *other* than ourselves.

Ok, head to the command line and run:

```terminal
php ./bin/console make:validator
```

Give it a cool name like `IsValidOwnerValidator`. In Symfony, validators are *two*
different classes. Open `src/Validator/IsValidOwner.php` first:

[[[ code('2abcf2b11a') ]]]

This lightweight class will be used as the *attribute*... and it just holds
options that we can configure, like `$message`, which is enough. Let's change
the default message to something a bit more helpful:

[[[ code('e632cf89e2') ]]]

The second class is the one that will be executed to handle the logic:

[[[ code('20258f5bbc') ]]]

We'll look at that in a moment... but let's *use* the new constraint first.
Over in `DragonTreasure`, down on the `owner` property... there we go...
add the new attribute: `IsValidOwner`:

[[[ code('e8549102c9') ]]]

## Filling in the Validator Logic

Now that we have this, when our object is validated, Symfony will call
`IsValidOwnerValidator` and pass us the `$value` - which will be the `User`
object - and the constraint, which will be `IsValidOwner`.

Let's do some clean up. Remove the `var` and replace it with
`assert($constraint instanceof IsValidOwner)`:

[[[ code('22fbf32f58') ]]]

That's just to help my editor: we know that Symfony will always pass us that.
Next, notice that it's checking to see if the `$value` is null or blank. And if
is, it does nothing. If the `$owner` property is empty, that should really be
handled by a *different* constraint.

Back in `DragonTreasure`, add `#[Assert\NotNull]`:

[[[ code('6b92e8ef08') ]]]

So if they forget to send `owner`, *this* will handle that validation error. Back
inside *our* validator, if we have that situation, we can just return:

[[[ code('7115db94a3') ]]]

Below this, add one more `assert()` that `$value` is an `instanceof User`.

Really, Symfony will pass us whatever value is attached to this property... but
*we* know that this will *always* be a `User`:

[[[ code('90eeece145') ]]]

Finally, delete `setParameter()` - that's not needed in our case - and
`$constraint->message` is reading the `$message` property:

[[[ code('bfbe36d59b') ]]]

At this point, we have a functional validator! Except... it's going to fail in all
situations. Ah, let's at least make sure it's being called. Run our test:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

Beautiful failure! A 422 coming from `DragonTreasureResourceTest` line 110...
because our constraint is *never* satisfied.

## Checking for Ownership in the Validator

*Finally* we can add our business logic. To do the owner check, we need to know
who's logged in. Add a `__construct()` method, autowire our favorite `Security`
class... and I'll put `private` in front of that, so it becomes a property:

[[[ code('3cb4f4a890') ]]]

Below, set `$user = $this->security->getUser()`. And if there is *no* user for
some reason, throw a `LogicException` to make things explode:

[[[ code('55f5589469') ]]]

Why not trigger a validation error? We could... but in our app, if an anonymous
user is somehow successfully *changing* a `DragonTreasure`... we have some sort
of misconfiguration.

Finally, if `$value` does not equal `$user` - so if the `owner` is *not* the
`User` - add that validation failure:

[[[ code('bfc68bf310') ]]]

That's it! Let's try this thing!

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateTreasure
```

And... bingo! Whether we're creating or editing a `DragonTreasure`, we are
not allowed to set the owner to someone that is *not* us.

And we can add whatever other fanciness we want. Like if the user is an admin,
return so that admin users are allowed to assign the `owner` to *anyone*:

[[[ code('7eb8843df1') ]]]

I love this. But... there's still one big security hole: a hole that will allow
a user to *steal* the treasures of someone else! Not cool! Let's find out what
that is next and crush it.
