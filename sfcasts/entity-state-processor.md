# DTO -> Entity State Processor

We've checked off the "provider" side of things for our new `UserApi` class.
So let's shift our focus to the *processor* so we can *save* things. And we
*do* have some rather delightful tests for our `User` endpoints. Open
`UserResourceTest`.

## The Anatomy of the Request & State Processor

Ok, `testPostToCreateUser()`, posts some data, creates the user, then tests
to make sure that the password we posted *works* by logging in. Add
`->dump()` to help us see what's going on. Then, copy that method name and
run it:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

No surprise... it fails:

> Current response status code is 400, but 201 expected.

The dump is really helpful. It's our favorite error!

> Unable to generate an IRI for the item of type `UserApi`.

We already talked about what's happening: the JSON is deserialized into a `UserApi`
object. Good! *Then* the core Doctrine `PersistProcessor` is called because
that's the default `processor` when using `stateOptions`. But... because our
`UserApi` *isn't* an entity, `PersistProcessor` does *nothing*. Finally,
API Platform serializes the `UserApi` *back* into JSON... but without the `id`
populated, it fails to generate the IRI.

Watch! Over in `UserApi`, temporarily default `$id` to `5`. When we try the
test *now*...

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

It *appears* to work. Ok, it fails... but only later... down here in
`UserResourceTest` line 33. It *is* getting through the POST successfully.

## Creating the State Processor

Look at the response on top, it *is* returning this user JSON.
But, still, nothing is *saving*. Change the id back to null. We need to fix
this lack of saving by creating a new state processor. So spin over and run:

```terminal
php bin/console make:state-processor
```

Call it `EntityClassDtoStateProcessor` because, again, we're going to make this
class generic so that it works for *any* API resource class that's tied to a Doctrine
entity. We'll use it later for `DragonTreasure`.

With the empty processor generated, go hook it up in `UserApi` with
`processor: EntityClassDtoStateProcessor::class`.

Henceforth, every time we POST, PATCH, or DELETE this resource, *this* processor
will be called.

## Mapping the DTO Back to an Entity

But what is this `$data` variable exactly? You may have a guess, but just in case,
let's `dd($data)`... and rerun the test.

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

Yup, it's a `UserApi` object! The JSON we sent is deserialized into this `UserApi`
object, and then *that* is passed to our state processor. The `UserApi` object is
the "central object" inside of API Platform for this request.

Our job in the state processor is simple but important: to convert this `UserApi`
back to a `User` entity so that we can save it. Say `assert($data instanceof UserApi)`
and, inside, `$entity =` set to a new helper function: `$this->mapDtoToEntity($data)`.
Below, `dd($entity)`.

Then go add that new `private function mapDtoToEntity()`, which will accept an
`object $dto` argument and return another `object`.

Again, we know this will *really* accept a `UserApi` object and return a `User`
entity... but we're trying to keep this class generic so we can reuse it later.
Though we *are* going to have some user-specific code down here temporarily.
In fact, to help our editor, add another `assert($dto instanceof UserApi)`.

## Querying for the Existing Entity

We need to think about two different cases. The first is when we POST to create
a brand-new user. In that case, `$dto` will have a `null` id. And that means we should
create a fresh `User` object. The *other* case is if we were making, for example,
a `PATCH` request to edit a user. In *that* case, the item *provider* will first
*load* that `User` entity from the database... our provider will turn that
into a `UserApi` object with `id` equal to `6`... and that will eventually be
passed to us here. If the `id` is 6... we *don't* want to create a new `User`
object: we want to *query* the database for tha *existing* `User`. Our job is to
handle *both* situations.

Undo the changes to the test so we don't break anything... and now, `if`
`$dto->id`, we need to query for an existing `User`. To do that, on top, add a
constructor with `private UserRepository $userRepository`. Back down here,
say `$entity = $this->userRepository->find($dto->id)`.

If we *don't* find that `User`, throw a big giant exception that will trigger
a 500 error with `Entity %d not found`.

You might be wondering:

> Shouldn't this trigger a 404 error instead?

The answer, in this case, is *no*. If we're in this situation, it means the item
state provider has already successfully queried for a `User` with this id. So there
should be *no way* for us to suddenly *not* find it. There *are* some exceptions
to this, like if you allowed your user to *change* their `id`... *or* if you allowed
users to create *brand-new* objects and *set* the id manually... but for *most*
situations, including ours, if this happens, something went weird.

Next up, if we *don't* have an `id`, say `$entity = new User()`.

Done! In both cases, down here, we're going to map the `$dto` object to the
`$entity` object. This code is boring... so I'll speed through this. For the password,
put a `TODO` temporarily because we still need to hash that. *Also* add a `TODO`
for `handle dragon treasures`. Just focus on the easy stuff... and at the bottom,
`return $entity`.

If we've done things correctly, we'll take the `UserApi`, transform that into
an `$entity` and dump it. Rerun the test:

```terminal-silent
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... *404*! Let's see what happened here. Oh... *of course*. I never put
my test back together. This should be `->post('/api/users')`. Try that again and...
*got it*! There's our `User` entity object with the email and username transferred
correctly!

Next: Let's *save* this by leveraging the core Doctrine `PersistProcessor` and
`RemoveProcessor`. We'll also handle hashing the password. By the end, our user
tests will be passing with *flying* colors.
