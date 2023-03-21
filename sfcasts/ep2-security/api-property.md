# Conditional Fields by User: ApiProperty

We control which fields are readable and writable inside our code via the
serialization groups. But what if you have a field that should be included in the
API... but *only* for certain users? That's not something we can do out of the box
with groups.

For example, find the `isPublished` field and let's make this part of our API by
adding the `treasure:read` and `treasure:write` groups.

Now if we spin over and try the tests:

```terminal-silent
symfony php bin/phpunit
```

This makes one test fail: `testGetCollectionOfTreasures` sees that `isPublished`
is being returned and it's not expecting it.

Here's the plan: we only want to include this field in our API but *only* for admin
users or owners of this `DragonTreasure`. How can we do that?

## Hello ApiProperty

Well, surprise! We don't often need it, but you can add an `ApiProperty` attribute
above any property to help *further* configure things. There's actually a bunch of
stuff that you we do with this, like a description that helps with your documentation
and many more edge-case things. There's even one called `readable`. If we said
`readable: false`... then the serialization groups say that this *should* be included
in our response... but then this overrides that. Watch: if we try the tests:

```terminal-silent
symfony php bin/phpunit
```

The test passes because the field is gone.

## The security Option

For *our* mission, we can leverage a super cool option inside of `ApiProperty`
called `security`. For example we can say `is_granted("ROLE_ADMIN")`.

That's it! If this expression return false, `isPublished` will *not* be included
in the final result.

And when we run the tests now:

```terminal-silent
symfony php bin/phpunit
```

They still pass, which means that `isPublished` field is *not* being returned. 

Now let's go test the "happy" path where this field *is* returned. Pop open
`DragonTreasureResourceTest`. Here's the original test: `testGetCollectionOfTreasures`.
We're anonymous, so `isPublished` is not returned.

Now scroll down to `testAdminCanPatchToEditTreasure`. When we create the
`DragonTreasure`, let's make sure it always starts with `isPublished` false.

Then, down here, `assertJsonMatches('isPublished', false)` to test that in this
situation, that field *is* returned.

Copy the test name, spin over and `--filter` to run *just* that test:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

And... that passes! The field *is* returned when we're an admin user.

## Also Returning isPublished for the Owner

What about if we're the *owner* of the treasure? Copy this test, then rename it
to `testOwnerCanSeeIsPublishedField()`... and let's tweak a few things.
Rename `$admin` to `$user`, simplify this to
`DragonTreasureFactory::createOne()` and make sure the `owner` is set to our new
`$user`.

We could change this to a GET request... but this is fine as a PATCH. The point
is, we want to make sure the `isPublished` is returned.

Since we haven't *implemented* this yet... let's make sure it fails. Copy the
method name and try it:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

Yes! Failure achieved! And, we know how to solve this. On the `security` option,
we *could* inline the logic with `or object.getOwner() === user`. But remember:
we created the voter so that we don't need to do crazy stuff like this! Instead,
say `is_granted()`, `EDIT` then `object`.

Try the test now:

```terminal-silent
symfony php bin/phpunit --filter=testOwnerCanSeeIsPublishedField
```

## The Special securityPostDenormalize

Got it! Oh, and I haven't used it much, but there's also a `securityPostDenormalize`
option. Just like with the other `securityPostDenormalize` on the operations this
runs after the new data is serialized onto the object. What's interesting is that
if the expression returns `false`, the data on the object is actually *reverted*.

For example, suppose the `isPublished` property started as `false` and then the user
sent some JSON to change it to `true`. But then, `securityPostDenormalize` returned
`false`. In that case, API Platform will *revert* the `isPublished` property *back*
to its original value. Oh, and also, `securityPostDenormalize` is *not* executed
on `GET` requests: it only happens when data is being deserialized. So make sure
to put your main security logic in `security` and only use `securityPostDenormalize`
when you need it.

Next: let's finally fix our user operations so that they *hash* the password before
storing it in the database. This will involve a new topic and tool called state
processors.
