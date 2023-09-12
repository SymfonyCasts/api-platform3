# Entity State Processor

We've done the `provider` side of things for our new `UserApi` API resource. Now let's shift our focus to the *processor*, so we can actually *save* things. We *do* have some nice tests for our `User` endpoints, so let's open `UserResourceTest.php`.

We have `testPostToCreateUser()`, which posts some data, creates the user, then actually *tests* to make sure that the password we posted works by attempting to log in with it. We're going to add `->dump()` here to help us see what's going on. Then, we'll copy that method name and run it:

```terminal
symfony php bin/phpunit --filter=testPostToCreateUser
```

And... it *fails*:

`Current response status code is 400,
but 201 expected.`

And the dump here is really helpful. You can see we got the same error that we saw earlier:

`Unable to generate an IRI for the item of type
\"App\\ApiResource\\UserApi\"`

We already talked about what's happening here. The JSON is sent, and it is successfully deserialized into a `UserApi` object. Yay! *Then* the core Doctrine persist processor is called because, when we use `stateOptions`, API Platform automatically sets our provider and processor to the core Doctrine stuff, unless we override it like we've done for `provider`. We haven't done that for processor, so it uses the core persist processor. That's good, except that our `UserApi` *isn't* an entity, so the persist processor does *nothing*.

Finally, API Platform tries to serialize our `UserApi` *back* into JSON to return it. *But* our `UserApi` has no ID populated, so it *fails* to generate the IRI. Watch! Over in `UserApi.php`, let's temporarily default `$id` to `5`. When we try to test *now*... it appears to work. It *technically* failed, but it's getting further than before. It's failing down here in `UserResourceTest` on line 33. So it's getting through this part successfully, it *is* a status 201, and then it just fails to log in because we're not yet *saving* that user. If you look at the response on top, it *is* *returning* this user successfully, but nothing is actually *saving* that in the database. Let's change this back to null... and we're going to fix this by creating a new state processor. Spin over and run:

```terminal
./bin/console make:state-process
```

And let's call this

```terminal
EntityClassDtoStateProcessor
```

because, again, we're making a state processor that's going to work for any situation where we have an API resource class that's tied to a Doctrine entity. We'll also use this later for our dragon treasure.

Okay, there's our new processor. Now we can start hooking things up. Say `processor: EntityClassDtoStateProcessor::class`. Perfect! From now on, whenever we are posting, patching, or deleting something, our processor is going to be called. But what is this `$data` thing? You may have already guessed, but just in case, let's `dd($data)` and rerun the test. And... it's our `UserApi` object! The JSON we sent is deserialized into a `UserApi` object, and then *that* `UserApi` object is passed to our state processor. The `UserApi` object is the central object inside of API Platform for this request.

All right, so our *main* job in here is to convert this `UserApi` object to a `User` entity so we can save it. Say `assert($data instanceof UserApi)` and, inside, `$entity =` with a new helper function - `$this->mapDtoToEntity($data)`. Below that, let's `dd($entity)`. Finally, we'll say `private function mapDtoToEntity()`. This is going to take in an `object $dto` and return another `object`.

Again, we know this is actually going to take in `UserApi` and return a `User` entity object, but we're going to try to write this class to be generic to *all* classes so we can use it later. We *are* going to have some user-specific code down here temporarily, though. In fact, to help our editor right now, let's add another `assert($dto instanceof UserApi)` just to make life easier.

Okay, we need to think about two different cases for this processor. The first case is that we're *posting* a brand new user. In that case, this `$dto` is *not* going to have an `$id`. The `$id` on this is going to be `null`. And that means we probably want to create a fresh `User` object. The *other* case is if we were making, for example, a `PATCH` request to some specific user. In *that* case, the item provider will first *load* that user from the database, and then our provider will turn that into a `UserApi` object with the `$id` equal to `6`. In this situation, when we get this `UserApi` `$dto`, its `$id` will be equal to six. So... we *don't* want to create a new `User` object, right? We actually want to *query* the database for the user with that ID. That means we need to address *both* cases, where we have a new user here *or* where we're querying for an *existing* user. No problem!

Let's undo the changes to this test so we don't break anything... and now, let's say `if ($dto->id)`. Basically, if we have an ID, *this* is the case where we need to *query* for that user. To do that, we'll need the user repository. Add a constructor up here with `private UserRepository $userRepository`. Then, down here, we'll say `$entity = $this->userRepository->find($dto->id)`. Now we're going to add some code that says:

`If we don't find an entity with that ID,
throw a new exception.`

This isn't going to be a normal exception. If it can't find an entity with the ID it's looking for, it's actually going to trigger a 500 error. So say `Entity %d not found`, and then pass `$dto->id`.

You might be wondering: "Shouldn't this be some sort of 404 through a `NotFound` HTTP exception here?" The answer, in this case, is *no*. If we have a situation like a `PATCH` request, it means our state provider has already successfully queried for that object and *found* it. So there should be *no way* for us to get to a point here where we have a `UserApi` object with an `id` that is not found in the database. There *are* some exceptions to this, like if you, for some reason, allowed your user to *change* the `id`. In that case, you would need to do this. *Or* if you allowed users to create *brand new* objects via a `PUT` request. But for *most* situations, including ours, if this happens, something went wrong.

Allright, so if we *have* an `id`, we've *queried* for the `id`. And if we *don't* have an `id`, this is we're going to say `$entity = new User()`. And that's it! Then, down here, regardless of the case, we're going to map the `$dto` object to the to the `$entity` object. This code is pretty boring, so I'll speed through this. And for the password, we're actually just going to put a `TODO` there because that needs to be the *hashed* password. We'll worry about that in a second. And we'll *also* add a `TODO` for `handle dragon treasures`. For now, we're just going to worry about the `email` and the `username`, and then, at the bottom, we'll `return $entity`.

Okay, so if we've done things correctly, we're going to get the `UserApi` that has this data on it, transform it into an `$entity`, dump that, and let's see if it works. Rerun our tests and... *404*. Let's see what happened here. Oh... *of course*. That's because I never actually put my test back together. This should read `->post('/api/users')`. I'll try that again and... *got it*! There's our `User` object with the email and username transferred correctly.

Next: Let's actually *save* this by leveraging the core Doctrine processor. We're *also* going to make this processor work when we're *deleting* users, and we'll finish our password by *hashing* it. By the end, we'll have our app passing this test with *flying* colors.
