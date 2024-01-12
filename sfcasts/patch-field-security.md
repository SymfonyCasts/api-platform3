# Field Security with Patch

In a heroic twist of bravery, we've decided to run *all* the dragon treasure
tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

And... we have *three* failures, including one from
`testAdminCanPatchToEditTreasure` on line 200... which says
`->assertJsonMatched('isPublished', true)`. That's failing because... we don't have
an `isPublished` field in our `DragonTreasureApi` *at all*!

## Adding the isPublished Field

That's because this is an *interesting* field. Previously, this field was readable
*only* by admin users or the owner. Let's re-add this field and keep
that behavior. Say `public bool $isPublished = false`.

Then... head into the mapper to populate this. Down here, get rid of the `TODO`
and say `$entity->setIsPublished($dto->isPublished)`.

So if we *change* `isPublished` in the API call, the new value will sync back
to the entity.

On the other side... it doesn't matter where... say
`$dto->isPublished = $entity->getIsPublished()`.

*Cool*! We don't have any security yet... so when we run the tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

A few pass, but the *original* one still fails - `testGetCollectionOfTreasures` -
because it's not expecting the `isPublished` to be there.

## Conditionally Showing isPublished via Security

Check it out: this is the *first* test, and at the bottom, we've asserted that these
are the *exact* properties we should have if we fetch treasures as an anonymous
user. So since we're *not* the owner or an admin, we should not see `isPublished`

*How* can do we that? Earlier, we worked on `DragonTreasureApiVoter`. When we call
this with the `EDIT` attribute, it checks to see if we're an admin, and if we *are*,
it grants access. It *also* checks to see if we're the *owner*. This is *exactly*
the logic we want to use to determine if the `isPublished` field should be serialized.

So... let's use it! Above this property, say
`#[ApiProperty(security: 'is_granted("EDIT", object)')]`.

If you want, you can change this attribute to something else - like `OWNER` -
if that's more clear. `EDIT` sounds a little funny here... since we're just deciding
if we should *include* this field in the response... not "edit" it... but it's up
to you.

*More* importantly, let's see if this does the trick. Run the tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

It *fixed* our first test! The `isPublished` field is no longer being shown.
*But*, curiously, we made *another* test *fail*. Whac-A-Mole! Now it's
`testPublishTreasure` - failing on line 244.

Let's pop over and search for that. Okay, as the name suggests, we're testing to see
if we can publish this treasure. We create a treasure that is
`'isPublished' => false`, log in as its owner, then send a  `patch()` request
to set `isPublished` to `true`. Finally, we assert that the JSON in the response
*has* `isPublished` true.  And *that's* what's failing.

## The ApiProperty Security Option on Patch Operations

Why? It took me a bit of debugging to unravel this mystery. The problem is that,
when the JSON is deserialized, `isPublished` is *not* writable.

The `security` expression is called both when serializing and *deserializing*:
when taking the JSON from the request and updating the object. For some reason,
during deserialization, our `security` expression is returning *false*!

The reason is... *arguably* a bug:  I have an issue open on API Platform. When
you make a `patch()` request, our data provider first loads the object from
the database. Despite this, when the expression is called during deserialization,
`object` is *always* null. And because our voter only supports if `object` is
a `DragonTreasureApi`, this returns `false`. Ultimately, *no* voters support this,
and when that happens, access is *denied*. The end result is that `isPublished`
is *not* writable.

The workaround is a bit weird, but stay with me here. We're basically
going to allow access to this field if `object === null` or
`is_granted("EDIT", object)`.

Think about this. If we're *reading* a `DragonTreasure`, then `object` is
*never* `null`. We will *always* have an object, so the voter will *always* be
called. This `object === null` will only happen during *deserialization*: when we're
checking to see if we can *write* this field. This *effectively* makes the field
*always writable*. That *seems* like a problem, but it's not, because we already
have `security` up here on `Post()` and `Patch()`. For `Patch`, only the *owner*
can edit this object. So once you've passed the `Patch` security, we already know
that you can edit this object. So, down here, it's okay to let us *always* edit
this field.

If this looks too weird to you, another strategy is to leave API security off of
the field *entirely*. Then, we would use the *mapper* to handle conditionally
setting the `isPublished` field. We could put some security logic right here that
basically says:

> Only set the `isPublished` field on the DTO if you're the owner. Otherwise,
> leave `isPublished` null as the default.

It's good to remember that we *do* have full control of the data via our mappers.

Okay, let's go back and re-add our security expression. Oh! And go back to
the mapper as well: I just realized that we also want to keep that `isPublished`
code... just not in the `if` statement.

All right, *now* head over and rerun all the tests.

```terminal-silent
symfony php bin/phpunit tests/Functional/DragonTreasureResourceTest.php
```

And... oooh! *So close*! We're down to just *one* failure in `testPublishTreasure`.
This tests that, when a treasure is published, we send a notification. Let's see
how we can tackle that in our new system next!
