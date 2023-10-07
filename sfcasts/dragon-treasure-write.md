# Dragon Treasure Write

Let's get our *write* endpoints working for our new `DragonTreasure`! If you look down here, there's a test called `testPostToCreateTreasure()`. That sounds like a good one! Over in your terminal, run:

```terminal
symfony php bin/phpunit --filter=
testPostToCreateTreasure
```

And... that *explodes*. It actually ran a *couple* of tests. We can look at *any* of them, but all of them will say the same thing:

`No mapper found for App\\ApiResource\\
DragonTreasureApi -> App\\Entity\\DragonTreasure`.

If we follow the logic here, we can see what's happening internally. When we try to create something, it *deserializes* the JSON into a `DragonTreasureApi` object, and then calls our processor. Our *processor* takes that API object and tries to use the MicroMapper to map it over to our entity. We're *missing* the mapping step from `DragonTreasureApi` to `DragonTreasure`, but *no worries*! That's an easy fix.

In `/src/Mapper`, let's create a new `DragonTreasureApiToEntityMapper`. The next part should look *very* familiar. This is going to implement `MapperInterface`, and we'll use the `#[AsMapper()]` to say that this is coming `from: DragonTreasureApi::class, to: DragonTreasure::class`. *Then* we'll implement the methods. This is going to be very similar to our `UserApiToEntityMapper` method.

In `load()`, if we have an ID, we're going to *query* for that object, so we can go ahead and add a constructor, with `private DragonTreasureRepository $repository`. Down here, we'll use the regular `$dto = $from`, and `assert` that `$dto` is an `instanceof DragonTreasureApi`, just to keep us all sane. To make this easier, we're going to steal some code from our other mapper, since it's *very* similar. Copy the code here, paste it over here, hit "Cancel" because we don't actually want that `use` statement... and let's rename this to just `$entity`. So if the `$dto` has an `id`, it means we're editing it and we want to find it. *Else*, we're going to create a `new DragonTreasure`. And while it *shouldn't* happen, we have an `Exception` in case that's not found.

One interesting thing about our `DragonTreasure()` entity is that it has a constructor argument, which is the *name*. We actually *don't* have a `setName()` method on it, so the only way to set the name is through the constructor. This is a case where we're going to transfer the `name` *from* the `$dto` *onto* the entity right there when we instantiate it, *if* it's a new one.

Okay, down in `populate()`, we'll start with the same code we have on `load()`. We'll also add `$entity = $to`... and one more `assert()` with `$entity instanceof DragonTreasure`. Finally, we'll say `TODO` for the other fields down here. We need to focus on getting this thing mapping correctly first.

When we ran our test earlier, it actually ran *three* tests that match that name, so let's make the test name a little more unique. This is called `testPostToCreateTreasure()` and it's using the normal log in mechanism, so let's add `WithLogin` at the end. If we run the test again with the new name... okay! we get `Current response status code is 500`.

Let's see... what's going on? Okay, good! We got further this time! It's *now* exploding when it hits the *database*. So it *is* trying to save, and it's *complaining* because `owner_id` is null. As a reminder, owner is *supposed* to be optional. If we don't pass an owner, we're setting it to the currently authenticated user. We need to re-add that logic, and we'll do that in a second. But this failure is actually coming from *earlier*. It's coming from line 71, which is right here. The first thing this test does is check our validation. It submits *no* JSON, and it makes sure that our validation constraints are hit. We don't have any valid validation constraints, so instead of *failing* validation, it's actually trying to *save* the database. Let's re-add our validation constraints, and we'll do this like we normally would, except we'll put it on our API class. So we want to have our `$name` `#[NotBlank]`, our `$description` `#[NotBlank]`, our `$value` will be `#[GreaterThanOrEqual(0)]`, our `$coolFactor` will be `#[GreaterThanOrEqual(0)]` and *also* `#[LessThanOrEqual(to)]`. That should do it!

Let's run the test again. We're probably going to hit that same error, and... yep - 500 error. But look! Now it's coming from line 78! That means we *are* hitting our status code here, and down here... it's posting a valid response, attempts to save it to the database, but it *can't* because, like we saw a second ago, the `owner_id` is *still* null.

This is one of the great things about the having these mapper objects. In `DragonTreasureApiToEntityMapper.php`, *normally*, we're going to do things like `$entity->setValue($dto->value)`, where we're just transferring data from one to the other. But we can *also* just set custom things here to do any weird transformations we want.

Check this out! Say `if ($dto->owner)`, and then we're going to set that onto the entity. We won't do that *yet*, so just `dd()` this for now. This would be a case where we have a test where, perhaps, we're choosing to *send* the `owner` as something. I'll talk more about that later, so we'll delete that for now. If we *tried* doing that, we would hit this dump here. Right now, we're going to work on the `else` situation where we *don't* have an `owner` in the `$dto`. In *that* case, we can just set it to the currently authenticated user. So, up here, like we've done so many places before, we'll just inject the `Security` service. Down *here*, we'll set the owner to `$this->security->getUser()`. Beautiful! We *are* still missing the other field setting here, so if we try to run the test... we're *still* going to get a 500 error. *But*, if you check out the error now, it's failing because the `description` is null. The `owner` is being set, but it's still failing because `description` isn't on the entity. We still have some work to do! Over here, say `$ntity->setDescription($dto->description)`, `$entity->setCoolFactor($dto->coolFactor)`, and `$entity->setValue($dto->value)`. We're just transferring things from one to another. It's boring, but it's also very clear. We'll also put `TODO` down here for `published()`. We'll talk more about `published()` shortly, so we won't set that field yet.

If we try to run that test now... it *passes*. Woo! But if we run *all* of our tests from `DragonTreasure`, we actually *do* have several failures. They're related to headers, security, validation, etc. Let's talk about what these are *next* and finish cleaning up our `DragonTreasure`.
