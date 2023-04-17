# Conditional Fields by User: ApiProperty

We control which fields are readable and writable via serialization groups.
But what if you have a field that should be included in the API... but *only*
for certain users? Sadly, groups can't pull off that kind of magic on their own.

For example, find the `$isPublished` field and let's make this part of our API by
adding the `treasure:read` and `treasure:write` groups:

[[[ code('c28ce9037f') ]]]

Now if we spin over and try the tests:

```terminal-silent
symfony php bin/phpunit
```

This makes one test fail: `testGetCollectionOfTreasures` sees that `isPublished`
is being returned... and it's not expecting it.

Here's the plan: we'll sneak the field into our API but *only* for admin
users *or* owners of this `DragonTreasure`. How can we pull that off?

## Hello ApiProperty

Well, surprise! We don't often need it, but we can add an `ApiProperty` attribute
above any property to help *further* configure it. It has a bunch of stuff,
like a description that helps with your documentation and many edge-case things.
There's even one called `readable`. If we said `readable: false`:

[[[ code('be66f00679') ]]]

Then the serialization groups would say that this *should* be included in the
response... but then this would override that. Watch: if we try the tests:

```terminal-silent
symfony php bin/phpunit
```

They pass because the field is gone.

## The security Option

For *our* mission, we can leverage a super cool option called `security`. Set it
to `is_granted("ROLE_ADMIN")`:

[[[ code('d62a931a94') ]]]

That's it! If this expression return false, `isPublished` will *not* be included
in the API: it won't be readable *or* writable.

And when we run the tests now:

```terminal-silent
symfony php bin/phpunit
```

They still pass, which means `isPublished` is *not* being returned. 

Now let's go test the "happy" path where this field *is* returned. Pop open
`DragonTreasureResourceTest`. Here's the original test: `testGetCollectionOfTreasures()`.
We're anonymous, so `isPublished` isn't returned.

Now scroll down to `testAdminCanPatchToEditTreasure()`. When we create the
`DragonTreasure`, let's make sure it always starts with `isPublished => false`:

[[[ code('8b20a16c96') ]]]

Then, down here, `assertJsonMatches('isPublished', false)` to test that the
field *is* returned:

[[[ code('fc9330ddee') ]]]

Copy the test name, spin over and add `--filter` to run *just* that test:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

And... it passes! The field *is* being returned when we're an admin.

## Also Returning isPublished for the Owner

What about if we're the *owner* of the treasure? Copy the test... rename it
to `testOwnerCanSeeIsPublishedField()`... and let's tweak a few things.
Rename `$admin` to `$user`, simplify this to `DragonTreasureFactory::createOne()`
and make sure the `owner` is set to our new `$user`:

[[[ code('ed6dba3c3e') ]]]

We *could* change this to a GET request... but PATCH is fine. In either situation,
we want to make sure the `isPublished` field is returned.

Since we haven't *implemented* this yet... let's make sure it fails. Copy the
method name and try it:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

Failure achieved! And we know how to solve this! On the `security` option,
we *could* inline the logic with `or object.getOwner() === user`. But remember:
we created the voter so that we don't need to do crazy stuff like that! Instead,
say `is_granted()`, `EDIT` then `object`:

[[[ code('82eec19413') ]]]

Try the test now:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

## The Special securityPostDenormalize

Got it! Oh, and I haven't used it much, but there's also a `securityPostDenormalize`
option. Just like with the `securityPostDenormalize` option on each operation, this
runs *after* the new data is deserialized onto the object. What's interesting is
that  if the expression returns `false`, the data on the object is actually *reverted*.

For example, suppose the `isPublished` property started as `false` and then the user
sent some JSON to change it to `true`. But then, `securityPostDenormalize` returned
`false`. In that case, API Platform will *revert* the `isPublished` property *back*
to its original value: it will change it from `false` *back* to `true`. Oh, and
by the way, `securityPostDenormalize` is *not* executed on `GET` requests: it
only happens when data is being deserialized. So be sure to put your main security
logic in `security` and only use `securityPostDenormalize` if you need it.

Up next on our to-do list: let's level-up our user operations to *hash* the password
before saving to the database. We'll need a fresh, non-persisted plain password
property to make it happen.
