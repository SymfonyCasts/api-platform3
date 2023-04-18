# Validation Groups & Patch Formats

Now that the `plainPassword` property is a legitimate part of our API, let's add
some validation... because you can't create a new user without a password! Add
`Assert\NotBlank`:

[[[ code('3c58a50bac') ]]]

Piece of cake! Well, that just created a new problem... but let's blindly move
forward and pretend that everything is fine.

Copy the first test and paste to create a second method that will make sure we can
*update* users. Call it `testPatchToUpdateUser()`. This one is simple: make a
new user - `$user = UserFactory::createOne()`, add `actingAs($user)` then `->patch()`
to `/api/users/` then `$user->getId()` to edit ourselves.

For the `json`, just send `username`, add `assertStatus(200)`.... then we don't
need  any of this other stuff:

[[[ code('2255e56fb9') ]]]

As a reminder, up on the `Patch` operation for `User`... here it is, we're
requiring that the user has `ROLE_USER_EDIT`. Because we're logging in as a "full"
user, we should have that... and everything should work fine... famous last words.

Run:

```terminal
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

## PATCH: The Most Interesting HTTP Method in the World

And... oh! 200 expected, got 415. That's a new one! Click to open the last response...
then I'll View Source to make it more clear. Interesting:

> The content-Type: `application/json` is not supported. Supported MIME types are
> `application/merge-patch+json`.

Let's unpack this. We're making a `PATCH` request... and `PATCH` requests are
quite simple: we send a subset of fields, and only *those* fields are updated.

Whelp, it turns out that the `PATCH` HTTP method can get a whole heck of a lot
more interesting than this. In the greater interwebs, there are competing *formats*
for how the data should look when using a PATCH request and each format *means*
something different.

Currently, API Platform supports only one of these formats: `application/merge-patch+json`.
This format is... kind of what you expect. It says: if you send a single field,
only that single field will be changed. But it also has other rules, like how you
could set `email` to `null`... and that would actually *remove* the `email` field.
That doesn't really make sense in our API, but the point is: the format defines
rules about how your JSON should look for a `PATCH` request and what that means.
If you want to know more, there's a [document that describes everything](https://www.rfc-editor.org/rfc/rfc7386):
it's quite short and readable.

So, API platform only supports *one* format for PATCH requests at the
moment. But, in the future, they might support more. And so, when you make a
`PATCH` request, API Platform requires you to send a `Content-Type` header set
to `application/merge-patch+json`... so that you're *explicitly* telling API platform
*which* format your JSON is using.

In other words, to fix our error, pass a `headers` key with `Content-Type` set
to `application/merge-patch+json`:

[[[ code('d055fac9e6') ]]]

Try this now:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

It *still* fails, but now it's a validation error! The takeaway is simple: PATCH
requests require this `Content-Type` header.

But wait! We did a bunch of `PATCH` requests over in `DragonTreasureResourceTest`
and those worked fine *without* the header! What the what?

That... was kind of on accident. Inside `DragonTreasure`, in the first tutorial...
here it is, we added a `formats` key so that we could add CSV support:

[[[ code('18f2b56f4b') ]]]

It turns out that, for some complex internal reasons, by adding `formats`, we
*removed* the requirement for needing that header. So we were "getting away" with
*not* setting the header in `DragonTreasureResourceTest`... even though we *should*
be setting it. It may have been better to set `formats` on the `GetCollection`
operation only... since that's the only spot we need CSV.

Anyway, that's why we didn't need it before, but we *do* need it now. By the way,
if adding this header every time you call `->patch` is annoying, this is another
situation where you could add a custom method to browser - like `->apiPatch()` -
which would work the same, but add that header automatically.

## Fixing the Validation Groups

Ok, back to the test! It's failing with a 422. Open the
error response. Ah, it's from `plainPassword`: this field should not be blank!

The `plainPassword` property is *not* persisted to the database. So, it's always
empty at the start of an API request. When we create a `User`, we absolutely *do*
want this field to be required. But when we're editing a `User`, we *don't* need
this field to be set. They *can* set it in order to change their password, but
that's optional.

This is the first spot where we need *conditional* validation: validation should
happen on one operation, but not on others. The way to fix this is with validation
groups, which is very similar to serialization groups.

Find the `Post` operation and pass a new option called
`validationContext` with, you guessed it, `groups`! Set this to an array with a
group called `Default` with a capital D. Then invent a second group:
`postValidation`:

[[[ code('18f2b56f4b') ]]]

When the validator validates an object, by default, it validates everything that's
in a group called `Default`. And any time you have a constraint, by default that
constraint is *in* that `Default` group. So what we're saying here is:

> We want to validate all the *normal* constraints *plus* any constraints
> that are in the `postValidation` group.

Now we can take that `postValidation`, go down to `plainPassword` and set
`groups` to `postValidation`:

[[[ code('3cd32444f9') ]]]

That *removes* this constraint from the `Default` group and *only* includes
it in the `postValidation` group. Thanks to this, other operations like `Patch`
will *not* run this, but the `Post` operation *will*.

Run the test now:

```terminal-silent
symfony php bin/phpunit --filter=testPatchToUpdateUser
```

We're unstoppable! In fact, *all* of our tests are passing!

## Careful: PUT Can Create Objects

But head's up! In `User`, we still have both `Put` and `Patch`. I haven't
played with it much yet, but the new `Put` behavior, in theory, *does* support
*creating* objects. This can make things tricky: do we need to require the password
or not? It depends! This might be another reason for removing the `Put` operation
to keep life  simple. That gives us one operation for creating and one operation
for editing.

Next: let's explore making our serialization groups *dynamic* based on the user.
This will give us another way to include or not include fields based on who
is logged in. And it'll lead us towards adding super custom fields.
