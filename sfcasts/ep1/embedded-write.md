# Embedded Write

I'm going to try out the GET one treasure endpoint... using a real id.
Perfect. Because of the changes we just made, the `owner` field is
*embedded*.

What about *changing* the owner? Piece of crumb cake: as long as the field is
writable... which ours is. Right now the `owner` is id 1. Use the PUT endpoint
to update id 2. For the payload, set `owner` to `/api/users/3`.

And... execute! Bah! Syntax error. JSON is crabby. Remove the comma, try again
and... yes! The `owner` comes back as the IRI `/api/users/3`.

## Sending Embedded Data to Update

But *now* I want to do something wild! This treasure is owned by user 3.
Let's go get their details. Open the GET one user endpoint, try it out, enter 3
and... there it is! The username is `burnout400`.

Here's the goal: while updating a `DragonTreasure` - so while using the PUT endpoint
to `/api/treasures/{id}` - instead of changing from one owner to another, I want
to change the existing owner's `username`. Something like this: instead of setting
`owner` to the IRI string, set it to an object with `username` assigned to something
new.

Would that work? Let's experiment! Hit Execute and it does *not*. It says:

> Nested documents for attribute `owner` are not allowed, use IRI instead.

## Allowing Writable Properties to be Embedded

So, at first glance, it looks like this isn't allowed: it looks like you can only
use an IRI string here. But actually, this *is* allowed. The problem is that the
`username` field is not *writable* via this *operation*.

Let's think about this. We're updating a `DragonTreasure`. This means that API
Platform is using the `treasure:write` serialization group. That group *is*
above the `owner` property, which is why we can change the `owner`.

[[[ code('6f3c3ee5de') ]]]

But if we want to be able to change the owner's `username`, then we *also* need to
go into `User` and add that group here.

[[[ code('caffaf1dfd') ]]]

This works exactly like embedded fields when we *read* them. Basically,
since at least *one* field in `User` has the `treasure:write` group, we are *now*
allowed to send an *object* to the `owner` field.

## New vs Existing Objects in Embedded Data

Watch: fire it up again. It works... almost. We get a 500 error:

> A new entity was found through the relationship `DragonTreasure.owner`, but was
> not configured to `cascade` persist.

Woh. This means that the serializer saw our data, created a *new* `User` object and
then set the `username` onto it. Doctrine failed because we never told it to persist
the new `User` object.

Though... that's not the point: the point is that we don't *want* a new `User`!
We want to grab the existing owner and update *its* `username`.

By the way, to make this example more realistic, let's also add a `name` to the
payload so we can pretend that we're *actually* updating the treasure... and decide
to *also* update the `username` of the owner while we're in the neighborhood.

Anyways: how do we tell the serializer to use the *existing* owner instead of creating
a new one? By adding an `@id` field set to the IRI of the user: `/api/users/3`.

That's it! When the serializer sees an object, if it does *not* have an `@id`, it
creates a new object. If it *does* have an `@id`, it finds *that* object and then
sets any data onto it.

So, moment of truth. When we try it... of course, another syntax error. Get
it together Ryan! After fixing that... perfect! A 200 status code! Though...
we can't really see if it updated the `username` here... since it just shows the owner.

Use the GET one `User` endpoint... find user 3... and check that sweet data! It
*did* change the `username`.

Ok, so I realize that this example may not have been the most realistic, but being
able to update related objects *does* have plenty of real use-cases.

## Cascading the Persist to Create a new Object

Looking back at that `PUT` request, what if we *did* want to allow a new
`User` object to be created and saved? Is that possible? It *is*!

First, we would need to add a `cascade: ['persist']` to the `treasure.owner`
`ORM\Column` attribute. This is something we'll see later. And second, we would
need to make sure to expose all of the required fields as writable. Right now
only `username` is writable... so we couldn't send `password` or `email`.

## The Valid Constraint

Before we keep going, we *are* missing one small, but important, detail. Let's
try this update one more time with the `@id`. But set `username` to an empty string.

Remember, the `username` field has a `NotBlank` above it, so this should fail
validation. And yet, when we try it, we get a 200 status code! And if we go to the
GET one user endpoint... yeah, the `username` is now empty! That's... a problem.

How did that happen? Because of how Symfony's validation system works.

The top-level entity - the object that we're modifying directly - is `DragonTreasure`.
So the validation system looks at `DragonTreasure` and it executes all of the
validation constraints. However, when it gets to an object like the `owner` property,
it stops. It does *not* continue to validate *that* object as well.

*If* you want that to happen, you need to add a constraint to this called
`Assert\Valid`.

[[[ code('8928ebc1be') ]]]

Now... on our PUT endpoint... if we try this again, yep! 422: `owner.username`, this
value should not be blank.

Being able to update an embedded object is really neat & powerful. But the cost
of this is making your API more and more complex. So while you *can* choose to do
this - and you should if it's what you want - you might also choose to force
the API client to update the treasure first... and then make a second request to
update the user's username... instead of allowing them to do it all fancy at the
same time.

Next: let's look at this relationship from the *other* side. When we're updating
a `User`, could we also update the *treasures* that belong to that user? Let's
find out!
