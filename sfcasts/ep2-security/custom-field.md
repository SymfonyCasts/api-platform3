# Totally Custom Fields

Let's get wild. I want to add a totally custom, crazy new field to
our `DragonTreasure` API that does *not* correspond to any property in our class.
Well, actually, we learned in part 1 of this series that adding custom fields is
possible by creating a getter method and adding a serialization group above it.
*But*, that solution only works if we can calculate the field's value solely from
the data on the object. If, for example, we need to call a *service* to get the
data, then we're out of luck.

Adding a new field whose data is calculated from a service is another trick up the
custom normalizer's sleeve. And since we already have one set up, I
thought we'd use it to see how this works.

# Testing for the IsMe Field

Go to `DragonTreasureResourceTest` and find
`testOwnerCanSeeIsPublishedField()`. Rename this to
`testOwnerCanSeeIsPublishedAndIsMineFields()`:

[[[ code('29adb7037b') ]]]

This is a bit silly, but if we own a `DragonTreasure`, we're going to add a new
boolean property called `$isMine` set to `true`. So, down at the bottom, we'll 
say `isMine` and expect it to be `true`:

[[[ code('034f0a0bc7') ]]]

Copy that method name, then spin over and run this test:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

Tada! It's `null` because the field doesn't exist yet.

## Returning the Custom Field

So how can we add this? Now that we've gone through the pain of getting the
normalizer set up, it's easy! The normalizer system will do its thing,
return the normalized data, then, between that and the `return` statement,
we can... just mess with it!

[[[ code('8a60aaf853') ]]]

Copy the if statement from up here. I could be more clever and reuse code,
but it's fine. If the object is a `DragonTreasure` and we own this
`DragonTreasure`, we will say `$normalized['isMine'] = true`:

[[[ code('aaeeb43eb3') ]]]

That's it! When we run the test:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedAndIsMineFields
```

All green!

## Custom Fields Missing in the Docs

But there's a practical downside to these custom fields: they will *not* be
documented in our API. Our API docs have *no* idea that this exists!

If you *do* need a super-duper custom field that requires service logic...
and you *do* need it to be documented, you have two options. First, you could add
a non-persisted `isMe` property to your class then populate it with a state provider.
We haven't talked about state providers yet, but they're how data is loaded. For
example, our classes are *already* using a *Doctrine* state provider behind the
scenes to query the database. We'll cover state providers in part 3 of this series.

The second solution would be to use the custom normalizer like we did, then try
to add the field to the OpenAPI docs manually via the OpenAPI factory trick that
we showed earlier.

Next: suppose a user *is* allowed to edit something... but there are certain changes
to the data that they are *not* allowed to make - like they could set a field to
`foo` but they aren't allowed to change it to `bar` because they don't have enough
permissions. How should we handle that? It's security meets validation.
