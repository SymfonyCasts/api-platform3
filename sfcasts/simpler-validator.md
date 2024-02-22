# Simpler Validator for Checking State Change

We're down to *one failing test*. Apparently we *can* steal treasures by patching
a user and sending `dragonTreasures` set to a treasure that's owned by someone else.
This should give us a `422` status code, but we get *200*.

But no huge deal: we fixed this in the previous tutorial. *Now* we just need to
reactivate and *adapt* that validator.

## Re-Adding the Constraint

In `UserApi`, above the `$dragonTreasures` property, we can remove `#[ApiProperty]`
and add `#[TreasuresAllowedOwnerChange]`.

In the last tutorial, we put this above that same `$dragonTreasures` property,
but inside the `User` entity. The validator would loop over each `DragonTreasure`,
use Doctrine's `UnitOfWork` to get the `$originalOwnerId`, and *then* check to see
if the `$newOwnerId` is different from the original. If it *was*, it would build
a violation.

## Adapting the Validator

First things first: the constraint will *not* be used on a property that holds
a `Collection` object anymore: the new property holds a simple array. Also
`dd($value)`.

Over in the test, on top, put a `dump()` that says `Real owner is` with
`$otherUser->getId()`. That'll help us track if it's stolen.

Okay, run *just* this test:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

And... perfect! The "Real owner" is supposed to be `2`, and the dump from the
validator shows a single `DragonTreasureApi` object.

Reminder: this dump is the `dragonTreasures` property for the `UserApi` that's
being updated. And, though we can't see it here, that user's id is 1. But, in
the dump, look at the owner: it's still `2`! That's *still* the correct owner!

When we make the PATCH request, this treasure is loaded from the database,
transformed into a `DragonTreasureApi`, then set onto the `dragonTreasures`
property of the `UserApi`. *But*, nothing has - yet - *changed* the treasure's
`owner`: it still has the original `owner`.

The *problematic* part comes later when our state processor, really,
`UserApiToEntityMapper`, maps the `dragonTreasures` property from `UserApi` to the
`User` entity. That causes `User.addDragonTreasure()` to be called... and *that*
causes `DragonTreasure.setOwner()` to be called... with the *new* `User` object.

So even though things *kind of* seem ok right now in the validator - the
owner is still the original - the treasure *will* ultimately be stolen. Watch:
add a `return` to the validator so it always passes. And in `UserResourceTest`,
`->get('/api/users/'.$otherUser->getId())` and `->dump()`.

Run the test:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

And... yup! The `dragonTreasures` field is *empty* for `$otherUser` because their
treasure was stolen! They're mad!

## Changing the Constraint to be above the Class

To sort out this mess in the validator, we need to know *two* things. First, what
the *original* owner is for each treasure. And we have that: each `DragonTreasureApi`
object stills has its original owner. *Second*, we need to know which *user* these
treasures belong to now: which `UserApi` object this property belongs to.
And we *don't* have that.

To get it, we can move the constraint from this specific property - where all we
have access to are the `DragonTreasureApi` objects - up to the *class*. That will
give us access to the entire `UserApi` object.

Step 1 is easy... move the constraint to be above the class! To allow this,
open the constraint class. Get rid of the annotation stuff - since annotations are
dead... and we're not using them. Then change this from `TARGET_PROPERTY` and
`TARGET_METHOD` to `TARGET_CLASS`.

For some reason, my editor adds an extra `\` there, so delete that. We *also* need
to override a method. I'm not sure why we have to specify the target in both places...
this method is specific to the validation system, but no big deal:
`return self::CLASS_CONSTRAINT`.

Also add a return type - `string|array`. That'll avoid a deprecation notice.

Back over in the validator, `dd($value)`... then rerun the test:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

Let's see... yes! It dumps the *entire* `UserApi` object with ID `1`. Good stuff!
The `dragonTreasures` property holds that single treasure... and down here, we
see its original owner! Now we can just check to see if the *new* owner is different
from the *original* owner. Easy!

Back in the validator, `assert()` that `$value` is an `instanceof UserApi`.
Then, `foreach` over `$value->dragonTreasures as $dragonTreasureApi`.

The positively *lovely* thing is that we don't need *any* of this `$unitOfWork`
stuff anymore. Delete it! Then say `$originalOwnerId = $dragonTreasureApi->owner->id`.
The `$newOwnerId` will be `$value->id`. That's it!

To code defensively, you can add a `?` here... in case there *isn't* an owner...
like if this is a new treasure.

The logic down here ain't broke, so nothing to fix: if we *don't* have the
`$originalOwnerId` or the `$originalOwnerId` equals `$newOwnerId`, everything is
cool. *Else*, build this violation. Remove this `$unitOfWork` line here as well,
those `use` statements... and this `EntityManagerInterface` constructor. Thanks
to the new DTO system, we now have a *very* boring custom validator.

Try the test again... and cross your fingers and toes for good luck:

```terminal-silent
symfony php bin/phpunit --filter=testTreasuresCannotBeStolen
```

We got it! High-five something, then remove this `->dump()` from the top. Deep
breath: run the *entire* test suite:

```terminal
symfony php bin/phpunit
```

All green! We have *completely* rebuilt our system using DTOs! Woohoo!

And... we're done! It took a bit of work to get this all set up, but that's
the whole point of DTOs! There's more groundwork in the beginning in exchange for
more flexibility and clarity later on, *especially* if you're building a really robust
API that you want to keep stable.

As always, if you have questions, comments, or want to POST about the cool
stuff you're building, we're here for you down in the comments. All right friends,
seeya next time!
