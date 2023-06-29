# Dynamic Groups: Context Builder

In `DragonTreasure`, find the `$isPublished` field. Earlier we added this `ApiProperty`
`security` thing so that the field is only returned for admin users or owners of
this treasure. This is a simple and 100% valid way to handle this situation.

However, there *is* another way to handle fields that should be dynamic based on
the current user... and it may or may not have two advantages depending on your
situation.

## The security Options vs Dynamic Groups

First, check out the documentation. Open the GET endpoint for a single `DragonTreasure`.
And, even without trying it, you can see that `isPublished` *is* a field that is
correctly advertised in our docs.

So, that's good, right? Yea! Well, probably. If `isPublished` were truly an internal,
admin-only field, we might *not* want it advertised to the world.

The second possible problem with `security` is that, if you have this option on
*many* properties, it's going to run that security check a *lot* of times when
returning a collection of objects. Honestly, that probably *won't* cause performance
issues, but it's something to be aware of.

## Inventing New Serialization Groups

To solve these two possible problems - and, honestly, just to learn more about how
API Platform works under the hood - I want to show you an alternative solution.
Remove the `ApiProperty` attribute:

[[[ code('1af325ec1d') ]]]

And replace it with two new groups. We're not going to use the normal
`treasure:read` and `treasure:write`... because then the fields would *always*
be part of our API. Instead, use `admin:read` and `admin:write`:

[[[ code('341802f8ff') ]]]

This won't work yet... because these groups are *never* used. But here's the idea:
if the current user is an admin, then when we serialize, we'll *add* these two
groups.

The tricky part is, right now, groups are static! We set them way up here on the
`ApiResource` attribute - or on a specific operation - and that's it! But we *can*
make them dynamic.

## Hello ContextBuilder

Internally, API Platform has a system called a context builder, which is responsible
for building the normalization or denormalization contexts that are then passed
into the serializer. *And*, we can hook *into* that to *change* the context: like
to add extra groups.

Let's do it! Over in `src/ApiPlatform/`, create a new class called
`AdminGroupsContextBuilder`... and make this implement
`SerializerContextBuilderInterface`:

[[[ code('549590dd51') ]]]

Then, go to "Code"->"Generate" - or `Command`+`N` on a Mac - and select
"Implement methods" to create the one we need: `createFromRequest()`:

[[[ code('b5da790e95') ]]]

It's pretty simple: API Platform will call this, pass us the `Request`, whether or
not we're normalizing or denormalizing... and then *we* return the `context` array
that should be passed to the serializer.

## Let's do some Decoration!

Like we've seen a few times already, our intention is *not* to *replace* the core
context builder. Nope, we want the core context builder to do its thing... and
*then* we'll add our own stuff.

To do this, once again, we'll use service decoration. We know how this
works: add a `__construct()` method that accepts a private
`SerializerContextBuilderInterface` and I'll call this `$decorated`:

[[[ code('4cc69fe354') ]]]

Then, down here, say `$context = this->decorated->createFromRequest()`
passing `$request`, `$normalization` and `$extractedAttributes`. Add a `dump()`
to make sure this is working and return `$context`:

[[[ code('fc65eabe4b') ]]]

To tell Symfony to use *our* context builder in place of the real one, add
our `#[AsDecorator()]`.

Here, we need the service ID of whatever the *core* context builder is. That's
something you can find in the docs: it's `api_platform.serializer.context_builder`:

[[[ code('2b4a37c6b8') ]]]

Oh, but be careful when using `SerializerContextBuilderInterface`:
there are *two* of them. One of is from GraphQL: make sure you select the one
from `ApiPlatform\Serializer`, unless you *are* using GraphQL.

Ok! Let's see if it hits our dump! Run *all* of our tests: I also want to see
which fail:

```terminal
symfony php bin/phpunit
```

And... okay! We see the dump a *bunch* of times, followed by two failures. The
first is `testAdminCanPatchToEditTreasure`. That's the case we're working on right
now. We'll worry about `testOwnerCanSeeIsPublishedFieldI` in a minute.

Copy the test method name and rerun that with `--filter=`:

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

## When the Context Builder is Called

Perfect! We see the dump: actually *three* times, which is interesting.
Open up that test so we can see what's going on. Yup! We're making a *single*
`PATCH` request to `/api/treasure/1`. So, the context builder is called 3 times
during just one request?

It is! It's called one time when API Platform is querying and loading the
`DragonTreasure` from the database. That's... kind of an odd situation because the
context is meant to be used for the serializer... but we're simply querying for the
object. But anyway, that's the first time.

The next two make sense: it's called when the JSON we're sending is
denormalized into the object... and a third time when the final `DragonTreasure`
is normalized back into JSON.

Anyway, let's hop in and add the dynamic groups. To determine if the user is an
admin, add a second constructor argument - `private Security` from `SecurityBundle`
called `$security`:

[[[ code('db6e21b050') ]]]

Then down here, if `isset($context['groups'])` and
`$this->security->isGranted('ROLE_ADMIN')`, then we'll add the groups:
`$context['groups'][] =`. If we're currently normalizing, add `admin:read` else
add `admin:write`:

[[[ code('83f68aad31') ]]]

Now, you might be wondering why we're checking if `isset($context['groups'])`. Well,
it doesn't apply to our app, but imagine if we were serializing an object that
didn't have *any* `groups` on it - like we never set the `normalizationContext`
on that `ApiResource`. In that case, adding these `groups` would cause it to return
*less* fields! Remember, if there are *no* serialization groups, the serializer
returns *every* accessible field. But as soon as you add even *one* group, it only
serializes the things *in* that one group. So if there aren't any `groups`, do
nothing and let *everything* be serialized or deserialized like normal.

Ok! Let's try the test now!

```terminal-silent
symfony php bin/phpunit --filter=testAdminCanPatchToEditTreasure
```

It passes! The `isPublished` field *is* being returned if we're an admin
user. But... go refresh the docs... and open the GET one treasure endpoint. Now
we do *not* see `isPublished` advertised as a field in our docs... even though
it *will* be returned if we're an admin. That might be good or bad. It *is*
possible to make the docs load dynamically based on *who* is logged in, but that's 
not something we're going to tackle in this tutorial. We *did* talk about that in
our [API platform 2](https://symfonycasts.com/screencast/api-platform2-security)
tutorial... but the config system has changed.

Let's dig into the next method, which tests that an *owner* can see the
`isPublished` field. This is currently failing... and it's even trickier than the
admin situation because we need to include or *not* include the `isPublished` field
on an object-by-object basis.
