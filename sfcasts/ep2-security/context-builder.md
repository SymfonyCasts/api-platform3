# Dynamic Groups: Context Builder

In `DragonTreasure`, find the `isPublished` field. Earlier we added this `ApiProperty`
`security` thing so that this field is only shown to admin users or owners of this
treasure. This is a simple and 100% valid way to handle this situation.

However, there *is* another way to handle fields that should be dynamic based on
the current user... and it may or may not have two advantages depending on your
situation.

## The security Options vs Dynamic Groups

First, check out the documentation. Open the GET endpoint for a single `DragonTreasure`
and, even without trying it, you can see that `isPublished` *is* a field that is
correctly advertised in our docs.

So, that's good right! Yea! Well, probably. If `isPublished` were truly an internal
admin-only field, we might *not* want that advertised to the world. So the fact that
`isPublished` might be good or bad depending on your situation.

The second possible problem with `security` is that if you have this option on *many*
different properties, it's going to run that security check a *lot* of times to return
a collection of objects. Honestly, that will probably *not* cause performance issues,
but it's something to be aware of.

## Inventing New Serialization Groups

To solve these two possible problems - and learn more about how API Platform works
under the hood - I want to show you an alternative solution. Remove the `ApiProperty`
attribute and replace it with two new groups. But we're not going to use the normal
`treasure:read` and `treasure:write`... because then the fields would *always*
be part of our API. Instead, use `admin:read` and `admin:write`.

This won't work yet... because these groups are *never* used. But here's the idea:
if the current user is an admin user, then when we serialize, we'll add these two
groups.

The tricky part is, right now, groups are static! They're set way up here on the
`ApiResource` attribute - or on a specific operation - and that's it! But we *can*
make them dynamic.

## Hello ContextBuilder

Internally, API Platform has a system called a context builder, which is responsible
for building the normalization or denormalization contexts that are then passed
into the serializer. *And*, we can hook *into* that to *change* the context, like
adding our own groups.

Let's do it! Over in `src/ApiPlatform/`, create a new class called
`AdminGroupsContextBuilder`... and make this implement
`SerializerContextBuilderInterface`. Then, go to Code -> Generate - or Command + N
on a Mac - and select "Implement Methods" to create the one we need:
`createFromRequest()`.

It's pretty simple: API Platform will call this, pass us the `Request`, whether or
not we're normalizing or denormalizing... and then *we're* going to return an array
of the `context` that should be passed to the serializer.

## Let's do some Decoration!

Like we've seen a few times already, our intention is *not* to *replace* the core
context builder. Rather, we want the core context builder to do it's thing and
*then* we want to add a little extra after.

To do this, once again, we're going to leverage service decoration. We know how this
works: add a `__construct` method that accepts a private
`SerializerContextBuilderInterface` and I'll call this `$decorated`.

Perfect. Then, down here, say `$context = this->decorated->createFromRequest()`
passing `$request`, `$normalization` and `$extractedAttributes`. Add a `dump`
to make sure this is working and return `$context`.

To tell a Symfony to use *our* context builder in place of the real one, add
our `#[AsDecorator()]`. Now we need the service ID of whatever the *core* context
builder is. And that's something you can find in the docs: it's
`api_platform.serializer.context_builder`.

Oh, but be careful when using `SerializerContextBuilderInterface`:
there are *two* of them. One of is from GraphQL - make sure you select the one
from `ApiPlatform\Serializer`, unless you *are* using the GraphQL.

Ok! Let's see if it hits our dump! Let's run *all* of our tests: I also want to see
which fail:

```terminal
symfony php bin/phpunit
```

And... okay! We see the dump a *bunch* of times, followed by two failures. The
first is `testAdminCanPatchToEditTreasure`. That's the one we're working on right
now. We're going to worry about `testOwnerCanSeeIsPublishedField` in a minute.

Copy the test method name and rerun that with `--filter=`:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

## When the Context Builder is Called

And... perfect! We see the dump - actually *three* times, which is interesting.
Let's open up that test so we can see what's going on. Yup! We're making a single
`PATCH` request to `/api/treasure/1`. So, the context builder is called 3 times
during just one request?

Yup! The `ContextBuilder` is called one time when it's querying and loading that
`DragonTreasure` from the database. It's kind of an odd situation because the context
is meant to be used for the serializer... but we're simply querying for the
`DragonTreasure`: not serializing it. But anyways, that's the first time.

The next two make sense: it's called again when the JSON we're sending is
denormalized into the object... and a third time when the final `DragonTreasure`
is being normalized back into JSON.

Anyways, let's hop in an add our dynamic groups. To determine if the user is an
admin, add a second constructor argument - `private Security` from `SecurityBundle`
called `$security`. Then down here, `if` `isset($context['groups'])`
and `$this->security->isGranted('ROLE_ADMIN')`, then we're going to add the groups:
`context['groups'][] =`. but we need to be careful: if we're currently normalizing,
add `admin:read` else add `admin:write`.

Now, you might be wondering why we're check if `isset($context['groups'])`. Well,
it doesn't apply to our project, but imagine if we were serializing an object that
didn't have *any* `groups` on it - like we never set the `normalizationContext`
on our `ApiResource`. In that case, adding these `groups` would cause it to return
*less* fields. Remember, if there are *no* serialization groups, the serializer
returns *every* accessible field. But as soon as you add *one* group, it only
serializes the things *in* that one group. So if there aren't any `groups`, do
nothing and let *everything* be serialized or deserialized like normal.

Ok! Let's try the test now!

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

And... it passes! The `isPublished` field *is* being returned if we're an admin
user. But... go refresh the docs... and open the GET one treasure endpoint. Now
we do *not* see `isPublished` advertised as a field that will be returned! That might
be a good or bad thing. It *is* possible to make the docs load dynamics based on
*who* is logged in, but that's not something we're going to tackle in this tutorial.
We *did* talk about it in our API platform 2 tutorial... but the config system
has changed.

Let's dig into the next method, which tests that an *owner* can see the
`isPublished` field. This is currently failing... and it's even trickier than the
admin situation because we need to include or *not* include the `isPublished` field
on an object-by-object basis.
